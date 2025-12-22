<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // PENTING: Untuk Notifikasi
use Illuminate\Support\Str;
use Carbon\Carbon;

// --- MODELS ---
use App\Models\User;
use App\Models\Facilities\WorkOrderFacilities; // Pastikan namespace ini benar sesuai folder Anda
use App\Models\Engineering\Machine;
use App\Models\Engineering\Plant;

// --- NOTIFICATION ---
use App\Notifications\NewTicketCreated;

class WorkOrderFacilityController extends Controller
{
    // --- 1. GET ALL TICKETS ---
    public function index(Request $request)
    {
        // Simple pagination for API
        return WorkOrderFacilities::with(['technicians', 'machine'])
            ->latest()
            ->paginate(10);
    }

    // --- 2. GET SINGLE TICKET ---
    public function show($id)
    {
        return WorkOrderFacilities::with(['technicians', 'machine'])->findOrFail($id);
    }

    // --- 3. CREATE TICKET (LOGIKA NOTIFIKASI ADA DISINI) ---
    public function store(Request $request)
    {
        // A. VALIDASI INPUT
        $rules = [
            'requester_name' => 'required|string',
            'plant_id' => 'required',
            'description' => 'required',
            'category' => 'required',
            'photo' => 'image|max:5120'
        ];

        // Validasi Kondisional Mesin
        if ($request->category == 'Pemasangan Mesin') {
            $rules['new_machine_name'] = 'required|string|max:255';
        } elseif (in_array($request->category, [
            'Modifikasi Mesin',
            'Pembongkaran Mesin',
            'Relokasi Mesin',
            'Perbaikan',
            'Pembuatan Alat Baru'
        ])) {
            $rules['machine_id'] = 'required';
        }

        $request->validate($rules);

        // B. UPLOAD FOTO (Opsional)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('wo_facilities', 'public');
        }

        // C. GENERATE NOMOR TIKET
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

        // D. LOGIKA PLANT & MACHINE
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

        // E. SIMPAN DATA KE DATABASE
        $wo = new WorkOrderFacilities();
        $wo->ticket_num = $ticketNum;
        $wo->requester_id = Auth::id() ?? 1; // Default 1 jika user blm login (Hati-hati)
        $wo->requester_name = $request->requester_name;
        $wo->plant = $plantName;
        $wo->machine_id = $machineId;
        $wo->machine_name = $machineName;
        $wo->location_details = $request->location_detail ?? '-';
        $wo->report_date = $request->report_date ? Carbon::parse($request->report_date) : now();
        $wo->report_time = $request->report_time;
        $wo->shift = $request->shift;
        $wo->description = $request->description;
        $wo->category = $request->category;
        $wo->target_completion_date = $request->target_completion_date;
        $wo->photo_path = $photoPath;
        $wo->status = 'pending';

        $wo->save(); // <--- TIKET DISIMPAN DISINI

        // F. LOGIKA NOTIFIKASI (INI YANG ANDA CARI)
        try {
            Log::info("API: Memulai proses notifikasi untuk tiket {$ticketNum}");

            // Cari Admin (Sesuaikan Role dengan database Anda: fh.admin / super.admin)
            $admins = User::whereIn('role', ['fh.admin', 'super.admin'])->get();

            if ($admins->count() > 0) {
                Notification::send($admins, new NewTicketCreated($wo));
                Log::info("API: Notifikasi BERHASIL dikirim ke " . $admins->count() . " admin.");
            } else {
                Log::warning("API: GAGAL kirim notifikasi. Tidak ada user dengan role 'fh.admin' atau 'super.admin'.");
            }
        } catch (\Exception $e) {
            Log::error('API Error Notifikasi: ' . $e->getMessage());
        }

        // G. RETURN RESPONSE JSON
        return response()->json([
            'message' => 'Work order created successfully',
            'data' => $wo
        ], 201);
    }

    // --- 4. UPDATE TICKET (Placeholder untuk Route::put) ---
    public function update(Request $request, $id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);
        // Tambahkan logika update field disini jika diperlukan
        $wo->update($request->all());
        return response()->json(['message' => 'Updated', 'data' => $wo]);
    }

    // --- 5. EXPORT (Placeholder untuk Route::get export) ---
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export logic not implemented yet']);
    }
}
