<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;

// --- MODELS ---
use App\Models\User;
use App\Models\Employee; // PENTING: Tambahkan Model Employee
use App\Models\Facilities\WorkOrderFacilities;
use App\Models\Engineering\Machine;
use App\Models\Engineering\Plant;

// --- NOTIFICATION ---
use App\Notifications\NewTicketCreated;

class WorkOrderFacilityController extends Controller
{
    // --- 1. GET ALL TICKETS ---
    public function index(Request $request)
    {
        return WorkOrderFacilities::with(['technicians', 'machine'])
            ->latest()
            ->paginate(10);
    }

    // --- 2. GET SINGLE TICKET ---
    public function show($id)
    {
        // Asumsi: NIK dan Divisi ada di tabel 'users' yang berelasi dengan work_order
        // Atau jika ada langsung di tabel work_orders, sesuaikan saja field-nya.
        $workOrder = WorkOrder::with('user')->findOrFail($id);

        return response()->json([
            'id' => $workOrder->id,
            'ticket_number' => $workOrder->ticket_number,
            'title' => $workOrder->title,
            'description' => $workOrder->description,
            'status' => $workOrder->status,
            // Tambahkan data User
            'requester_name' => $workOrder->user->name,
            'nik' => $workOrder->user->nik,        // Pastikan kolom ini ada di DB
            'divisi' => $workOrder->user->divisi,  // Pastikan kolom ini ada di DB
        ]);
    }

    // --- 3. CREATE TICKET (MODIFIED FOR GUEST + NIK) ---
    public function store(Request $request)
    {
        // A. VALIDASI INPUT
        $rules = [
            'requester_nik'  => 'required|exists:employees,nik', // WAJIB ADA DI TABLE EMPLOYEE
            'requester_name' => 'required|string',
            'plant_id'       => 'required',
            'description'    => 'required',
            'category'       => 'required',
            'photo'          => 'image|max:5120'
        ];

        // Validasi Kondisional Mesin
        if ($request->category == 'Pemasangan Mesin') {
            $rules['new_machine_name'] = 'required|string|max:255';
        } elseif (in_array($request->category, [
            'Modifikasi Mesin',
            'Pembongkaran Mesin',
            'Relokasi Mesin',
            'Perbaikan Mesin',
            'Pembuatan Alat Baru'
        ])) {
            $rules['machine_id'] = 'required';
        }

        $request->validate($rules);

        // B. CEK DATA KARYAWAN (AUTO-FILL BACKEND)
        $employee = Employee::where('nik', $request->requester_nik)->first();
        if (!$employee) {
            return response()->json(['message' => 'Data Karyawan Tidak Ditemukan'], 422);
        }

        // C. UPLOAD FOTO
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('wo_facilities', 'public');
        }

        // D. GENERATE NOMOR TIKET
        $bulanIndo = [
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MEI',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGU',
            '09' => 'SEP',
            '10' => 'OKT',
            '11' => 'NOV',
            '12' => 'DES'
        ];
        $hari  = date('d');
        $bulan = date('m');
        $tahun = date('y');
        $dateCode = $hari . $bulanIndo[$bulan] . $tahun;
        $prefix = 'FAC-' . $dateCode . '-';

        $lastTicket = WorkOrderFacilities::where('ticket_num', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $newSeq = $lastTicket ? ((int)substr($lastTicket->ticket_num, -3) + 1) : 1;
        $ticketNum = $prefix . sprintf('%03d', $newSeq);

        // E. LOGIKA PLANT & MACHINE
        $plantName = '-';
        if ($p = Plant::find($request->plant_id)) {
            $plantName = $p->name;
        }

        $machineId = null;
        $machineName = null;

        if ($request->category == 'Pemasangan Mesin') {
            $newMachine = Machine::create([
                'plant_id' => $request->plant_id,
                'name' => $request->new_machine_name,
                'code' => 'NEW-' . strtoupper(Str::random(5)),
            ]);
            $machineId = $newMachine->id;
            $machineName = $newMachine->name;
        } else {
            if ($request->filled('machine_id')) {
                $m = Machine::find($request->machine_id);
                if ($m) {
                    $machineId = $m->id;
                    $machineName = $m->name;
                }
            }
        }

        // F. SIMPAN DATA KE DATABASE
        $wo = new WorkOrderFacilities();
        $wo->ticket_num = $ticketNum;

        // --- PERUBAHAN UTAMA DI SINI ---
        $wo->requester_id = Auth::id(); // Akan NULL jika Guest (Pastikan kolom DB nullable)
        $wo->requester_nik = $employee->nik;
        $wo->requester_name = $employee->name;
        $wo->requester_division = $employee->department; // Penting untuk Approval SPV
        // -------------------------------

        $wo->plant = $plantName;
        $wo->machine_id = $machineId;
        $wo->machine_name = $machineName;
        $wo->location_details = $request->location_detail ?? '-';
        $wo->report_date = $request->report_date ? Carbon::parse($request->report_date) : now();
        $wo->description = $request->description;
        $wo->category = $request->category;
        $wo->target_completion_date = $request->target_completion_date;
        $wo->photo_path = $photoPath;

        // STATUS BERJENJANG (TIERED APPROVAL)
        $wo->status = 'draft';
        $wo->internal_status = 'waiting_spv'; // Menunggu SPV Approve

        $wo->save();

        // G. RETURN RESPONSE JSON
        return response()->json([
            'message' => 'Tiket berhasil dibuat. Menunggu persetujuan SPV ' . $employee->department,
            'data' => $wo
        ], 201);
    }

    // --- 4. UPDATE TICKET ---
    public function update(Request $request, $id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);
        $wo->update($request->all());
        return response()->json(['message' => 'Updated', 'data' => $wo]);
    }

    // --- 5. EXPORT ---
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export logic placeholder']);
    }
}
