<?php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str; // Tambahan untuk Str::slug / Str::random
use Carbon\Carbon;
use App\Exports\FacilitiesExport; // Pastikan ini ada jika pakai Excel
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini ada jika pakai Excel

// --- IMPORT MODEL DI SINI (DI LUAR CLASS) ---
use App\Models\User;
use App\Models\Facilities\WorkOrderFacilities;
use App\Models\Engineering\Plant;
use App\Models\Engineering\Machine;
use App\Models\FacilityTech; // Tambahkan jika perlu

// --- IMPORT NOTIFIKASI DI SINI ---
use App\Notifications\NewTicketCreated;
use App\Notifications\TicketStatusNotification; // Pastikan file ini ada

class FacilitiesController extends Controller
{
    private function getAccessMap()
    {
        return [
            // Format: 'ROLE' => ['Nama Divisi di DB 1', 'Nama Divisi di DB 2']
            'eng.admin' => ['Engineering', 'ENGINEERING', 'Teknik'], // Tambahkan variasi biar aman
            'ga.admin'  => ['General Affair', 'General Affairs', 'GA', 'HRD'],
            'mt.admin'  => ['Maintenance', 'MAINTENANCE', 'MT'],

            // Role Admin FH/Super Admin = ALL
            'super.admin' => ['ALL'],
            'fh.admin'    => ['ALL']
        ];
    }
    private function buildQuery(Request $request)
    {
        $query = WorkOrderFacilities::query();
        $user = Auth::user();

        // Logic Admin: Hanya 'fh.admin' dan 'super.admin' yang bisa lihat semua
        if ($user && $user->role !== 'fh.admin' && $user->role !== 'super.admin') {
            $query->where('requester_id', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_num', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);

        // Date Range
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        return $query->with(['user', 'technicians', 'machine'])->latest();
    }

    // --- MAIN PAGE (TABLE & FORM) ---
    // --- MAIN PAGE (TABLE & FORM) ---
    public function index(Request $request)
    {
        // 1. BASE QUERY
        $query = WorkOrderFacilities::query();
        $user = Auth::user();

        // --- LOGIKA FILTER AKSES BARU (MODIFIKASI DISINI) ---
        if ($user) {
            $role = $user->role;
            $accessMap = $this->getAccessMap();

            // 1. SUPER ADMIN (Bisa lihat segalanya untuk monitoring)
            if ($role === 'super.admin') {
                // Tidak ada filter, lihat semua
            }

            // 2. FACILITY ADMIN (fh.admin)
            // KUNCI: Hanya boleh lihat tiket yang SUDAH DI-APPROVE SPV
            elseif ($role === 'fh.admin') {
                $query->where('internal_status', '!=', 'waiting_spv');
                // Atau bisa juga: $query->where('status', '!=', 'draft');
            }

            // 3. ADMIN DIVISI (eng.admin, ga.admin, dll)
            elseif (array_key_exists($role, $accessMap)) {
                // Hanya lihat divisi sendiri (Termasuk yang belum diapprove, karena dia harus approve)
                $allowedDepts = $accessMap[$role];
                $query->whereIn('requester_division', $allowedDepts);
            }

            // 4. USER BIASA / STAFF
            else {
                $query->where('requester_id', $user->id);
            }
        }
        // ----------------------------------------------------

        // 2. FILTER SEARCH (Logika Lama - Tetap)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_num', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%");
            });
        }

