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
use App\Notifications\TicketApprovalRequest;
use App\Notifications\TicketReadyForAction;
use App\Notifications\TicketStatusUpdate;

class FacilitiesController extends Controller
{
    private function getAccessMap()
    {
        // Kita petakan secara manual: Role A boleh melihat Divisi X, Y, Z
        return [
            'eng.admin'  => ['ENGINEER', 'ENGINEERING'],
            'ga.admin'   => ['GA', 'GENERAL AFFAIR'],
            'fh.admin' => ['Facilities', 'FH', 'fh', 'FACILITIES', 'Fh'],
            'mt.admin' => ['Maintenance', 'MAINTENANCE', 'MT', 'mt', 'Mt']
            // Tambahkan role lainnya di sini sesuai database Anda
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
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = WorkOrderFacilities::query();
        $accessMap = $this->getAccessMap();

        $role = $user ? $user->role : null;
        // if ($role == 'eng.admin') {
        //     dd([
        //         'Role_User' => $role,
        //         'Is_Key_Exists' => array_key_exists($role, $accessMap),
        //         'Isi_Map_Untuk_Role_Ini' => $accessMap[$role] ?? 'KOSONG',
        //         'Semua_Key_Di_Map' => array_keys($accessMap)
        //     ]);
        // }
        // --- 1. LOGIKA AKSES DATA ---
        if ($user) {
            // Facility Admin bisa melihat semua report yang statusnya != waiting_spv
            if ($role == 'fh.admin' || $role == 'super.admin') {
                $query->where('internal_status', '!=', 'waiting_spv');
            }
            // Admin divisi hanya melihat dan approve dari divisi sendiri
            else if (array_key_exists($role, $accessMap)) {
                $allowedDepts = $accessMap[$role];
                $query->where(function ($q) use ($allowedDepts) {
                    foreach ($allowedDepts as $depts) {
                        $q->orWhereRaw('UPPER(requester_division) =?', [strtoupper($depts)]);
                    }
                });
            } else {
                $userNik = $user->employee->nik ?? 'NONE';
                $query->where('requester_nik', 'like', "%{$userNik}%");
            }
        } else {
        }

        // --- 2. FILTER SEARCH & DROPDOWN ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_num', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'waiting_spv') {
                $query->where('internal_status', 'waiting_spv');
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('plant_id')) {
            $plant = Plant::find($request->plant_id);
            if ($plant) $query->where('plant', $plant->name);
        }

        // --- 3. LOGIKA EXPORT ---
        if ($request->get('export') === 'true') {
            $exportData = $request->filled('selected_ids')
                ? WorkOrderFacilities::with(['technicians', 'machine'])->whereIn('id', explode(',', $request->selected_ids))->get()
                : (clone $query)->with(['technicians', 'machine'])->get();

            return Excel::download(new FacilitiesExport($exportData), 'facilities_report_' . date('Ymd_His') . '.xlsx');
        }

        // --- 4. COUNTERS (Berdasarkan Query yang sudah terfilter akses) ---
        $countTotal = (clone $query)->count();
        $countWaitingSpv = (clone $query)->where('internal_status', 'waiting_spv')->count();
        $countPending = (clone $query)->where('status', 'pending')->count();
        $countProgress = (clone $query)->where('status', 'in_progress')->count();
        $countDone = (clone $query)->where('status', 'completed')->count();

        // --- 5. GET DATA & PAGING ---
        $workOrders = $query->with(['user', 'technicians', 'machine'])
            ->latest()
            ->paginate(7)
            ->onEachSide(1)
            ->withQueryString();

        $pageIds = $workOrders->pluck('id')->toArray();
        // Data pendukung untuk Modal/Dropdown

        $plants = Plant::whereNotIn('name', ['QC FO', 'HC', 'FA', 'IT', 'Sales', 'Marketing', 'RM Office', 'RM 1', 'RM 2', 'RM 3', 'RM 5', 'FH', 'FO', 'QR', 'QC LAB', 'QC LV', 'QC MV', 'Autowire', 'Gudang Jadi', 'MC Cable', 'Konstruksi', 'Workshop Electric', 'Plant Tools'])->get();
        $machines = Machine::all();
        $technicians = FacilityTech::all();

        // Safety check untuk open_ticket_id (Jangan sampai bisa buka tiket divisi lain via URL)
        $openTicket = null;
        if ($request->filled('open_ticket_id')) {
            $openTicket = (clone $query)->with(['technicians', 'machine'])->find($request->open_ticket_id);
        }

        return view('Division.Facilities.Index', compact(
            'workOrders',
            'plants',
            'machines',
            'technicians',
            'pageIds', // <--- Pastikan variabel ini dikirim
            'countTotal',
            'countWaitingSpv',
            'countPending',
            'countProgress',
            'countDone',
            'openTicket'
        ));
    }

