@section('browser_title', 'Facilities Work Order')

<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex justify-between items-center py-2">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] flex items-center justify-center text-white shadow-lg shadow-blue-900/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-2xl text-slate-800 tracking-tight">WO Facilities</h2>
                    <p class="text-xs text-slate-500 font-medium">Manage maintenance & repair tickets</p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- LIBRARIES --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- STYLE --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        [x-cloak] {
            display: none !important;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        input:focus,
        select:focus,
        textarea:focus {
            box-shadow: 0 4px 20px -2px rgba(59, 130, 246, 0.1);
        }
    </style>

    {{-- MAIN CONTENT --}}
    <div class="py-8 bg-[#F8FAFC] min-h-screen font-sans" x-data="facilitiesData">

        {{-- ALERT --}}
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '{{ session('success') }}',
                        confirmButtonColor: '#1E3A5F',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-3xl'
                        }
                    });
                });
            </script>
        @endif

        <div class="max-w-[95rem] mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. STATS OVERVIEW --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Total --}}
                <div
                    class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-xl shadow-blue-500/20 hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-blue-100 uppercase tracking-widest mb-1">Total Tiket</p>
                        <div class="flex items-end justify-between">
                            <h3 class="text-4xl font-black">{{ $countTotal }}</h3>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending --}}
                <div
                    class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-orange-500 to-amber-500 p-6 text-white shadow-xl shadow-orange-500/20 hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-orange-100 uppercase tracking-widest mb-1">Pending</p>
                        <div class="flex items-end justify-between">
                            <h3 class="text-4xl font-black">{{ $countPending }}</h3>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- In Progress --}}
                <div
                    class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-cyan-500 to-blue-500 p-6 text-white shadow-xl shadow-cyan-500/20 hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-cyan-100 uppercase tracking-widest mb-1">In Progress</p>
                        <div class="flex items-end justify-between">
                            <h3 class="text-4xl font-black">{{ $countProgress }}</h3>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Completed --}}
                <div
                    class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-xl shadow-emerald-500/20 hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative z-10 w-full">
                        <p class="text-xs font-bold text-emerald-100 uppercase tracking-widest mb-1">Completed</p>
                        <div class="flex items-end justify-between mb-2">
                            <h3 class="text-4xl font-black">{{ $countDone }}</h3>
                            <span
                                class="text-xl font-bold opacity-80">{{ $countTotal > 0 ? round(($countDone / $countTotal) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-black/20 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-white h-full rounded-full transition-all duration-1000"
                                style="width: {{ $countTotal > 0 ? ($countDone / $countTotal) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. TOOLBAR --}}
            <div class="bg-white rounded-[1.5rem] shadow-lg shadow-slate-200/50 border border-slate-100 p-5">
                <form action="{{ route('fh.index') }}" method="GET"
                    class="flex flex-col xl:flex-row gap-4 justify-between items-end xl:items-center">

                    <div class="flex flex-col lg:flex-row gap-3 w-full xl:w-auto flex-1">
                        <div class="relative w-full lg:w-64 group">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari tiket..."
                                class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium transition duration-200">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <select name="category" onchange="this.form.submit()"
                            class="w-full lg:w-48 rounded-2xl border border-slate-200 bg-slate-50 text-sm py-3 px-4 text-slate-600 font-medium focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer transition">
                            <option value="">Semua Kategori</option>
                            <option value="Modifikasi Mesin"
                                {{ request('category') == 'Modifikasi Mesin' ? 'selected' : '' }}>Modifikasi Mesin
                            </option>
                            <option value="Pemasangan Mesin"
                                {{ request('category') == 'Pemasangan Mesin' ? 'selected' : '' }}>Pemasangan Mesin
                            </option>
                            <option value="Pembongkaran Mesin"
                                {{ request('category') == 'Pembongkaran Mesin' ? 'selected' : '' }}>Pembongkaran Mesin
                            </option>
                            <option value="Relokasi Mesin"
                                {{ request('category') == 'Relokasi Mesin' ? 'selected' : '' }}>Relokasi Mesin</option>
                            <option value="Perbaikan" {{ request('category') == 'Perbaikan' ? 'selected' : '' }}>
                                Perbaikan</option>
                            <option value="Pembuatan Alat Baru"
                                {{ request('category') == 'Pembuatan Alat Baru' ? 'selected' : '' }}>Pembuatan Alat
                                Baru</option>
                            <option value="Rakit Steel Drum"
                                {{ request('category') == 'Rakit Steel Drum' ? 'selected' : '' }}>Rakit Steel Drum
                            </option>
                            <option value="Lain-Lain" {{ request('category') == 'Lain-Lain' ? 'selected' : '' }}>
                                Lain-Lain</option>
                        </select>

                        <select name="status" onchange="this.form.submit()"
                            class="w-full lg:w-40 rounded-2xl border border-slate-200 bg-slate-50 text-sm py-3 px-4 text-slate-600 font-medium focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer transition">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>

                        <select name="plant_id" onchange="this.form.submit()"
                            class="w-full lg:w-40 rounded-2xl border border-slate-200 bg-slate-50 text-sm py-3 px-4 text-slate-600 font-medium focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer transition">
                            <option value="">Semua Plant</option>
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}"
                                    {{ request('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}
                                </option>
                            @endforeach
                        </select>

                        <a href="{{ route('fh.index') }}"
                            class="px-4 py-3 rounded-2xl border border-slate-200 text-slate-500 hover:text-red-500 hover:bg-red-50 hover:border-red-200 transition text-sm font-bold flex items-center justify-center gap-2 bg-white shadow-sm hover:shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Reset
                        </a>
                    </div>

                    <div class="flex gap-3 w-full lg:w-auto">
                        @if (in_array(Auth::user()->role, ['fh.admin', 'super.admin']))
                            <a href="{{ route('fh.dashboard') }}"
                                class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 hover:text-blue-600 transition flex items-center gap-2 bg-white shadow-sm hover:shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                                Dashboard
                            </a>
                        @endif

                        <button type="button" @click="submitExport()"
                            class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 hover:text-green-600 transition flex items-center gap-2 bg-white shadow-sm hover:shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export
                        </button>

                        <button type="button" @click="resetForm(); showCreateModal = true"
                            class="px-6 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] hover:from-[#162c46] hover:to-[#1E3A5F] text-white rounded-2xl font-bold text-sm shadow-lg shadow-blue-900/20 transition transform active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Ticket
                        </button>
                    </div>
                </form>
            </div>

            {{-- 3. TABLE DATA (ZEBRA & ROUNDED) --}}
            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        {{-- Header Table --}}
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-5 w-12 text-center">
                                    <input type="checkbox" @change="toggleSelectAll()"
                                        :checked="selectedTickets.length === pageIds.length && pageIds.length > 0"
                                        class="rounded-md border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] cursor-pointer w-4 h-4">
                                </th>
                                <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                                    Tiket Info</th>
                                <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                                    Pemohon</th>
                                <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                                    Lokasi & Mesin</th>
                                <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                                    Kategori</th>
                                <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                                    Status & PIC</th>
                                <th
                                    class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">
                                    Aksi</th>
                            </tr>
                        </thead>
                        {{-- Body Table --}}
                        <tbody class="divide-y divide-slate-100">
                            @forelse($workOrders as $wo)
                                {{-- ZEBRA STRIPING: odd:bg-white, even:bg-slate-50 --}}
                                <tr
                                    class="group transition-all duration-200 hover:bg-blue-50 odd:bg-white even:bg-slate-100">
                                    <td class="px-6 py-4 text-center align-top pt-5">
                                        <input type="checkbox" value="{{ $wo->id }}" x-model="selectedTickets"
                                            class="rounded-md border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] cursor-pointer w-4 h-4">
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs group-hover:bg-blue-600 group-hover:text-white transition shadow-sm">
                                                WO
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-[#1E3A5F] text-sm group-hover:text-blue-600 transition">
                                                    {{ $wo->ticket_num }}</div>
                                                <div class="text-xs text-slate-400 mt-1 font-medium">
                                                    {{ $wo->report_date ? \Carbon\Carbon::parse($wo->report_date)->format('d M Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-bold text-slate-700 text-sm">{{ $wo->requester_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-bold text-slate-700 text-sm mb-1">{{ $wo->plant }}</div>
                                        @if ($wo->machine)
                                            <span
                                                class="inline-block px-2.5 py-1 rounded-lg border border-purple-100 bg-purple-50 text-[11px] font-bold text-purple-600">
                                                {{ $wo->machine->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <span
                                            class="inline-block px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-slate-600 shadow-sm">
                                            {{ $wo->category }}
                                        </span>
                                        <div class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed"
                                            title="{{ $wo->description }}">{{ $wo->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        @php
                                            $st = $wo->status;
                                            $cls = match ($st) {
                                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'in_progress' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
                                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full border {{ $cls }} text-[10px] font-bold uppercase tracking-wide shadow-sm">
                                            {{ str_replace('_', ' ', $st) }}
                                        </span>

                                        @if ($wo->technicians->count() > 0)
                                            <div class="mt-3 flex -space-x-2 overflow-hidden pl-1">
                                                @foreach ($wo->technicians->take(3) as $tech)
                                                    <div class="inline-flex h-7 w-7 rounded-full ring-2 ring-white bg-gradient-to-br from-slate-700 to-slate-800 items-center justify-center text-[9px] font-bold text-white shadow-sm"
                                                        title="{{ $tech->name }}">
                                                        {{ substr($tech->name, 0, 1) }}
                                                    </div>
                                                @endforeach
                                                @if ($wo->technicians->count() > 3)
                                                    <div
                                                        class="inline-flex h-7 w-7 rounded-full ring-2 ring-white bg-slate-200 items-center justify-center text-[9px] font-bold text-slate-600">
                                                        +{{ $wo->technicians->count() - 3 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-top text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                @click='ticket = @json($wo); showDetailModal = true'
                                                class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </button>
                                            @if (Auth::user()->role == 'fh.admin')
                                                <button @click='openEditModal(@json($wo))'
                                                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition border border-transparent hover:border-amber-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <div
                                                class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                    </path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium">Belum ada tiket yang tersedia.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 text-xs">
                    {{ $workOrders->links() }}
                </div>
            </div>

        </div>

        {{-- MODAL CREATE (SOFT & ROUNDED) --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    @click="showCreateModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 border-b border-white/10 flex justify-between items-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-white/5 pattern-dots"></div>
                            <h3 class="font-extrabold text-xl text-white tracking-tight relative z-10">Create New
                                Ticket</h3>
                            <button type="button" @click="showCreateModal = false"
                                class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition relative z-10"><svg
                                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg></button>
                        </div>
                        <form action="{{ route('fh.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                {{-- Form Content sama seperti sebelumnya tapi class rounded-xl --}}
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Requester Name</label>
                                    <input type="text" name="requester_name" x-model="form.requester_name"
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                        required>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-4 bg-blue-50/50 p-4 rounded-2xl border border-blue-100 text-center">
                                    <div><span
                                            class="text-[10px] uppercase text-blue-400 font-bold tracking-wider">Date</span>
                                        <div class="text-lg font-black text-[#1E3A5F]" x-text="currentDate"></div>
                                        <input type="hidden" name="report_date" x-model="currentDateDB">
                                    </div>
                                    <div><span
                                            class="text-[10px] uppercase text-blue-400 font-bold tracking-wider">Time</span>
                                        <div class="text-lg font-black text-[#1E3A5F]" x-text="currentTime"></div>
                                        <input type="hidden" name="report_time" x-model="currentTime">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Plant</label>
                                        <select name="plant_id" x-model="form.plant_id" @change="filterMachines()"
                                            class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                            required>
                                            <option value="">Select...</option>
                                            @foreach ($plants as $plant)
                                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Category</label>
                                        <select name="category" x-model="form.category"
                                            class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                            required>
                                            <option value="">Select...</option>
                                            <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                            <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                            <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                            <option value="Relokasi Mesin">Relokasi Mesin</option>
                                            <option value="Perbaikan">Perbaikan</option>
                                            <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                            <option value="Rakit Steel Drum">Rakit Steel Drum</option>
                                            <option value="Lain-Lain">Lain-Lain</option>
                                        </select>
                                    </div>
                                </div>

                                <div x-show="form.category != 'Pemasangan Mesin' && needsMachineSelect()" x-transition>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Machine</label>
                                    <div class="relative">
                                        <div x-show="!form.plant_id"
                                            class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center text-xs text-slate-400 italic rounded-xl border border-dashed border-slate-200">
                                            Select Plant first</div>
                                        <select name="machine_id" x-model="form.machine_id"
                                            class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                                            <option value="">Select Machine...</option>
                                            <template x-for="m in filteredMachines" :key="m.id">
                                                <option :value="m.id" x-text="m.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="form.category == 'Pemasangan Mesin'">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">New Machine Name</label>
                                    <input type="text" name="new_machine_name" x-model="form.new_machine_name"
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                    <textarea name="description" x-model="form.description" rows="3"
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                        required></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Target Date</label>
                                    <input type="text" name="target_completion_date"
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                        x-init="flatpickr($el, { minDate: 'today', dateFormat: 'Y-m-d' })" placeholder="Select date...">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Attachment</label>
                                    <input type="file" name="photo"
                                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" />
                                </div>
                            </div>
                            <div
                                class="bg-slate-50 px-8 py-6 border-t border-slate-200 flex justify-end gap-3 rounded-b-[2.5rem]">
                                <button type="button" @click="showCreateModal = false"
                                    class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition">Cancel</button>
                                <button type="submit"
                                    class="px-8 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-900/20 hover:scale-105 active:scale-95 transition transform">Create
                                    Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL EDIT / UPDATE --}}
        <template x-teleport="body">
            <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    @click="showEditModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-visible transform transition-all">
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 rounded-t-[2.5rem] flex justify-between items-center">
                            <h3 class="text-white font-extrabold text-xl">Update Status</h3>
                            <button @click="showEditModal = false"
                                class="text-white/60 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition">&times;</button>
                        </div>
                        <form x-bind:action="'/fh/' + editForm.id + '/update-status'" method="POST"
                            class="p-8 space-y-6">
                            @csrf @method('PUT')

                            {{-- Dropdown Teknisi --}}
                            <div x-data="{ open: false }" class="relative">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Teknisi <span
                                        class="text-xs font-normal text-slate-400">(Max 5)</span></label>
                                <button type="button" @click="open = !open"
                                    class="w-full text-left border border-slate-200 rounded-xl px-4 py-3 text-sm bg-white flex justify-between items-center font-medium text-slate-600 shadow-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition">
                                    <span
                                        x-text="editForm.selectedTechs.length > 0 ? editForm.selectedTechs.length + ' Selected' : '-- Select --'"></span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false"
                                    class="absolute z-10 w-full bg-white border border-slate-100 rounded-xl shadow-xl mt-2 max-h-48 overflow-y-auto p-2"
                                    x-transition>
                                    <template x-for="tech in techniciansData" :key="tech.id">
                                        <div @click="toggleTech(tech.id)"
                                            class="flex items-center gap-3 p-2.5 hover:bg-blue-50 cursor-pointer rounded-lg transition group">
                                            <div class="w-5 h-5 border rounded flex items-center justify-center transition"
                                                :class="editForm.selectedTechs.includes(tech.id) ?
                                                    'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'">
                                                <svg x-show="editForm.selectedTechs.includes(tech.id)"
                                                    class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-slate-600 group-hover:text-blue-700"
                                                x-text="tech.name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2" x-show="editForm.selectedTechs.length > 0">
                                <template x-for="id in editForm.selectedTechs" :key="id">
                                    <span
                                        class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-xs font-bold border border-blue-100 flex items-center gap-2 shadow-sm">
                                        <span x-text="getTechName(id)"></span>
                                        <button type="button" @click="toggleTech(id)"
                                            class="hover:text-red-500 transition">&times;</button>
                                        <input type="hidden" name="facility_tech_ids[]" :value="id">
                                    </span>
                                </template>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
                                <select name="status" x-model="editForm.status"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition font-medium text-slate-700">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            {{-- Dates --}}
                            <div x-show="editForm.status == 'in_progress' || editForm.status == 'completed'"
                                x-transition>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                                <input type="text" name="start_date" x-model="editForm.start_date"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 date-picker-edit focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                    placeholder="YYYY-MM-DD">
                            </div>
                            <div x-show="editForm.status == 'completed'" x-transition>
                                <label class="block text-sm font-bold text-emerald-700 mb-2">Tanggal Selesai
                                    (Actual)</label>
                                <input type="text" name="actual_completion_date"
                                    x-model="editForm.actual_completion_date"
                                    class="w-full rounded-xl border-emerald-200 bg-emerald-50 text-emerald-800 text-sm py-3 px-4 date-picker-edit font-bold focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                                    placeholder="YYYY-MM-DD" :required="editForm.status == 'completed'">
                            </div>

                            <button type="submit"
                                class="w-full py-3.5 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition transform">Save
                                Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL DETAIL (VIEW) --}}
        <template x-teleport="body">
            <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    @click="showDetailModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 flex justify-between items-start">
                            <div>
                                <h3 class="font-black text-2xl text-white tracking-tight"
                                    x-text="ticket ? ticket.ticket_num : ''"></h3>
                                <span
                                    class="px-3 py-1 rounded-lg text-xs font-bold uppercase mt-2 inline-block bg-white/20 text-white backdrop-blur-sm"
                                    x-text="ticket ? ticket.status.replace('_', ' ') : ''"></span>
                            </div>
                            <button @click="showDetailModal = false"
                                class="text-white/60 hover:text-white text-2xl transition bg-white/10 hover:bg-white/20 rounded-full w-10 h-10 flex items-center justify-center">&times;</button>
                        </div>
                        <div class="p-8 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                            <div class="grid grid-cols-2 gap-8 text-sm">
                                <div><span
                                        class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Pemohon</span><span
                                        class="font-bold text-slate-800 text-lg"
                                        x-text="ticket ? ticket.requester_name : ''"></span></div>
                                <div><span
                                        class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Lokasi</span><span
                                        class="font-bold text-slate-800 text-lg"
                                        x-text="ticket ? ticket.plant : ''"></span></div>
                                <div><span
                                        class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Kategori</span><span
                                        class="font-bold text-slate-800 text-lg"
                                        x-text="ticket ? ticket.category : ''"></span></div>
                                <div><span
                                        class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Mesin</span><span
                                        class="font-bold text-slate-800 text-lg"
                                        x-text="ticket && ticket.machine ? ticket.machine.name : '-'"></span></div>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <span
                                    class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-3">Deskripsi
                                    Pekerjaan</span>
                                <div class="text-slate-700 leading-relaxed whitespace-pre-wrap font-medium"
                                    x-text="ticket ? ticket.description : ''"></div>
                            </div>
                            <template x-if="ticket && ticket.photo_path">
                                <div>
                                    <span
                                        class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-3">Foto
                                        Lampiran</span>
                                    <a :href="'/storage/' + ticket.photo_path" target="_blank"
                                        class="block group relative overflow-hidden rounded-2xl max-w-sm">
                                        <img :src="'/storage/' + ticket.photo_path"
                                            class="w-full h-auto object-cover transition duration-500 group-hover:scale-110">
                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                            <span
                                                class="text-white font-bold px-4 py-2 bg-white/20 backdrop-blur-md rounded-lg">View
                                                Full Image</span>
                                        </div>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    {{-- ALPINE DATA DEFINITION --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('facilitiesData', () => ({
                showCreateModal: false,
                showEditModal: false,
                showDetailModal: false,
                ticket: null,
                machinesData: @json($machines),
                techniciansData: @json($technicians),
                pageIds: @json($pageIds),
                form: {
                    requester_name: '',
                    plant_id: '',
                    machine_id: '',
                    new_machine_name: '',
                    category: '',
                    description: '',
                    target_completion_date: '',
                    photo: null
                },
                editForm: {
                    id: '',
                    status: '',
                    start_date: '',
                    actual_completion_date: '',
                    selectedTechs: []
                },
                filteredMachines: [],
                selectedTickets: [],

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    @if (request('open_ticket_id') && isset($openTicket))
                        this.ticket = @json($openTicket);
                        this.showDetailModal = true;
                        const url = new URL(window.location);
                        url.searchParams.delete('open_ticket_id');
                        window.history.replaceState({}, '', url);
                    @endif
                },
                updateTime() {
                    const now = new Date();
                    this.currentDate = now.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    this.currentDateDB = `${year}-${month}-${day}`;
                    this.currentTime = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                },
                filterMachines() {
                    this.form.machine_id = '';
                    this.filteredMachines = this.machinesData.filter(m => m.plant_id == this.form
                        .plant_id);
                },
                needsMachineSelect() {
                    return ['Modifikasi Mesin', 'Pembongkaran Mesin', 'Relokasi Mesin', 'Perbaikan',
                        'Pembuatan Alat Baru'
                    ].includes(this.form.category);
                },
                resetForm() {
                    this.form = {
                        requester_name: '',
                        plant_id: '',
                        machine_id: '',
                        new_machine_name: '',
                        category: '',
                        description: '',
                        target_completion_date: '',
                        photo: null
                    };
                    this.filteredMachines = [];
                },
                openEditModal(wo) {
                    this.ticket = wo;
                    this.editForm.id = wo.id;
                    this.editForm.status = wo.status;
                    this.editForm.start_date = wo.start_date;
                    this.editForm.actual_completion_date = wo.actual_completion_date;
                    this.editForm.selectedTechs = wo.technicians ? wo.technicians.map(t => t.id) : [];
                    this.showEditModal = true;
                    setTimeout(() => {
                        document.querySelectorAll('.date-picker-edit').forEach(el => flatpickr(
                            el, {
                                dateFormat: 'Y-m-d'
                            }));
                    }, 100);
                },
                toggleTech(id) {
                    if (this.editForm.selectedTechs.includes(id)) {
                        this.editForm.selectedTechs = this.editForm.selectedTechs.filter(t => t !== id);
                    } else {
                        if (this.editForm.selectedTechs.length >= 5) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Limit Reached',
                                text: 'Max 5 technicians allowed!',
                                confirmButtonColor: '#1E3A5F'
                            });
                            return;
                        }
                        this.editForm.selectedTechs.push(id);
                    }
                },
                getTechName(id) {
                    let tech = this.techniciansData.find(t => t.id == id);
                    return tech ? tech.name : 'Unknown';
                },
                toggleSelectAll() {
                    this.selectedTickets = (this.selectedTickets.length === this.pageIds.length) ? [] :
                        [...this.pageIds];
                },
                submitExport() {
                    let url = '{{ route('fh.index') }}?export=true';
                    if (this.selectedTickets.length > 0) url += '&selected_ids=' + this.selectedTickets
                        .join(',');
                    window.location.href = url;
                }
            }));
        });
    </script>
</x-app-layout>
