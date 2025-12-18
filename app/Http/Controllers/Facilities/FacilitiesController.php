<?php

namespace App\Http\Controllers\Facilities;

use App\Events\TicketCreated;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facilities\WorkOrderFacilities; // Pastikan namespace ini benar sesuai folder Anda
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Engineering\Plant;
use App\Models\Engineering\Machine;
use App\Models\FacilityTech; // Model Teknisi Facility
use Maatwebsite\Excel\Facades\Excel; // PENTING: Import Excel
use App\Exports\FacilitiesExport;      // PENTING: Import Class Export tadi
use Illuminate\Support\Str;

class FacilitiesController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = WorkOrderFacilities::query();
        $user = Auth::user();

        // Logic Admin: Hanya 'fh.admin' dan 'super.admin' yang bisa lihat semua
        if ($user->role !== 'fh.admin' && $user->role !== 'super.admin') {
            $query->where('requester_id', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_num', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);

        // Date Range
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        // Eager Load 'facilityTech' jika relasi sudah dibuat di model WorkOrderFacilities
        return $query->with(['user', 'technicians', 'machine'])->latest();
    }

    // --- MAIN PAGE (TABLE & FORM) ---
    public function index(Request $request)
    {
        // 1. BASE QUERY
        $query = WorkOrderFacilities::query();
        $user = Auth::user();

        // 2. FILTER SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_num', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('plant', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%");
            });
        }

        // 3. FILTER DROPDOWN (Category, Status, Plant)
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('plant_id')) {
            $plantName = Plant::find($request->plant_id)->name ?? '';
            $query->where('plant', $plantName);
        }

        // Permission Check
        if ($user->role !== 'fh.admin' && $user->role !== 'super.admin') {
            $query->where('requester_id', $user->id);
        }

        // 4. LOGIKA EXPORT XLSX
        if ($request->has('export') && $request->export == 'true') {
            // Jika user memilih checkbox tertentu
            if ($request->filled('selected_ids')) {
                $ids = explode(',', $request->selected_ids);
                $exportData = WorkOrderFacilities::with(['technicians', 'machine'])->whereIn('id', $ids)->get();
            } else {
                // Jika tidak, export semua hasil filter saat ini
                $exportData = $query->with(['technicians', 'machine'])->get();
            }

            return Excel::download(new FacilitiesExport($exportData), 'facilities_report_' . date('Ymd_His') . '.xlsx');
        }

        // 5. GET DATA UTAMA
        $workOrders = $query->with(['user', 'technicians', 'machine'])
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Agar filter tidak hilang saat ganti halaman

        // DATA PENDUKUNG VIEW
        $plants = Plant::whereNotIn('name', ['PE', 'QC FO', 'HC', 'GA', 'FA', 'IT', 'Sales', 'Marketing', 'RM Office', 'RM 1', 'RM 2', 'RM 3', 'RM 5', 'MT', 'FH', 'FO', 'QR', 'QC LAB', 'QC LV', 'QC MV', 'Autowire', 'Gudang Jadi', 'MC Cable', 'Konstruksi', 'Workshop Electric', 'Plant Tools'])->get();
        $machines = Machine::all();
        $technicians = FacilityTech::all();
        $pageIds = $workOrders->pluck('id')->toArray();

        // COUNTERS
        $statsQuery = WorkOrderFacilities::query();
        if ($user->role !== 'fh.admin' && $user->role !== 'super.admin') {
            $statsQuery->where('requester_id', $user->id);
        }
        $countTotal = (clone $statsQuery)->count();
        $countPending = (clone $statsQuery)->where('status', 'pending')->count();
        $countProgress = (clone $statsQuery)->where('status', 'in_progress')->count();
        $countDone = (clone $statsQuery)->where('status', 'completed')->count();
        $openTicket = null;
        if ($request->has('open_ticket_id')) {
            // Cari tiket spesifik untuk langsung dibuka di modal
            $openTicket = \App\Models\Facilities\WorkOrderFacilities::with(['technicians', 'machine'])->find($request->open_ticket_id);
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
        // 1. CEK ROLE
        if (!in_array(Auth::user()->role, ['fh.admin', 'super.admin'])) {
            abort(403);
        }

        // 2. BASE QUERY
        $query = WorkOrderFacilities::where('status', '!=', 'cancelled');

        // 3. FILTER LOGIC
        $selectedMonth = null;

        // Default Time Range untuk Visual Gantt (15 Hari "Brutal View")
        // Kita set default H-7 sampai H+7 agar fokus ke pekerjaan sekarang.
        $ganttStartDate = now()->subDays(7);
        $ganttTotalDays = 15;

        if ($request->filled('month')) {
            $selectedMonth = $request->month;
            try {
                $start = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();

                $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);

                // Jika filter bulan aktif, Gantt chart mengikuti bulan tersebut
                $ganttStartDate = $start;
                $ganttTotalDays = $start->daysInMonth;
            } catch (\Exception $e) {
                // ignore invalid
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        } else {
            // Jika tidak ada filter, ambil 100 teratas agar tidak berat
            $query->take(100);
        }

        $workOrders = $query->latest()->get();

        // 4. COUNTERS & CHARTS (LOGIKA LAMA TETAP DIPAKAI)
        $countTotal = $workOrders->count();
        $countPending = $workOrders->where('status', 'pending')->count();
        $countProgress = $workOrders->where('status', 'in_progress')->count();
        $countDone = $workOrders->where('status', 'completed')->count();

        $catData = $workOrders->groupBy('category')->map->count();
        $chartCatLabels = $catData->keys();
        $chartCatValues = $catData->values();

        $statusData = $workOrders->groupBy('status')->map->count();
        $chartStatusLabels = $statusData->keys();
        $chartStatusValues = $statusData->values();

        $plantData = $workOrders->groupBy('plant')->map->count();
        $chartPlantLabels = $plantData->keys();
        $chartPlantValues = $plantData->values();

        $periodTotal = $workOrders->count();
        $periodCompleted = $workOrders->where('status', 'completed')->count();
        $completionPct = $periodTotal ? round(($periodCompleted / $periodTotal) * 100, 1) : 0;

        // 5. GANTT CHART LOGIC (BARU: GROUPED & OPTIMIZED)
        // Mengelompokkan tiket berdasarkan Kategori (Level 1)
        $groupedGantt = $workOrders->groupBy('category')->map(function ($items, $category) {

            // Cari tanggal min/max dalam grup untuk keperluan logic (opsional)
            $minStart = $items->min(fn($i) => $i->start_date ? Carbon::parse($i->start_date) : $i->created_at);
            $maxEnd   = $items->max(fn($i) => $i->actual_completion_date ?? $i->target_completion_date);

            // Cek apakah ada delay di grup ini
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
                    // Tentukan Tanggal Mulai & Selesai Item
                    $start = $wo->start_date ? Carbon::parse($wo->start_date) : $wo->created_at;

                    if ($wo->status == 'completed' && $wo->actual_completion_date) {
                        $end = Carbon::parse($wo->actual_completion_date);
                    } else {
                        $end = $wo->target_completion_date ? Carbon::parse($wo->target_completion_date) : now();
                    }

                    // Jika end < start, paksa sama (durasi 1 hari)
                    if ($end->lt($start)) $end = $start->copy();

                    // Logic Warna Status (Visual Waras)
                    $statusColor = match ($wo->status) {
                        'completed' => 'bg-emerald-500',
                        'in_progress' => 'bg-blue-500',
                        'pending' => 'bg-slate-400',
                        'cancelled' => 'bg-slate-200',
                        default => 'bg-slate-300'
                    };

                    // Override Merah jika Delay
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
                        'pic' => $wo->technicians->pluck('name')->join(', '), // Ambil nama teknisi
                    ];
                })->values() // Reset array keys setelah map
            ];
        });

        // 6. TECH CHART LOGIC (KEEP EXISTING)
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
        $chartTechLabels = collect($techData)->keys();
        $chartTechValues = collect($techData)->values();

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
            // Variable Baru untuk Gantt Chart "Brutal"
            'groupedGantt',
            'ganttStartDate', // Variable nama baru untuk view ($startDate)
            'ganttTotalDays'  // Variable nama baru untuk view ($totalDays)
        ));
    }

    // --- STORE ---
    public function store(Request $request)
    {
        // 1. Validasi Dasar
        $rules = [
            'requester_name' => 'required|string',
            'plant_id' => 'required',
            'description' => 'required',
            'category' => 'required',
            'photo' => 'image|max:5120'
        ];

        // 2. Validasi Tambahan Berdasarkan Kategori
        if ($request->category == 'Pemasangan Mesin') {
            // Jika pasang mesin baru, wajib isi nama mesin baru
            $rules['new_machine_name'] = 'required|string|max:255';
        }
        // [FIX] Tambahkan 'Pembuatan Alat Baru' ke array ini
        elseif (in_array($request->category, [
            'Modifikasi Mesin',
            'Pembongkaran Mesin',
            'Relokasi Mesin',
            'Perbaikan',
            'Pembuatan Alat Baru' // <-- DITAMBAHKAN
        ])) {
            // Jika kategori ini, wajib pilih mesin dari dropdown
            $rules['machine_id'] = 'required|exists:machines,id';
        }

        $request->validate($rules);

        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('wo_facilities', 'public') : null;

        // ... (Sisa kode ke bawah sama seperti sebelumnya) ...

        // 3. Generate Ticket Number
        $bulanIndo = [
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MEI',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AGU', // Agustus biasanya disingkat AGU atau AGS
            '09' => 'SEP',
            '10' => 'OKT', // October jadi OKT
            '11' => 'NOV',
            '12' => 'DES'  // December jadi DES
        ];

        // 2. Ambil komponen tanggal saat ini
        $hari  = date('d'); // 18
        $bulan = date('m'); // 12
        $tahun = date('y'); // 25

        // 3. Susun kodenya
        // Hasil: 18DES25
        $dateCode = $hari . $bulanIndo[$bulan] . $tahun;
        $prefix = 'FAC-' . $dateCode . '-';
        $lastTicket = WorkOrderFacilities::where('ticket_num', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $newSeq = $lastTicket ? ((int)substr($lastTicket->ticket_num, -3) + 1) : 1;
        $ticketNum = $prefix . sprintf('%03d', $newSeq);

        $plantObj = \App\Models\Engineering\Plant::find($request->plant_id);
        $plantName = $plantObj ? $plantObj->name : '-';

        // 5. LOGIKA PENENTUAN MESIN
        $machineId = null;
        $machineName = null;

        if ($request->category == 'Pemasangan Mesin') {
            // SKENARIO A: MESIN BARU (Create Master Data)
            $newMachine = \App\Models\Engineering\Machine::create([
                'plant_id' => $request->plant_id,
                'name' => $request->new_machine_name,
                'code' => 'NEW-' . strtoupper(\Illuminate\Support\Str::random(5)),
            ]);
            $machineId = $newMachine->id;
            $machineName = $newMachine->name;
        } else {
            // SKENARIO B: MESIN LAMA (Dari Dropdown)
            if ($request->filled('machine_id')) {
                $m = \App\Models\Engineering\Machine::find($request->machine_id);
                $machineId = $m->id;
                $machineName = $m->name;
            }
        }

        $wo = WorkOrderFacilities::create([
            'ticket_num' => $ticketNum,
            'requester_id' => Auth::id(),
            'requester_name' => $request->requester_name,
            'plant' => $plantName,
            'machine_id' => $machineId,
            'machine_name' => $machineName,
            'location_details' => $request->location_detail ?? '-',
            'report_date' => $request->report_date ? Carbon::parse($request->report_date) : now(),
            'report_time' => $request->report_time,
            'shift' => $request->shift,
            'description' => $request->description,
            'category' => $request->category,
            'target_completion_date' => $request->target_completion_date,
            'photo_path' => $photoPath,
            'status' => 'pending'
        ]);
        // $wo   = WorkOrderFacilities::create($data);
        event(new TicketCreated($wo));
        return redirect()->route('fh.index')->with('success', 'Request Created Successfully!');
    }

    // --- UPDATE STATUS (ACCEPT / ASSIGN TECH) ---
    // --- UPDATE STATUS ---
    // --- UPDATE STATUS ---
    public function updateStatus(Request $request, $id)
    {
        $wo = WorkOrderFacilities::findOrFail($id);

        // --- 1. VALIDASI INPUT ---
        // Kita validasi dulu sebelum memproses data
        $rules = [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'facility_tech_ids' => 'nullable', // Bisa array atau string
        ];

        // ATURAN KHUSUS: Jika status Completed, Tanggal Selesai (Actual) WAJIB diisi
        if ($request->status === 'completed') {
            $rules['actual_completion_date'] = 'required|date';
        }

        $request->validate($rules);

        // --- 2. UPDATE STATUS ---
        $wo->status = $request->status;

        // --- 3. LOGIKA TANGGAL (PENTING) ---
        if ($request->status === 'completed') {
            // AMBIL DARI INPUT MANUAL USER (Sesuai request Anda)
            $wo->actual_completion_date = $request->actual_completion_date;
        } elseif ($request->status === 'in_progress') {
            // Logic Start Date:
            // 1. Jika user input tanggal mulai manual, pakai itu.
            // 2. Jika tidak input manual DAN di database masih kosong, isi otomatis hari ini.
            if ($request->filled('start_date')) {
                $wo->start_date = $request->start_date;
            } elseif (is_null($wo->start_date)) {
                $wo->start_date = now();
            }

            // Reset tanggal selesai jika status dikembalikan ke in_progress
            $wo->actual_completion_date = null;
        } else {
            // Jika status Pending/Cancelled, reset tanggal selesai
            $wo->actual_completion_date = null;
        }

        // --- 4. SIMPAN TEKNISI (MULTIPLE) ---
        $ids = $request->input('facility_tech_ids', []);

        // Handling jika input berupa string "1,2" (kadang terjadi pada form multipart)
        if (!is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }

        // Filter angka valid saja
        $ids = array_filter($ids, function ($value) {
            return is_numeric($value) && $value > 0;
        });

        $wo->technicians()->sync($ids);

        // --- 5. CATAT PEMROSES (Jika belum ada) ---
        if (!$wo->processed_by) {
            $wo->processed_by = Auth::id();
            $wo->processed_by_name = Auth::user()->name;
        }

        $wo->save();

        return redirect()->back()->with('success', 'Status updated successfully!');
    }
}
