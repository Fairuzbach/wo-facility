<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

// --- MODELS ---
use App\Models\User;
use App\Models\Employee;
use App\Models\Facilities\WorkOrderFacilities;
use App\Models\Engineering\Machine;
use App\Models\Engineering\Plant;

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
        $workOrder = WorkOrderFacilities::with(['technicians', 'machine', 'plant'])
            ->findOrFail($id);

        return response()->json([
            'id' => $workOrder->id,
            'ticket_num' => $workOrder->ticket_num,
            'requester_name' => $workOrder->requester_name,
            'requester_nik' => $workOrder->requester_nik,
            'requester_division' => $workOrder->requester_division,
            'plant' => $workOrder->plant,
            'machine' => $workOrder->machine,
            'description' => $workOrder->description,
            'category' => $workOrder->category,
            'status' => $workOrder->status,
            'internal_status' => $workOrder->internal_status,
            'technicians' => $workOrder->technicians,
            'photo_path' => $workOrder->photo_path,
            'report_date' => $workOrder->report_date,
            'target_completion_date' => $workOrder->target_completion_date,
            'actual_completion_date' => $workOrder->actual_completion_date,
        ]);
    }

    // --- 3. CREATE MULTIPLE TICKETS (MODIFIED FOR MULTIPLE REQUESTERS) ---
    public function store(Request $request)
    {
        Log::info('🎯 Store method called', [
            'all_data' => $request->all(),
            'files' => $request->allFiles()
        ]);

        // A. VALIDASI INPUT
        $rules = [
            'requester_nik.*'     => 'required|exists:employees,nik',
            'requester_name.*'    => 'required|string',
            'requester_division.*' => 'required|string',
            'plant_id'            => 'required|exists:plants,id',
            'description'         => 'required|string',
            'category'            => 'required|string',
            'photo'               => 'nullable|image|max:5120',
            'target_completion_date' => 'nullable|date',
            'sub_plant'           => 'nullable|string|in:A,B,C,D,E,F',
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
            $rules['machine_id'] = 'required|exists:machines,id';
        }

        $validated = $request->validate($rules);

        // B. CEK APAKAH ADA MINIMAL 1 PELAPOR
        if (!$request->has('requester_nik') || count($request->requester_nik) === 0) {
            return response()->json([
                'message' => 'Minimal 1 pelapor harus diisi',
                'errors' => ['requester_nik' => ['Pelapor tidak boleh kosong']]
            ], 422);
        }

        // C. UPLOAD FOTO (JIKA ADA)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            try {
                $photoPath = $request->file('photo')->store('wo_facilities', 'public');
                Log::info('✅ Photo uploaded', ['path' => $photoPath]);
            } catch (\Exception $e) {
                Log::error('❌ Photo upload failed', ['error' => $e->getMessage()]);
            }
        }

        // D. GET PLANT INFO
        $plant = Plant::findOrFail($request->plant_id);
        $plantName = $plant->name;

        // Add sub_plant info if exists
        if ($request->filled('sub_plant')) {
            $plantName .= ' - Plant ' . $request->sub_plant;
        }

        // E. LOGIKA MACHINE
        $machineId = null;
        $machineName = null;

        if ($request->category == 'Pemasangan Mesin') {
            // Buat mesin baru
            $machineData = [
                'plant_id' => $request->plant_id,
                'name' => $request->new_machine_name,
            ];

            // Tambahkan sub_plant jika ada
            if ($request->filled('sub_plant')) {
                $machineData['sub_plant'] = $request->sub_plant;
            }

            $newMachine = Machine::create($machineData);
            $machineId = $newMachine->id;
            $machineName = $newMachine->name;
            Log::info('✅ New machine created', ['machine' => $newMachine]);
        } elseif ($request->filled('machine_id')) {
            // Gunakan mesin yang sudah ada
            $machine = Machine::find($request->machine_id);
            if ($machine) {
                $machineId = $machine->id;
                $machineName = $machine->name;
            }
        }

        // F. GENERATE TICKET NUMBER BASE
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

        // Get last sequence number
        $lastTicket = WorkOrderFacilities::where('ticket_num', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $currentSeq = $lastTicket ? ((int)substr($lastTicket->ticket_num, -3)) : 0;

        // G. LOOP UNTUK SETIAP PELAPOR - BUAT 1 TIKET PER ORANG
        $createdTickets = [];
        $requesterNiks = $request->requester_nik;
        $requesterNames = $request->requester_name;
        $requesterDivisions = $request->requester_division;

        DB::beginTransaction();

        try {
            foreach ($requesterNiks as $index => $nik) {
                // Generate unique ticket number for each requester
                $currentSeq++;
                $ticketNum = $prefix . sprintf('%03d', $currentSeq);

                // Verify employee exists
                $employee = Employee::where('nik', $nik)->first();
                if (!$employee) {
                    Log::warning('⚠️ Employee not found', ['nik' => $nik]);
                    continue; // Skip this employee
                }

                // Create work order
                $wo = new WorkOrderFacilities();
                $wo->ticket_num = $ticketNum;
                $wo->requester_id = Auth::id(); // NULL if guest
                $wo->requester_nik = $nik;
                $wo->requester_name = $requesterNames[$index] ?? $employee->name;
                $wo->requester_division = $requesterDivisions[$index] ?? $employee->department;
                $wo->plant = $plantName;
                $wo->machine_id = $machineId;
                $wo->machine_name = $machineName;
                $wo->location_details = $request->location_detail ?? '-';
                $wo->report_date = $request->report_date ? Carbon::parse($request->report_date) : now();
                $wo->description = $request->description;
                $wo->category = $request->category;
                $wo->target_completion_date = $request->target_completion_date ? Carbon::parse($request->target_completion_date) : null;
                $wo->photo_path = $photoPath;

                // STATUS WORKFLOW
                $wo->status = 'pending';
                $wo->internal_status = 'waiting_spv'; // Menunggu approval supervisor

                $wo->save();

                $createdTickets[] = [
                    'ticket_num' => $ticketNum,
                    'requester_name' => $wo->requester_name,
                    'requester_nik' => $nik,
                ];

                Log::info('✅ Ticket created', [
                    'ticket_num' => $ticketNum,
                    'requester' => $wo->requester_name
                ]);
            }

            DB::commit();

            Log::info('✅ All tickets created successfully', [
                'count' => count($createdTickets),
                'tickets' => $createdTickets
            ]);

            return response()->json([
                'success' => true,
                'message' => count($createdTickets) . ' tiket berhasil dibuat. Menunggu persetujuan SPV.',
                'data' => $createdTickets
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Error creating tickets', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tiket: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- 4. UPDATE STATUS TICKET ---
    public function updateStatus(Request $request, $id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'note' => 'nullable|string',
            'actual_completion_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'facility_tech_ids' => 'nullable|array',
            'facility_tech_ids.*' => 'exists:facility_technicians,id',
        ]);

        // Update status
        $wo->status = $request->status;

        if ($request->filled('start_date')) {
            $wo->start_date = Carbon::parse($request->start_date);
        }

        if ($request->filled('actual_completion_date')) {
            $wo->actual_completion_date = Carbon::parse($request->actual_completion_date);
        }

        if ($request->filled('note')) {
            $wo->completion_note = $request->note;
        }

        // Update internal status based on status
        if ($request->status === 'in_progress') {
            $wo->internal_status = 'approved';
        } elseif ($request->status === 'completed') {
            $wo->internal_status = 'completed';
        }

        $wo->save();

        // Sync technicians if provided
        if ($request->has('facility_tech_ids')) {
            $wo->technicians()->sync($request->facility_tech_ids);
        }

        Log::info('✅ Ticket updated', [
            'ticket_num' => $wo->ticket_num,
            'status' => $wo->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status tiket berhasil diperbarui',
            'data' => $wo->load('technicians')
        ]);
    }

    // --- 5. APPROVE TICKET (BY SUPERVISOR) ---
    public function approve($id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        // Check if ticket is waiting for approval
        if ($wo->internal_status !== 'waiting_spv') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini tidak dalam status menunggu approval'
            ], 400);
        }

        // Update status
        $wo->internal_status = 'approved';
        $wo->status = 'pending'; // Ready to be worked on
        $wo->save();

        Log::info('✅ Ticket approved', [
            'ticket_num' => $wo->ticket_num,
            'approved_by' => Auth::user()->name ?? 'System'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil disetujui',
            'data' => $wo
        ]);
    }

    // --- 6. DECLINE TICKET (BY SUPERVISOR) ---
    public function decline($id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        // Check if ticket is waiting for approval
        if ($wo->internal_status !== 'waiting_spv') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini tidak dalam status menunggu approval'
            ], 400);
        }

        // Update status
        $wo->internal_status = 'declined';
        $wo->status = 'cancelled';
        $wo->save();

        Log::info('⚠️ Ticket declined', [
            'ticket_num' => $wo->ticket_num,
            'declined_by' => Auth::user()->name ?? 'System'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tiket ditolak',
            'data' => $wo
        ]);
    }

    // --- 7. GET MACHINES BY PLANT (WITH SUB PLANT FILTER) ---
    public function getMachinesByPlant($plantId, Request $request)
    {
        try {
            Log::info('🔍 Fetching machines', [
                'plant_id' => $plantId,
                'sub_plant' => $request->sub_plant ?? 'none'
            ]);

            // Validate plant exists
            $plant = Plant::find($plantId);
            if (!$plant) {
                Log::warning('⚠️ Plant not found', ['plant_id' => $plantId]);
                return response()->json([]);
            }

            // Build query - Simple and clean
            $query = Machine::where('plant_id', $plantId);

            // Filter by sub_plant if provided (for PE and MT plants)
            if ($request->filled('sub_plant')) {
                $query->where('sub_plant', $request->sub_plant);
                Log::info('📍 Filtering by sub_plant', ['sub_plant' => $request->sub_plant]);
            }

            // Get machines - only id and name
            $machines = $query->orderBy('name', 'asc')->get(['id', 'name']);

            Log::info('✅ Machines fetched', [
                'count' => $machines->count(),
                'plant_name' => $plant->name
            ]);

            return response()->json($machines);
        } catch (\Exception $e) {
            Log::error('❌ Error fetching machines', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Gagal memuat data mesin'
            ], 500);
        }
    }

    // --- 8. GET EMPLOYEE BY NIK ---
    public function getEmployeeByNik($nik)
    {
        Log::info('🔍 Fetching employee', ['nik' => $nik]);

        $employee = Employee::where('nik', $nik)->first();

        if (!$employee) {
            Log::warning('⚠️ Employee not found', ['nik' => $nik]);
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        Log::info('✅ Employee found', ['name' => $employee->name]);

        return response()->json([
            'nik' => $employee->nik,
            'name' => $employee->name,
            'department' => $employee->department,
            'department_name' => $employee->department_name ?? $employee->department,
        ]);
    }

    // --- 9. DELETE TICKET ---
    public function destroy($id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        // Delete photo if exists
        if ($wo->photo_path && \Storage::disk('public')->exists($wo->photo_path)) {
            \Storage::disk('public')->delete($wo->photo_path);
        }

        $wo->delete();

        Log::info('🗑️ Ticket deleted', ['ticket_num' => $wo->ticket_num]);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dihapus'
        ]);
    }
}
