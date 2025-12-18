@section('browser_title', 'Facilities Dashboard')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 -my-2">

            {{-- LEFT SIDE: BACK BUTTON & TITLE --}}
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="{{ route('fh.index') }}"
                    class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-white hover:bg-[#1E3A5F] hover:border-[#1E3A5F] transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>

                <h2
                    class="font-extrabold text-2xl text-[#1E3A5F] leading-tight uppercase tracking-wider flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
                    {{ __('Facilities Dashboard') }}
                </h2>
            </div>

            {{-- RIGHT SIDE: FILTER & EXPORT --}}
            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <form method="GET" action="{{ route('fh.dashboard') }}" class="flex items-center gap-2">
                    <div class="relative group">
                        <input type="month" name="month" value="{{ $selectedMonth ?? '' }}"
                            class="rounded-xl border border-slate-200 pl-4 pr-2 py-2 text-sm font-bold text-slate-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition shadow-sm hover:shadow-md cursor-pointer">
                    </div>
                    <button type="submit"
                        class="p-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                    </button>
                </form>

                <div class="relative">
                    <button onclick="toggleExportMenu()"
                        class="bg-[#1E3A5F] hover:bg-[#152a45] text-white px-5 py-2.5 rounded-xl font-bold text-sm uppercase shadow-lg shadow-blue-900/20 transition-all duration-300 flex items-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export
                    </button>
                    {{-- Export Menu Dropdown --}}
                    <div id="exportMenu"
                        class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl z-50 border border-slate-100 overflow-hidden transform transition-all origin-top-right">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-500 uppercase">Download Options</p>
                        </div>
                        <button onclick="exportToPDF(); toggleExportMenu();"
                            class="w-full text-left px-4 py-3 text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition-colors flex items-center gap-3">
                            <span class="text-lg">📄</span>
                            <span class="text-sm font-bold">Save as PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .animate-spin-slow {
            animation: spin 8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Smooth hover for cards */
        .hover-card-up {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-card-up:hover {
            transform: translateY(-5px);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="py-10 bg-[#F8FAFC] min-h-screen">
        <div id="dashboard-content" class="max-w-[95rem] mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. VIBRANT GRADIENT CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">

                {{-- CARD 1: TOTAL (Deep Ocean Blue) --}}
                <div
                    class="hover-card-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-xl shadow-blue-500/30">
                    {{-- Decorative Circle --}}
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
                    <div class="absolute -bottom-6 -left-6 h-24 w-24 rounded-full bg-black/10 blur-xl"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-blue-100 uppercase tracking-wider">Total Tiket</p>
                            <h3 class="mt-2 text-4xl font-black tracking-tight">{{ $countTotal }}</h3>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-blue-100">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-blue-300"></span>
                        Semua request masuk
                    </div>
                </div>

                {{-- CARD 2: PENDING (Vibrant Orange) --}}
                <div
                    class="hover-card-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 p-6 text-white shadow-xl shadow-orange-500/30">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-orange-100 uppercase tracking-wider">Pending</p>
                            <h3 class="mt-2 text-4xl font-black tracking-tight">{{ $countPending }}</h3>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-orange-100">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                        Menunggu tindakan
                    </div>
                </div>

                {{-- CARD 3: IN PROGRESS (Bright Cyan/Blue) --}}
                <div
                    class="hover-card-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 p-6 text-white shadow-xl shadow-cyan-500/30">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-cyan-100 uppercase tracking-wider">In Progress</p>
                            <h3 class="mt-2 text-4xl font-black tracking-tight">{{ $countProgress }}</h3>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white animate-spin-slow" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-cyan-100">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-white"></span>
                        Sedang dikerjakan
                    </div>
                </div>

                {{-- CARD 4: COMPLETED (Emerald Green) --}}
                <div
                    class="hover-card-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-xl shadow-emerald-500/30">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div class="w-full">
                            <p class="text-sm font-medium text-emerald-100 uppercase tracking-wider">Completed</p>
                            <div class="flex items-end justify-between w-full">
                                <h3 class="mt-2 text-4xl font-black tracking-tight">{{ $countDone }}</h3>
                                <span class="text-2xl font-bold opacity-80 mb-1">
                                    {{ $countTotal > 0 ? round(($countDone / $countTotal) * 100) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar Inside Card --}}
                    <div class="mt-4 w-full bg-black/20 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-white h-full rounded-full"
                            style="width: {{ $countTotal > 0 ? ($countDone / $countTotal) * 100 : 0 }}%"></div>
                    </div>
                </div>

                {{-- CARD 5: COMPLETION RATE (Vibrant Violet/Purple) --}}
                <div
                    class="hover-card-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 to-purple-600 p-6 text-white shadow-xl shadow-purple-500/30">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div class="w-full">
                            <p class="text-sm font-medium text-purple-100 uppercase tracking-wider">Rate
                                ({{ $selectedMonth ? date('M Y', strtotime($selectedMonth)) : 'All' }})</p>
                            <div class="flex items-end justify-between w-full">
                                <h3 class="mt-2 text-4xl font-black tracking-tight">{{ $completionPct }}%</h3>
                            </div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    {{-- Progress Bar Inside Card --}}
                    <div class="mt-4 w-full bg-black/20 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-white h-full rounded-full" style="width: {{ $completionPct }}%"></div>
                    </div>
                </div>

            </div>

            {{-- 2. CHARTS AREA --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Chart: Category --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-500 rounded-full"></span> Request Categories
                        </h4>
                    </div>
                    <div class="h-72"><canvas id="catChart"></canvas></div>
                </div>

                {{-- Chart: Status Distribution --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                            <span class="w-2 h-6 bg-amber-500 rounded-full"></span> Status Distribution
                        </h4>
                    </div>
                    <div class="h-72"><canvas id="statusChart"></canvas></div>
                </div>

                {{-- Chart: Plant --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                            <span class="w-2 h-6 bg-indigo-500 rounded-full"></span> Requests by Plant
                        </h4>
                    </div>
                    <div class="h-72"><canvas id="plantChart"></canvas></div>
                </div>

                {{-- Chart: Technician --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                            <span class="w-2 h-6 bg-purple-500 rounded-full"></span> Tech Assignments
                        </h4>
                    </div>
                    <div class="h-72"><canvas id="techChart"></canvas></div>
                </div>
            </div>

            {{-- 3. GANTT CHART --}}
            {{-- GANTT CHART SECTION --}}
            {{-- 4. GANTT CHART (BRUTAL & WARAS VERSION) --}}
            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden"
                x-data="{
                    expanded: [],
                    toggle(id) {
                        if (this.expanded.includes(id)) {
                            this.expanded = this.expanded.filter(x => x !== id);
                        } else {
                            this.expanded.push(id);
                        }
                    }
                }">

                {{-- Header Gantt --}}
                <div
                    class="px-8 py-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-lg text-slate-800">Timeline Pekerjaan</h4>
                            <p class="text-xs text-slate-500 font-medium">Monitoring
                                {{ $ganttStartDate->format('d M') }} s/d
                                {{ $ganttStartDate->copy()->addDays($ganttTotalDays - 1)->format('d M Y') }}</p>
                        </div>
                    </div>

                    {{-- Legend Warna --}}
                    <div class="flex flex-wrap gap-3 text-xs font-bold text-slate-600">
                        <div
                            class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai
                        </div>
                        <div
                            class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Proses
                        </div>
                        <div
                            class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Pending
                        </div>
                        <div
                            class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-rose-200 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span> Overdue/Lewat
                            Target
                        </div>
                    </div>
                </div>

                {{-- Scroll Container --}}
                <div class="relative w-full overflow-x-auto custom-scrollbar">
                    <div class="min-w-[1000px]"> {{-- Min Width agar tidak gepeng --}}

                        {{-- A. TIMELINE HEADER (DATES) --}}
                        <div
                            class="flex border-b border-slate-200 bg-slate-100/50 text-[10px] uppercase font-bold text-slate-500 sticky top-0 z-20">
                            {{-- Kolom Nama Kategori --}}
                            <div
                                class="w-64 flex-shrink-0 p-3 border-r border-slate-200 bg-slate-50 sticky left-0 z-30 shadow-sm">
                                Kategori / Task
                            </div>

                            {{-- Kolom Tanggal --}}
                            <div class="flex-grow flex relative">
                                @for ($i = 0; $i < $ganttTotalDays; $i++)
                                    @php
                                        $date = $ganttStartDate->copy()->addDays($i);
                                        $isToday = $date->isToday();
                                        $isWeekend = $date->isWeekend();
                                    @endphp
                                    <div
                                        class="flex-1 text-center py-2 border-r border-slate-200/60 {{ $isToday ? 'bg-blue-100/50 text-blue-700' : ($isWeekend ? 'bg-slate-50' : '') }}">
                                        <div class="text-xs">{{ $date->format('d') }}</div>
                                        <div class="font-normal opacity-70">{{ $date->format('D') }}</div>
                                    </div>
                                    {{-- Garis Penanda Hari Ini --}}
                                    @if ($isToday)
                                        <div class="absolute top-0 bottom-0 w-[2px] bg-red-400/30 z-10 border-l border-dashed border-red-400 pointer-events-none h-[1000px]"
                                            style="left: calc({{ ($i / $ganttTotalDays) * 100 }}% + (100% / {{ $ganttTotalDays }} / 2))">
                                            <span
                                                class="absolute top-0 left-1 text-[9px] font-bold text-red-500 bg-red-50 px-1 rounded">HARI
                                                INI</span>
                                        </div>
                                    @endif
                                @endfor
                            </div>
                        </div>

                        {{-- B. GANTT BODY --}}
                        <div class="divide-y divide-slate-100 bg-white">
                            @foreach ($groupedGantt as $group)
                                {{-- LEVEL 1: PARENT ROW (CATEGORY) --}}
                                <div class="group/row transition-colors hover:bg-slate-50">
                                    <div class="flex items-stretch h-12">

                                        {{-- Nama Group (Sticky Left) --}}
                                        <div class="w-64 flex-shrink-0 px-4 border-r border-slate-100 flex items-center justify-between cursor-pointer sticky left-0 bg-white group-hover/row:bg-slate-50 z-10"
                                            @click="toggle('{{ $group['id'] }}')">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-200"
                                                    :class="expanded.includes('{{ $group['id'] }}') ?
                                                        'rotate-90 text-blue-600 bg-blue-50' : ''">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </div>
                                                <span class="font-bold text-sm text-slate-700 truncate"
                                                    title="{{ $group['name'] }}">{{ $group['name'] }}</span>
                                            </div>

                                            {{-- Indikator Delay di Group --}}
                                            @if ($group['has_delay'])
                                                <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"
                                                    title="Ada tiket terlambat di kategori ini"></div>
                                            @endif
                                        </div>

                                        {{-- Timeline Bar Group --}}
                                        <div class="flex-grow relative w-full">
                                            @php
                                                // Hitung posisi & lebar
                                                $startDiff = $ganttStartDate->diffInDays($group['start'], false);
                                                $duration = $group['start']->diffInDays($group['end']) + 1;

                                                $leftPct = ($startDiff / $ganttTotalDays) * 100;
                                                $widthPct = ($duration / $ganttTotalDays) * 100;

                                                // Batasi agar tidak render di luar
                                                if ($leftPct < 0) {
                                                    $widthPct += $leftPct;
                                                    $leftPct = 0;
                                                }
                                                if ($leftPct + $widthPct > 100) {
                                                    $widthPct = 100 - $leftPct;
                                                }
                                            @endphp

                                            @if ($widthPct > 0)
                                                <div class="absolute top-1/2 -translate-y-1/2 h-6 rounded-lg bg-slate-200 border border-slate-300 shadow-sm flex items-center px-3 text-xs font-bold text-slate-600 cursor-pointer hover:bg-slate-300 transition-colors z-0"
                                                    style="left: {{ $leftPct }}%; width: {{ $widthPct }}%;"
                                                    @click="toggle('{{ $group['id'] }}')">
                                                    {{ $group['count'] }} Tiket
                                                </div>
                                            @endif

                                            {{-- Grid Lines Background --}}
                                            <div class="absolute inset-0 flex pointer-events-none -z-10">
                                                @for ($i = 0; $i < $ganttTotalDays; $i++)
                                                    <div class="flex-1 border-r border-slate-50"></div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- LEVEL 2: CHILD ROWS (ITEMS) --}}
                                <div x-show="expanded.includes('{{ $group['id'] }}')"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                                    x-transition:enter-end="opacity-100 translate-y-0 max-h-[500px]"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 max-h-[500px]"
                                    x-transition:leave-end="opacity-0 max-h-0"
                                    class="bg-slate-50/50 shadow-inner overflow-hidden">

                                    @foreach ($group['items'] as $item)
                                        <div
                                            class="flex items-center h-10 border-b border-slate-100/50 hover:bg-white transition-colors">

                                            {{-- Label Item --}}
                                            <div
                                                class="w-64 flex-shrink-0 px-4 pl-12 border-r border-slate-200 flex flex-col justify-center sticky left-0 bg-slate-50/50 z-10">
                                                <div class="text-[11px] font-bold text-slate-700 truncate font-mono">
                                                    {{ $item['ticket'] }}</div>
                                                <div class="text-[9px] text-slate-400 truncate"
                                                    title="{{ $item['pic'] }}">
                                                    {{ $item['pic'] ? Str::limit($item['pic'], 20) : 'Unassigned' }}
                                                </div>
                                            </div>

                                            {{-- Timeline Bar Item --}}
                                            <div class="flex-grow relative w-full h-full">
                                                @php
                                                    $itemStartDiff = $ganttStartDate->diffInDays($item['start'], false);
                                                    $itemDuration = $item['start']->diffInDays($item['end']) + 1;

                                                    $iLeft = ($itemStartDiff / $ganttTotalDays) * 100;
                                                    $iWidth = ($itemDuration / $ganttTotalDays) * 100;

                                                    // Clipping
                                                    if ($iLeft < 0) {
                                                        $iWidth += $iLeft;
                                                        $iLeft = 0;
                                                    }
                                                    if ($iLeft + $iWidth > 100) {
                                                        $iWidth = 100 - $iLeft;
                                                    }
                                                @endphp

                                                @if ($iWidth > 0)
                                                    {{-- Bar --}}
                                                    <div class="absolute top-1/2 -translate-y-1/2 h-3.5 rounded-full {{ $item['color'] }} shadow-sm hover:ring-2 hover:ring-offset-1 hover:ring-blue-200 transition-all cursor-help"
                                                        style="left: {{ $iLeft }}%; width: {{ $iWidth }}%; min-width: 10px;"
                                                        title="{{ $item['desc'] }} ({{ $item['start']->format('d M') }} - {{ $item['end']->format('d M') }})">
                                                    </div>
                                                @endif

                                                {{-- Grid Lines (Visual Helper) --}}
                                                <div class="absolute inset-0 flex pointer-events-none">
                                                    @for ($i = 0; $i < $ganttTotalDays; $i++)
                                                        <div class="flex-1 border-r border-slate-100/50"></div>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            @if ($groupedGantt->isEmpty())
                                <div class="p-8 text-center text-slate-400 italic">
                                    Tidak ada jadwal pekerjaan dalam periode ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- CHART JS SCRIPTS (Keep Logic intact) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.register(ChartDataLabels);

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            borderDash: [2, 4],
                            color: '#f1f5f9'
                        }
                    }
                }
            };

            // 1. Cat Chart
            if (@json($chartCatLabels ?? []).length > 0) {
                new Chart(document.getElementById('catChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartCatLabels),
                        datasets: [{
                            label: 'Total',
                            data: @json($chartCatValues),
                            backgroundColor: '#3B82F6',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y'
                    }
                });
            }

            // 2. Status Chart (Doughnut)
            const statusLabels = @json($chartStatusLabels ?? []);
            if (statusLabels.length > 0) {
                const statusColors = {
                    'pending': '#fbbf24',
                    'in_progress': '#3b82f6',
                    'completed': '#10b981',
                    'cancelled': '#ef4444'
                };
                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: @json($chartStatusValues ?? []),
                            backgroundColor: statusLabels.map(l => statusColors[l] ?? '#cbd5e1'),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            datalabels: {
                                color: '#fff',
                                font: {
                                    weight: 'bold'
                                },
                                formatter: (val, ctx) => {
                                    let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b,
                                        0);
                                    return (val * 100 / sum).toFixed(0) + "%";
                                }
                            }
                        }
                    }
                });
            }

            // 3. Plant Chart
            if (@json($chartPlantLabels ?? []).length > 0) {
                new Chart(document.getElementById('plantChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartPlantLabels),
                        datasets: [{
                            label: 'Reqs',
                            data: @json($chartPlantValues),
                            backgroundColor: '#6366f1',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y'
                    }
                });
            }

            // 4. Tech Chart
            if (@json($chartTechLabels ?? []).length > 0) {
                new Chart(document.getElementById('techChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartTechLabels),
                        datasets: [{
                            label: 'Assigns',
                            data: @json($chartTechValues),
                            backgroundColor: '#a855f7',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y'
                    }
                });
            }
        });

        function toggleExportMenu() {
            document.getElementById('exportMenu').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('exportMenu');
            if (menu && !e.target.closest('.relative')) menu.classList.add('hidden');
        });

        async function exportToPDF() {
            try {
                document.getElementById('exportMenu').classList.add('hidden');
                Swal.fire({
                    title: 'Generating PDF...',
                    didOpen: () => Swal.showLoading()
                });
                const element = document.getElementById('dashboard-content');
                const canvas = await html2canvas(element, {
                    scale: 1.5,
                    backgroundColor: '#F8FAFC'
                });
                const imgData = canvas.toDataURL('image/png');
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF('l', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('Facilities_Dashboard.pdf');
                Swal.fire({
                    icon: 'success',
                    title: 'Downloaded!',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (e) {
                Swal.fire('Error', e.message, 'error');
            }
        }
    </script>
</x-app-layout>