    // --- DASHBOARD (ADMIN STATS) ---
    public function dashboard(Request $request)
    {
        // Cek Role
        if (!Auth::check() || !in_array(Auth::user()->role, ['fh.admin', 'super.admin'])) {
            abort(403);
        }

        $query = WorkOrderFacilities::query();

        // [PERBAIKAN 1] Inisialisasi variabel agar tidak Undefined saat compact()
        $selectedMonth = null;

        // Gantt defaults
        $ganttStartDate = now()->subDays(7);
        $ganttTotalDays = 15;
        $isGanttDefault = true; // Penanda apakah pakai default

        // 1. FILTER LOGIC
        if ($request->filled('month')) {
            $selectedMonth = $request->month;
            try {
                $start = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
                $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);

                $ganttStartDate = $start;
                $ganttTotalDays = $start->daysInMonth;
                $isGanttDefault = false;
            } catch (\Exception $e) {
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);

            // Opsional: Sesuaikan gantt dengan range tanggal filter
            try {
                $start = Carbon::parse($request->start_date);
                $end = Carbon::parse($request->end_date);
                $ganttStartDate = $start;
                $ganttTotalDays = $start->diffInDays($end) + 1;
                $isGanttDefault = false;
            } catch (\Exception $e) {
            }
        }

        // =========================================================================
        // A. DATA UNTUK STATS & CHART (Ambil Semua Data menggunakan get())
        // =========================================================================
        $allData = (clone $query)->latest()->get();

        // 1. Total: Filter 'cancelled' secara eksplisit
        $countTotal = $allData->filter(function ($item) {
            return strtolower($item->status) !== 'cancelled';
        })->count();

        // 2. Counter Status Lainnya
        $countPending   = $allData->where('status', 'pending')->count();
        $countProgress  = $allData->where('status', 'in_progress')->count();
        $countDone      = $allData->where('status', 'completed')->count();

        // 3. Hitung Waiting Approval
        $countWaitingSpv = $allData->filter(function ($wo) {
            return in_array($wo->internal_status, ['waiting_spv', 'waiting_facility_approval']);
        })->count();

        // 4. Data untuk Chart.js
        $catData    = $allData->groupBy('category')->map->count();
        $statusData = $allData->groupBy('status')->map->count();
        $plantData  = $allData->groupBy('plant')->map->count();

        $techData = [];
        foreach ($allData as $wo) {
            if ($wo->technicians) {
                foreach ($wo->technicians as $tech) {
                    $techData[$tech->name] = ($techData[$tech->name] ?? 0) + 1;
                }
            }
        }
        arsort($techData);

        $chartData = [
            'catLabels'    => $catData->keys()->toArray(),
            'catValues'    => $catData->values()->toArray(),
            'statusLabels' => $statusData->keys()->toArray(),
            'statusValues' => $statusData->values()->toArray(),
            'plantLabels'  => $plantData->keys()->toArray(),
            'plantValues'  => $plantData->values()->toArray(),
            'techLabels'   => collect($techData)->keys()->toArray(),
            'techValues'   => collect($techData)->values()->toArray(),
        ];

        // Completion Rate
        $periodTotal     = $countTotal;
        $periodCompleted = $countDone;
        $completionPct   = $periodTotal ? round(($periodCompleted / $periodTotal) * 100, 1) : 0;