        // 3. FILTER DROPDOWN (Logika Lama - Tetap)
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('plant_id')) {
            $plantName = Plant::find($request->plant_id)->name ?? '';
            $query->where('plant', $plantName);
        }

        // 4. LOGIKA EXPORT XLSX (Logika Lama - Tetap)
        if ($request->has('export') && $request->export == 'true') {
            if ($request->filled('selected_ids')) {
                $ids = explode(',', $request->selected_ids);
                $exportData = WorkOrderFacilities::with(['technicians', 'machine'])->whereIn('id', $ids)->get();
            } else {
                $exportData = $query->with(['technicians', 'machine'])->get();
            }
            return Excel::download(new FacilitiesExport($exportData), 'facilities_report_' . date('Ymd_His') . '.xlsx');
        }

        // 5. GET DATA UTAMA
        $workOrders = $query->with(['user', 'technicians', 'machine']) // Tambahkan 'user' kembali
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // DATA PENDUKUNG VIEW
        $plants = Plant::whereNotIn('name', ['QC FO', 'HC', 'GA', 'FA', 'IT', 'Sales', 'Marketing', 'RM Office', 'RM 1', 'RM 2', 'RM 3', 'RM 5', 'MT', 'FH', 'FO', 'QR', 'QC LAB', 'QC LV', 'QC MV', 'Autowire', 'Gudang Jadi', 'MC Cable', 'Konstruksi', 'Workshop Electric', 'Plant Tools'])->get();
        $machines = Machine::all();
        $technicians = FacilityTech::all();
        $pageIds = $workOrders->pluck('id')->toArray();

        // COUNTERS (Disesuaikan dengan logika filter di atas)
        // Kita clone query utama agar counternya akurat sesuai hak akses user
        $countTotal = (clone $query)->count();
        $countPending = (clone $query)->where('status', 'pending')->count();
        $countProgress = (clone $query)->where('status', 'in_progress')->count();
        $countDone = (clone $query)->where('status', 'completed')->count();

        $openTicket = null;
        if ($request->has('open_ticket_id')) {
            $openTicket = WorkOrderFacilities::with(['technicians', 'machine'])->find($request->open_ticket_id);
        }

        return view('Division.Facilities.Index', compact(
            'workOrders',
            'plants',
            'machines',
            'technicians',
            'countTotal',
            'countPending',
            'countProgress',
            'countDone',
            'pageIds',
            'openTicket'
        ));
    }

    // --- DASHBOARD (ADMIN STATS) ---
    public function dashboard(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['fh.admin', 'super.admin'])) {
            abort(403);
        }

        $query = WorkOrderFacilities::where('status', '!=', 'cancelled');
        $selectedMonth = null;
        $ganttStartDate = now()->subDays(7);
        $ganttTotalDays = 15;

        if ($request->filled('month')) {
            $selectedMonth = $request->month;
            try {
                $start = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
                $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
                $ganttStartDate = $start;
                $ganttTotalDays = $start->daysInMonth;
            } catch (\Exception $e) {
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        } else {
            $query->take(100);
        }

        $workOrders = $query->latest()->get();

        $countTotal = $workOrders->count();
        $countPending = $workOrders->where('status', 'pending')->count();
        $countProgress = $workOrders->where('status', 'in_progress')->count();
        $countDone = $workOrders->where('status', 'completed')->count();

        $catData = $workOrders->groupBy('category')->map->count();
        $chartCatLabels = $catData->keys()->toArray();
        $chartCatValues = $catData->values()->toArray();

        $statusData = $workOrders->groupBy('status')->map->count();
        $chartStatusLabels = $statusData->keys()->toArray();
        $chartStatusValues = $statusData->values()->toArray();

        $plantData = $workOrders->groupBy('plant')->map->count();
        $chartPlantLabels = $plantData->keys()->toArray();
        $chartPlantValues = $plantData->values()->toArray();

        $periodTotal = $workOrders->count();
        $periodCompleted = $workOrders->where('status', 'completed')->count();
        $completionPct = $periodTotal ? round(($periodCompleted / $periodTotal) * 100, 1) : 0;

        // GANTT CHART LOGIC
        $groupedGantt = $workOrders->groupBy('category')->map(function ($items, $category) {
            $minStart = $items->min(fn($i) => $i->start_date ? Carbon::parse($i->start_date) : $i->created_at);
            $maxEnd   = $items->max(fn($i) => $i->actual_completion_date ?? $i->target_completion_date);
            $hasDelay = $items->contains(function ($i) {
                $target = $i->target_completion_date ? Carbon::parse($i->target_completion_date) : now();
                return $i->status != 'completed' && $target->isPast();
            });

            return [
                'id' => Str::slug($category ?? 'uncategorized'),
                'name' => $category ?? 'Uncategorized',
                'count' => $items->count(),
                'start' => $minStart,
                'end' => $maxEnd,
                'has_delay' => $hasDelay,
                'items' => $items->map(function ($wo) {
                    $start = $wo->start_date ? Carbon::parse($wo->start_date) : $wo->created_at;
                    if ($wo->status == 'completed' && $wo->actual_completion_date) {
                        $end = Carbon::parse($wo->actual_completion_date);
                    } else {
                        $end = $wo->target_completion_date ? Carbon::parse($wo->target_completion_date) : now();
                    }
                    if ($end->lt($start)) $end = $start->copy();

                    $statusColor = match ($wo->status) {
                        'completed' => 'bg-emerald-500',
                        'in_progress' => 'bg-blue-500',
                        'pending' => 'bg-slate-400',
                        'cancelled' => 'bg-slate-200',
                        default => 'bg-slate-300'
                    };
                    $isDelayed = false;
                    if ($wo->status != 'completed' && $end->isPast()) {
                        $statusColor = 'bg-rose-500';
                        $isDelayed = true;
                    }
                    return [
                        'id' => $wo->id,
                        'ticket' => $wo->ticket_num,
                        'desc' => $wo->description,
                        'start' => $start,
                        'end' => $end,
                        'color' => $statusColor,
                        'is_delayed' => $isDelayed,
                        'pic' => $wo->technicians->pluck('name')->join(', '),
                    ];
                })->values()
            ];
        });

        $techData = [];
        foreach ($workOrders as $wo) {
            if ($wo->technicians && $wo->technicians->count() > 0) {
                foreach ($wo->technicians as $tech) {
                    if (!isset($techData[$tech->name])) {
                        $techData[$tech->name] = 0;
                    }
                    $techData[$tech->name]++;
                }
            }
        }
        arsort($techData);
        $chartTechLabels = collect($techData)->keys()->toArray();
        $chartTechValues = collect($techData)->values()->toArray();

        return view('Division.Facilities.Dashboard', compact(
            'workOrders',
            'countTotal',
            'countPending',
            'countProgress',
            'countDone',
            'chartCatLabels',
            'chartCatValues',
            'chartStatusLabels',
            'chartStatusValues',
            'chartPlantLabels',
            'chartPlantValues',
            'chartTechLabels',
            'chartTechValues',
            'completionPct',
            'selectedMonth',
            'groupedGantt',
            'ganttStartDate',
            'ganttTotalDays'
        ));
    }

    // --- STORE ---
    public function store(Request $request)
    {
        // 1. VALIDASI
        $request->validate([
            'plant_id'           => 'required',
            'category'           => 'required',
            'description'        => 'required',
            'requester_nik'      => 'required|array|min:1',
            'requester_name'     => 'required|array',
            'requester_division' => 'required|array',
            'photo'              => 'image|max:5120|nullable'
        ]);

        try {
            return DB::transaction(function () use ($request) {

                // 2. OLAH DATA MULTIPLE REQUESTER (GUEST MODE)
                $niks  = $request->input('requester_nik');
                $names = $request->input('requester_name');
                $divs  = $request->input('requester_division');

                // Gabung jadi string: "Fairuz (12345), Budi (67890)"
                $combinedNames = [];
                foreach ($names as $key => $name) {
                    $nik = $niks[$key] ?? 'N/A';
                    $combinedNames[] = trim($name) . " (" . trim($nik) . ")";
                }

                $finalRequesterName = implode(', ', $combinedNames);
                $finalRequesterNik  = implode(', ', $niks);
                $mainDivision       = $divs[0] ?? '-';

                // 3. GENERATE NOMOR TIKET
                $bulanIndo = ['01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MEI', '06' => 'JUN', '07' => 'JUL', '08' => 'AGU', '09' => 'SEP', '10' => 'OKT', '11' => 'NOV', '12' => 'DES'];
                $prefix = 'FAC-' . date('d') . $bulanIndo[date('m')] . date('y') . '-';

                $lastTicket = WorkOrderFacilities::where('ticket_num', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $newSeq = $lastTicket ? ((int)substr($lastTicket->ticket_num, -3) + 1) : 1;
                $ticketNum = $prefix . sprintf('%03d', $newSeq);

                // 4. AMBIL DATA PLANT
                $plantObj = Plant::find($request->plant_id);
                $plantName = $plantObj ? $plantObj->name : '-';

                // 5. UPLOAD FOTO
                $photoPath = $request->hasFile('photo') ?
                    $request->file('photo')->store('wo_facilities', 'public') : null;

                // 6. SIMPAN KE DATABASE
                $wo = WorkOrderFacilities::create([
                    'ticket_num'         => $ticketNum,
                    'requester_id'       => null, // Null karena multiple/guest
                    'requester_nik'      => $finalRequesterNik,
                    'requester_name'     => $finalRequesterName,
                    'requester_division' => $mainDivision,
                    'plant'              => $plantName,
                    'description'        => $request->description,
                    'category'           => $request->category,
                    'machine_id'         => $request->machine_id,
                    'new_machine_name'   => $request->new_machine_name,
                    'photo_path'         => $photoPath,
                    'target_completion_date' => $request->target_completion_date,
                    'status'             => 'draft',
                    'internal_status'    => 'waiting_spv'
                ]);

                return response()->json([
                    'message' => 'Tiket berhasil dibuat',
                    'data'    => $wo
                ], 201);
            });
        } catch (\Exception $e) {
            // Tulis error ke log laravel agar bisa dibaca di storage/logs/laravel.log
            Log::error("FATAL ERROR STORE WO: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    // Approval SPV
    // --- HALAMAN LIST APPROVAL (BERDASARKAN ROLE) ---
    public function approvalIndex()
    {
        $user = Auth::user();
        $role = $user->role;
        $tickets = \App\Models\Facilities\WorkOrderFacilities::where('internal_status', 'waiting_spv')->get();

        // 2. Tampilkan Data Mentah di Layar
        // dd([
        //     'A. PERAN ANDA' => $role,

        //     'B. MAPPING DI KODINGAN' => $this->getAccessMap()[$role] ?? 'TIDAK ADA DI MAP',

        //     'C. NAMA DIVISI YANG ADA DI 70 TIKET TSB' => $tickets->pluck('requester_division')->unique()->values()->toArray()
        // ]);
        // 1. Ambil Mapping dari function private
        $accessMap = $this->getAccessMap();

        // 2. Cek apakah role user punya akses?
        // Kita cek apakah key role ada di array, ATAU dia admin global
        $isGlobalAdmin = in_array($role, ['super.admin', 'fh.admin']);

        if (!array_key_exists($role, $accessMap) && !$isGlobalAdmin) {
            return redirect()->route('fh.index')
                ->with('error', 'Role Anda (' . $role . ') tidak memiliki akses approval.');
        }

        // 3. Tentukan Divisi yang Boleh Dilihat
        // Jika user tidak ada di map tapi lolos validasi (berarti super admin), kita anggap ALL
        $allowedDepts = $accessMap[$role] ?? ['ALL'];

        // 4. Query Tiket
        $query = \App\Models\Facilities\WorkOrderFacilities::with('user')
            ->where('internal_status', 'waiting_spv')
            ->orderBy('created_at', 'desc');

        // Jika bukan ALL, filter whereIn
        if (!in_array('ALL', $allowedDepts)) {
            $query->whereIn('requester_division', $allowedDepts);
        }

        $approvals = $query->get();

        return view('Division.Facilities.Approval', compact('approvals', 'role'));
    }

    // --- ACTION APPROVE TIKET ---
    public function approve($id)
    {
        $user = Auth::user();
        $role = $user->role;
        $wo = WorkOrderFacilities::findOrFail($id);

        // 1. Ambil Mapping (Konsisten dengan Index)
        $accessMap = $this->getAccessMap();

        // 2. Cek Hak Akses Super Admin (Bypass)
        $isSuper = in_array($role, ['super.admin', 'fh.admin']);

        // 3. Cek Hak Akses Per Divisi
        if (!$isSuper) {
            // Ambil daftar divisi yang boleh dihandle user ini
            $myAllowedDepts = $accessMap[$role] ?? [];

            // Security Check: Apakah divisi tiket ini ada dalam daftar izin user?
            if (!in_array($wo->requester_division, $myAllowedDepts)) {
                return redirect()->back()->with(
                    'error',
                    'Akses Ditolak! Anda (' . $role . ') hanya boleh menyetujui divisi: ' . implode(', ', $myAllowedDepts) .
                        '. Sedangkan tiket ini dari: ' . $wo->requester_division
                );
            }
        }

        // 4. Eksekusi Approval
        $wo->update([
            'internal_status' => 'approved',
            'status' => 'pending',
            'approved_at' => now(),
            'approved_by' => $user->id
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil disetujui!');
    }
    public function decline($id)
    {
        // 1. Cari data tiket
        $workOrder = WorkOrderFacilities::findOrFail($id);

        // 2. Ubah status (sesuaikan dengan flow bisnis Anda, misal: 'cancelled' atau 'rejected')
        $workOrder->status = 'cancelled';
        $workOrder->internal_status = 'rejected_by_admin'; // Opsional, jika ada internal status
        $workOrder->save();

        // 3. Kembali ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Tiket berhasil ditolak.');
    }
    // --- UPDATE STATUS ---
    public function updateStatus(Request $request, $id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        $rules = [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'facility_tech_ids' => 'nullable',
            'note' => 'nullable|string'
        ];
        if ($request->status === 'completed') {
            $rules['actual_completion_date'] = 'required|date';
        }
        $request->validate($rules);

        $wo->status = $request->status;

        // Logic Tanggal & Note
        if ($request->status === 'completed') {
            $wo->actual_completion_date = $request->actual_completion_date;
            $wo->completion_note = $request->note;
        } elseif ($request->status === 'in_progress') {
            if ($request->filled('start_date')) {
                $wo->start_date = $request->start_date;
            } elseif (is_null($wo->start_date)) {
                $wo->start_date = now();
            }
            $wo->actual_completion_date = null;
            $wo->completion_note = null;
        } else {
            $wo->actual_completion_date = null;
            $wo->completion_note = null;
        }

        // Logic Teknisi
        $ids = $request->input('facility_tech_ids', []);
        if (!is_array($ids)) $ids = explode(',', (string)$ids);
        $ids = array_filter($ids, function ($value) {
            return is_numeric($value) && $value > 0;
        });
        $wo->technicians()->sync($ids);

        // Logic Pemroses
        if (!$wo->processed_by && Auth::check()) {
            $wo->processed_by = Auth::id();
            $wo->processed_by_name = Auth::user()->name;
        }

        $wo->save();

        // LOGIKA NOTIFIKASI STATUS UPDATE
        try {
            $requester = User::find($wo->requester_id);
            if ($requester) {
                // Gunakan Notifikasi Status (TicketStatusNotification)
                // Pastikan class ini di import di atas!
                $requester->notify(new TicketStatusNotification($wo));
            }
        } catch (\Exception $e) {
            Log::error('Gagal kirim notifikasi update status: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Status updated successfully!', 'data' => $wo]);
        }

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