        // Gantt Chart Logic (Pakai $allData)
        $groupedGantt = $allData->groupBy('category')->map(function ($items, $category) {
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

        // =========================================================================
        // B. DATA UNTUK TABEL (Paginate)
        // =========================================================================
        $workOrders = $query->latest()->paginate(20)->onEachSide(1);

        return view('Division.Facilities.Dashboard', compact(
            'workOrders',
            'countTotal',
            'countPending',
            'countProgress',
            'countDone',
            'countWaitingSpv',
            'completionPct',
            'selectedMonth', // <-- Variabel ini sekarang aman karena sudah di-init null
            'groupedGantt',
            'ganttStartDate',
            'ganttTotalDays',
            'chartData'
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
            'requester_email'    => 'nullable|email', // Validasi format email (boleh kosong)
            'photo'              => 'image|max:5120|nullable'
        ]);

        try {
            return DB::transaction(function () use ($request) {

                // === LOGIKA 1: TENTUKAN PLANT ID TIKET ===
                // Jika Pemasangan Mesin, lokasi tiket = target_plant_id (Lokasi Pasang)
                // Jika perbaikan biasa, lokasi tiket = plant_id (Lokasi User)
                $finalPlantId = $request->plant_id;

                if ($request->category === 'Pemasangan Mesin') {
                    // Prioritaskan target_plant_id yang dikirim dari dropdown "Mau dipasang di Plant mana?"
                    if ($request->target_plant_id) {
                        $finalPlantId = $request->target_plant_id;
                    }
                }

                // === LOGIKA 2: OLAH DATA MULTIPLE REQUESTER (GUEST MODE) ===
                $niks  = $request->input('requester_nik');
                $names = $request->input('requester_name');
                $divs  = $request->input('requester_division');

                // Gabung jadi string
                $combinedNames = [];
                foreach ($names as $key => $name) {
                    $nik = $niks[$key] ?? 'N/A';
                    $combinedNames[] = trim($name) . " (" . trim($nik) . ")";
                }

                $finalRequesterName = implode(', ', $combinedNames);
                $finalRequesterNik  = implode(', ', $niks);
                // Ambil divisi pertama sebagai divisi utama tiket
                $mainDivision       = strtoupper($divs[0] ?? '-');

                // === LOGIKA 3: GENERATE NOMOR TIKET ===
                $bulanIndo = ['01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MEI', '06' => 'JUN', '07' => 'JUL', '08' => 'AGU', '09' => 'SEP', '10' => 'OKT', '11' => 'NOV', '12' => 'DES'];
                $prefix = 'FAC-' . date('d') . $bulanIndo[date('m')] . date('y') . '-';

                $lastTicket = WorkOrderFacilities::where('ticket_num', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $newSeq = $lastTicket ? ((int)substr($lastTicket->ticket_num, -3) + 1) : 1;
                $ticketNum = $prefix . sprintf('%03d', $newSeq);

                // === LOGIKA 4: AMBIL DATA NAMA PLANT ===
                $plantObj = \App\Models\Engineering\Plant::find($finalPlantId);
                $plantName = $plantObj ? $plantObj->name : '-';

                // === LOGIKA 5: UPLOAD FOTO ===
                $photoPath = $request->hasFile('photo') ?
                    $request->file('photo')->store('wo_facilities', 'public') : null;

                // === LOGIKA 6: SIMPAN TIKET KE DATABASE ===
                $wo = WorkOrderFacilities::create([
                    'ticket_num'         => $ticketNum,
                    'requester_id'       => null,
                    'requester_nik'      => $finalRequesterNik,
                    'requester_name'     => $finalRequesterName,
                    'requester_division' => $mainDivision,

                    // [FIX] Simpan inputan email dari form (bukan string validasi)
                    'requester_email'    => $request->requester_email,

                    // Lokasi fisik tiket (Target Plant)
                    'plant'              => $plantName,
                    'plant_id'           => $finalPlantId,

                    'description'        => $request->description,
                    'category'           => $request->category,

                    // Logic ID Mesin (Null jika pemasangan baru)
                    'machine_id'         => $request->category === 'Pemasangan Mesin' ? null : $request->machine_id,
                    'new_machine_name'   => $request->new_machine_name,

                    'photo_path'         => $photoPath,
                    'target_completion_date' => $request->target_completion_date,

                    // Status awal selalu Waiting SPV (Butuh Approval)
                    'status'             => 'draft',
                    'internal_status'    => 'waiting_spv'
                ]);

                // =================================================================
                // === LOGIKA 7: NOTIFIKASI EMAIL ===
                // =================================================================

                // A. KIRIM KE ADMIN SPV (Minta Approval)
                try {
                    $targetRole = null;
                    $div = strtoupper($wo->requester_division);

                    // Mapping Divisi ke Role Admin
                    if (in_array($div, ['GA', 'GENERAL AFFAIR'])) $targetRole = 'ga.admin';
                    elseif (in_array($div, ['ENGINEERING', 'ENG'])) $targetRole = 'eng.admin';
                    elseif (in_array($div, ['MAINTENANCE', 'MT'])) $targetRole = 'mt.admin';

                    if ($targetRole) {
                        // Cari user di database (Otomatis ambil email user)
                        $admins = User::where('role', $targetRole)->get();
                        if ($admins->count() > 0) {
                            Notification::send($admins, new TicketApprovalRequest($wo));
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Err Notif Admin: " . $e->getMessage());
                }

                // B. [BARU] KIRIM KE REQUESTER (Konfirmasi Laporan Diterima)
                // Memanggil helper yang ada di bawah controller
                $this->notifyRequester($wo, 'created');

                // === RETURN SUCCESS ===
                return response()->json([
                    'message' => 'Tiket berhasil dibuat dan menunggu approval SPV.',
                    'data'    => $wo
                ], 201);
            });
        } catch (\Exception $e) {
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
            $myAllowedDepts = $accessMap[$role] ?? [];

            // --- PERBAIKAN DI SINI: Samakan semua ke UPPERCASE ---
            $myDeptsUpper = array_map('strtoupper', array_map('trim', $myAllowedDepts));
            $woDeptUpper = strtoupper(trim($wo->requester_division));

            if (!in_array($woDeptUpper, $myDeptsUpper)) {
                return redirect()->back()->with(
                    'error',
                    'Akses Ditolak! Anda (' . $role . ') tidak memiliki izin untuk divisi: ' . $wo->requester_division
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

        try {
            $fhAdmins = User::whereIn('role', ['fh.admin', 'super.admin'])->get();
            Notification::send($fhAdmins, new TicketReadyForAction($wo));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return redirect()->back()->with('success', 'Tiket disetujui & Notifikasi dikirim ke Facilities!');
    }
    public function decline($id)
    {
        // 1. Cari data tiket
        $workOrder = WorkOrderFacilities::findOrFail($id);

        // 2. Ubah status (sesuaikan dengan flow bisnis Anda, misal: 'cancelled' atau 'rejected')
        $workOrder->status = 'cancelled';
        $workOrder->internal_status = 'rejected_by_admin'; // Opsional, jika ada internal status
        $workOrder->save();

        $this->notifyRequester($wo, 'cancelled');

        // 3. Kembali ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Tiket berhasil ditolak.');
    }
    // --- UPDATE STATUS ---
    // --- UPDATE STATUS (VERSI FIXED JAM & NOTE) ---
    public function updateStatus(Request $request, $id)
    {
        // 1. Ambil Data Tiket
        $wo = WorkOrderFacilities::findOrFail($id);

        // [TAMBAHAN PENTING] Simpan status lama sebelum diapa-apakan
        $originalStatus = $wo->status;

        // 2. Validasi Input
        $rules = [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'facility_tech_ids' => 'nullable',
            'note' => 'nullable|string',
            'completion_note' => 'nullable|string'
        ];

        if ($request->status === 'completed') {
            $rules['actual_completion_date'] = 'required|date';
        }

        $request->validate($rules);

        // 3. Set Status Baru
        $wo->status = $request->status;

        // =========================================================================
        // LOGIC UTAMA: JIKA STATUS DIUBAH JADI COMPLETED
        // =========================================================================
        if ($request->status === 'completed') {
            // ... (Kode Logic Mesin Baru & Note Tetap Sama, tidak perlu diubah) ...
            if ($wo->category === 'Pemasangan Mesin' && $wo->new_machine_name && is_null($wo->machine_id)) {
                // ... (Logic Create Machine) ...
                try {
                    $newMachine = \App\Models\Engineering\Machine::create([
                        'name'       => strtoupper(trim($wo->new_machine_name)),
                        'plant_id'   => $wo->plant_id,
                        'sub_plant'  => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $wo->machine_id = $newMachine->id;
                    $systemNote = "\n[SYSTEM]: Mesin baru '{$newMachine->name}' berhasil didaftarkan otomatis.";
                    $userNote = $request->note ?? $request->completion_note ?? '';
                    $request->merge(['note' => $userNote . $systemNote]);
                } catch (\Exception $e) {
                    // handle error
                }
            }

            // Logic Date & Note Completion
            $dateInput = $request->actual_completion_date;
            try {
                $cleanDate = \Carbon\Carbon::parse($dateInput)->format('Y-m-d');
            } catch (\Exception $e) {
                $cleanDate = now()->format('Y-m-d');
            }
            $timeNow = now()->format('H:i:s');
            $wo->actual_completion_date = $cleanDate . ' ' . $timeNow;
            $wo->completion_note = $request->note ?? $request->completion_note;
        } elseif ($request->status === 'cancelled') {
            // [BARU] Simpan alasan pembatalan ke completion_note
            $wo->completion_note = $request->note ?? $request->completion_note;

            // Reset tanggal selesai (karena batal)
            $wo->actual_completion_date = null;
            $wo->start_date = null;
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

        // 4. Logic Teknisi
        $ids = $request->input('facility_tech_ids', []);
        if (!is_array($ids)) $ids = explode(',', (string)$ids);
        $ids = array_filter($ids, function ($value) {
            return is_numeric($value) && $value > 0;
        });
        $wo->technicians()->sync($ids);

        // 5. Logic Pemroses
        if (!$wo->processed_by && Auth::check()) {
            $wo->processed_by = Auth::id();
            $wo->processed_by_name = Auth::user()->name;
        }

        // 6. Simpan
        $wo->save();

        // 7. NOTIFIKASI (ANTI DUPLIKAT)
        // Kita cek: Apakah status yang baru ($request->status) berbeda dengan status lama ($originalStatus)?
        // Jika SAMA (artinya user klik double), JANGAN kirim email lagi.

        if ($originalStatus !== $request->status) {
            try {
                $this->notifyRequester($wo, $request->status);
            } catch (\Exception $e) {
                Log::error('Email Error: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Status updated successfully!', 'data' => $wo]);
        }

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    // Helper untuk mencari email requester berdasarkan NIK & kirim notif
    private function notifyRequester($wo, $statusType)
    {
        // DEBUG 1: Cek apakah function ini terpanggil
        \Illuminate\Support\Facades\Log::info("🔔 Memulai notifyRequester untuk Tiket: " . $wo->ticket_num);
        \Illuminate\Support\Facades\Log::info("📧 Email di Database adalah: " . ($wo->requester_email ?? 'KOSONG/NULL'));

        // CEK 1: Prioritas Email Inputan
        if (!empty($wo->requester_email)) {
            try {
                \Illuminate\Support\Facades\Log::info("🚀 Mengirim ke email inputan: " . $wo->requester_email);

                \Illuminate\Support\Facades\Notification::route('mail', $wo->requester_email)
                    ->notify(new \App\Notifications\TicketStatusUpdate($wo, $statusType));

                return;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("❌ Gagal kirim ke email inputan: " . $e->getMessage());
            }
        } else {
            \Illuminate\Support\Facades\Log::warning("⚠️ Email inputan kosong, notifikasi dilewati.");
        }
    }
}
