@section('browser_title', 'Facilities Work Order')

<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex justify-between items-center py-2">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] flex items-center justify-center text-white shadow-lg shadow-blue-900/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight">WO Facilities</h2>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- LIBRARIES --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- GLOBAL STYLES --}}
    <style>
        /* Enhanced Input Styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="month"],
        input[type="time"],
        textarea,
        select {
            @apply transition-all duration-200 shadow-sm;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="month"]:focus,
        input[type="time"]:focus,
        textarea:focus,
        select:focus {
            @apply shadow-md outline-none;
        }

        /* Button Global Styles */
        button {
            @apply transition-all duration-200 font-medium;
        }

        button:not(.no-scale):hover {
            @apply -translate-y-0.5;
        }

        button:not(.no-scale):active {
            @apply translate-y-0;
        }

        /* Smooth transitions for modals */
        [x-cloak] {
            @apply hidden;
        }

        /* Table row hover effect */
        tbody tr {
            @apply transition-colors duration-150;
        }

        tbody tr:hover {
            @apply bg-gradient-to-r from-blue-50/50 to-transparent;
        }

        /* Smooth scrollbar for modals */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, 0.5);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.6);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.9);
        }

        /* Pulse animation for active filters */
        @keyframes pulse-soft {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-pulse {
            animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Fade in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-slow {
            animation: spin-slow 8s linear infinite;
        }
    </style>

    {{-- MAIN CONTENT --}}
    {{-- UPDATE X-DATA DI BAGIAN ATAS FILE --}}
    <div class="py-8 bg-[#F8FAFC] min-h-screen font-sans" x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        ticket: null,
    
        // Forms
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
        editForm: { id: '', status: '', start_date: '', selectedTechs: [] },
    
        // Data
        machinesData: {{ Js::from($machines) }},
        techniciansData: {{ Js::from($technicians) }},
        filteredMachines: [],
    
        // ... (kode export & time tetap sama) ...
        selectedTickets: [],
        pageIds: {{ Js::from($pageIds) }},
        currentDate: '',
        currentDateDB: '',
        currentTime: '',
        currentShift: '',
    
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
        },
    
        updateTime() {
            // ... (kode waktu tetap sama) ...
            const now = new Date();
            this.currentDate = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            this.currentDateDB = `${year}-${month}-${day}`;
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
            const hour = now.getHours();
            this.currentShift = (hour >= 7 && hour < 15) ? '1 (Pagi)' : ((hour >= 15 && hour < 23) ? '2 (Sore)' : '3 (Malam)');
        },
    
        filterMachines() {
            this.form.machine_id = '';
            this.filteredMachines = this.machinesData.filter(m => m.plant_id == this.form.plant_id);
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
    
        // [FIX] FUNGSI BARU UNTUK CEK APAKAH BUTUH DROPDOWN MESIN
        needsMachineSelect() {
            // Kategori yang WAJIB pilih mesin dari DATABASE (Dropdown)
            const dropdownCategories = [
                'Modifikasi Mesin',
                'Pembongkaran Mesin',
                'Relokasi Mesin',
                'Perbaikan',
                'Pembuatan Alat Baru' // <-- SUDAH MASUK
            ];
            return dropdownCategories.includes(this.form.category);
        },
    
        // ... (Sisa fungsi openEditModal, toggleTech, dll tetap sama) ...
        openEditModal(wo) {
            this.ticket = wo;
            this.editForm.id = wo.id;
            this.editForm.status = wo.status;
            this.editForm.start_date = wo.start_date;
            this.editForm.selectedTechs = wo.technicians ? wo.technicians.map(t => t.id) : [];
            this.showEditModal = true;
            setTimeout(() => { document.querySelectorAll('.date-picker-edit').forEach(el => flatpickr(el, { dateFormat: 'Y-m-d' })); }, 100);
        },
        toggleTech(id) {
            if (this.editForm.selectedTechs.includes(id)) {
                this.editForm.selectedTechs = this.editForm.selectedTechs.filter(t => t !== id);
            } else {
                if (this.editForm.selectedTechs.length >= 5) {
                    Swal.fire({ icon: 'warning', title: 'Limit Reached', text: 'Max 5 technicians allowed!', confirmButtonColor: '#1E3A5F' });
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
            this.selectedTickets = (this.selectedTickets.length === this.pageIds.length) ? [] : [...this.pageIds];
        },
        submitExport() {
            let url = '{{ route('fh.index') }}?export=true';
            if (this.selectedTickets.length > 0) {
                url += '&selected_ids=' + this.selectedTickets.join(',');
            } else {
                const params = new URLSearchParams(window.location.search);
                params.set('export', 'true');
                url = '{{ route('fh.index') }}?' + params.toString();
            }
            window.location.href = url;
        }
    }">

        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1E3A5F',
                    timer: 2000,
                    showConfirmButton: false
                })
            </script>
        @endif

        <div class="max-w-[95rem] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 1. STATS OVERVIEW --}}
            {{-- 1. STATS OVERVIEW --}}
            {{-- 1. STATS OVERVIEW (VIBRANT STYLE) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                {{-- CARD 1: TOTAL (Deep Ocean Blue) --}}
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-xl shadow-blue-500/30 transition-transform hover:-translate-y-1 hover:shadow-2xl">
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
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 p-6 text-white shadow-xl shadow-orange-500/30 transition-transform hover:-translate-y-1 hover:shadow-2xl">
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
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 p-6 text-white shadow-xl shadow-cyan-500/30 transition-transform hover:-translate-y-1 hover:shadow-2xl">
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
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-xl shadow-emerald-500/30 transition-transform hover:-translate-y-1 hover:shadow-2xl">
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
            </div>

            {{-- 2. FILTER & TOOLBAR (DIRAPIKAN) --}}
            <div
                class="bg-white rounded-[1.5rem] shadow-md border border-slate-100 p-6 hover:shadow-lg transition-shadow duration-300">
                <form action="{{ route('fh.index') }}" method="GET"
                    class="flex flex-col xl:flex-row gap-4 items-end xl:items-center justify-between">

                    {{-- Filter Group --}}
                    <div class="flex flex-col lg:flex-row gap-3 w-full xl:w-auto flex-1">
                        {{-- Search --}}
                        <div class="relative w-full lg:w-64 group">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search ticket / requester..."
                                class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm font-medium transition duration-200 shadow-sm group-hover:shadow-md">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        {{-- [BARU] Filter Kategori --}}
                        <select name="category" onchange="this.form.submit()"
                            class="w-full lg:w-48 rounded-xl border border-slate-200 text-sm py-3 px-4 bg-slate-50 font-medium text-slate-600 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer hover:bg-white shadow-sm hover:shadow-md transition-all duration-200 appearance-none bg-no-repeat bg-right pr-10"
                            style="background-image: url('data:image/svg+xml;utf8,<svg class=%22w-4 h-4%22 fill=%22none%22 stroke=%22%2364748b%22 viewBox=%220 0 24 24%22 xmlns=%22http://www.w3.org/2000/svg%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22></path></svg>'); background-position: right 0.75rem center; background-size: 1.25rem;">
                            <option value="">All Category</option>
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
                        </select>

                        {{-- Filter Status --}}
                        <select name="status" onchange="this.form.submit()"
                            class="w-full lg:w-40 rounded-xl border border-slate-200 text-sm py-3 px-4 bg-slate-50 font-medium text-slate-600 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer hover:bg-white shadow-sm hover:shadow-md transition-all duration-200 appearance-none bg-no-repeat bg-right pr-10"
                            style="background-image: url('data:image/svg+xml;utf8,<svg class=%22w-4 h-4%22 fill=%22none%22 stroke=%22%2364748b%22 viewBox=%220 0 24 24%22 xmlns=%22http://www.w3.org/2000/svg%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22></path></svg>'); background-position: right 0.75rem center; background-size: 1.25rem;">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>

                        {{-- Filter Plant --}}
                        <select name="plant_id" onchange="this.form.submit()"
                            class="w-full lg:w-40 rounded-xl border border-slate-200 text-sm py-3 px-4 bg-slate-50 font-medium text-slate-600 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer hover:bg-white shadow-sm hover:shadow-md transition-all duration-200 appearance-none bg-no-repeat bg-right pr-10"
                            style="background-image: url('data:image/svg+xml;utf8,<svg class=%22w-4 h-4%22 fill=%22none%22 stroke=%22%2364748b%22 viewBox=%220 0 24 24%22 xmlns=%22http://www.w3.org/2000/svg%22><path stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22></path></svg>'); background-position: right 0.75rem center; background-size: 1.25rem;">
                            <option value="">All Plants</option>
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}"
                                    {{ request('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- [BARU] Tombol Reset Filter --}}
                        <a href="{{ route('fh.index') }}"
                            class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 flex items-center justify-center gap-2 transition duration-200 shadow-sm hover:shadow-md bg-white"
                            title="Reset All Filters">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Reset
                        </a>
                    </div>

                    {{-- Actions --}}
                    {{-- Action Buttons --}}
                    <div class="flex gap-3 w-full lg:w-auto">

                        {{-- [BARU] Tombol Dashboard (Hanya Admin) --}}
                        @if (in_array(Auth::user()->role, ['fh.admin', 'super.admin']))
                            <a href="{{ route('fh.dashboard') }}"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 flex items-center gap-2 transition group"
                                title="Open Analytics Dashboard">
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                                Dashboard
                            </a>
                        @endif

                        {{-- Tombol Export (Existing) --}}
                        <button type="button" @click="submitExport()"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 flex items-center gap-2 transition hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export <span x-show="selectedTickets.length > 0"
                                class="text-xs bg-slate-200 px-1.5 rounded-full ml-1"
                                x-text="selectedTickets.length"></span>
                        </button>

                        {{-- Tombol New Ticket (Existing) --}}
                        <button type="button" @click="resetForm(); showCreateModal = true"
                            class="px-6 py-2.5 bg-[#1E3A5F] hover:bg-[#162c46] text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-900/20 transition transform active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Ticket
                        </button>
                    </div>
                </form>
            </div>

            {{-- 3. TABLE --}}
            <div
                class="bg-white rounded-[1.5rem] shadow-md border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-extrabold">
                                <th class="px-6 py-5 w-10 text-center"><input type="checkbox"
                                        @change="toggleSelectAll()"
                                        :checked="selectedTickets.length === pageIds.length && pageIds.length > 0"
                                        class="rounded border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] w-4 h-4 cursor-pointer">
                                </th>
                                <th class="px-6 py-5">Tiket</th>
                                <th class="px-6 py-5">Pemohon</th>
                                <th class="px-6 py-5">Lokasi</th>
                                <th class="px-6 py-5">Kategori Pekerjaan</th>
                                <th class="px-6 py-5">Status & PIC</th>
                                <th class="px-6 py-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($workOrders as $wo)
                                {{-- PERUBAHAN DISINI: 
                                     odd:bg-white = Baris ganjil putih bersih
                                     even:bg-slate-100 = Baris genap abu-abu muda (kontras jelas)
                                     hover:bg-blue-50 = Efek hover biru muda --}}
                                <tr
                                    class="transition duration-150 group odd:bg-white even:bg-slate-100 hover:bg-blue-50">

                                    <td class="px-4 py-4 text-center align-middle">
                                        <input type="checkbox" value="{{ $wo->id }}" x-model="selectedTickets"
                                            class="rounded border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] w-4 h-4 cursor-pointer shadow-sm">
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-9 w-9 rounded-lg bg-[#1E3A5F] text-white flex items-center justify-center font-bold text-[10px] shadow-sm">
                                                WO
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition-colors">
                                                    {{ $wo->ticket_num }}</div>
                                                <div class="text-[11px] text-slate-500 font-medium">
                                                    {{ $wo->report_date ? \Carbon\Carbon::parse($wo->report_date)->translatedFormat('d M Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="font-bold text-slate-700 text-sm">{{ $wo->requester_name }}</div>
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <div class="font-bold text-slate-700 text-sm">{{ $wo->plant }}</div>
                                        @if ($wo->machine)
                                            <div
                                                class="mt-1 inline-flex px-2 py-0.5 rounded border border-purple-200 bg-purple-50 text-[10px] font-bold text-purple-700">
                                                {{ $wo->machine->name }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-md bg-white border border-slate-200 text-[11px] font-bold text-slate-600 shadow-sm">
                                            {{ $wo->category }}
                                        </span>
                                        <div class="text-[11px] text-slate-500 mt-1.5 max-w-[200px] truncate"
                                            title="{{ $wo->description }}">
                                            {{ $wo->description }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        @php
                                            $st = $wo->status;
                                            // Warna badge status dipertegas
                                            $cls = match ($st) {
                                                'completed'
                                                    => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                                'in_progress' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                                'pending' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                                'cancelled' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                                default => 'bg-slate-100 text-slate-800 border border-slate-200',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full {{ $cls }} text-[10px] font-bold uppercase tracking-wide">
                                            {{ str_replace('_', ' ', $st) }}
                                        </span>

                                        @if ($wo->technicians->count() > 0)
                                            <div class="mt-2 flex -space-x-2 overflow-hidden pl-1">
                                                @foreach ($wo->technicians->take(3) as $tech)
                                                    <div class="inline-flex h-6 w-6 rounded-full ring-2 ring-white bg-[#2d5285] items-center justify-center text-[8px] font-bold text-white shadow-sm"
                                                        title="{{ $tech->name }}">
                                                        {{ substr($tech->name, 0, 1) }}
                                                    </div>
                                                @endforeach
                                                @if ($wo->technicians->count() > 3)
                                                    <div
                                                        class="inline-flex h-6 w-6 rounded-full ring-2 ring-white bg-slate-200 items-center justify-center text-[8px] font-bold text-slate-600">
                                                        +{{ $wo->technicians->count() - 3 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-middle text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                @click='ticket = @json($wo); showDetailModal = true'
                                                class="p-2 rounded-lg text-slate-500 hover:bg-white hover:text-blue-600 hover:shadow-md border border-transparent hover:border-slate-100 transition-all duration-200">
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
                                                    class="p-2 rounded-lg text-slate-500 hover:bg-white hover:text-amber-600 hover:shadow-md border border-transparent hover:border-slate-100 transition-all duration-200">
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
                                            <svg class="w-12 h-12 mb-3 opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                            <span class="italic">Belum ada tiket yang tersedia.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">{{ $workOrders->links() }}</div>
            </div>
        </div>

        {{-- MODAL CREATE: (Clean & Simple) --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                    @click="showCreateModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 border-b border-slate-200/50 flex justify-between items-center sticky top-0 z-10">
                            <h3 class="text-white font-extrabold text-xl tracking-tight">Create New Ticket</h3>
                            <button @click="showCreateModal = false"
                                class="text-white/60 hover:text-white hover:bg-white/10 rounded-full p-2.5 transition duration-200"><svg
                                    class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg></button>
                        </div>
                        <form action="{{ route('fh.store') }}" method="POST" enctype="multipart/form-data"
                            class="max-h-[75vh] overflow-y-auto custom-scrollbar">
                            @csrf
                            <div class="p-8 space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Requester Name <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" name="requester_name" x-model="form.requester_name"
                                        required
                                        class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition py-3 bg-slate-50/50 font-medium text-slate-600">
                                </div>
                                {{-- UPDATE: Grid diubah jadi 2 kolom (Date & Time saja) --}}
                                <div
                                    class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100 grid grid-cols-2 gap-4 text-center">
                                    {{-- DATE --}}
                                    <div>
                                        <div class="text-[10px] font-bold text-blue-300 uppercase">Date</div>
                                        <div class="font-bold text-[#1E3A5F] text-sm" x-text="currentDate"></div>
                                        <input type="hidden" name="report_date" x-model="currentDateDB">
                                    </div>

                                    {{-- TIME --}}
                                    <div>
                                        <div class="text-[10px] font-bold text-blue-300 uppercase">Time</div>
                                        <div class="font-bold text-[#1E3A5F] text-sm" x-text="currentTime"></div>
                                        <input type="hidden" name="report_time" x-model="currentTime">
                                    </div>

                                    {{-- BAGIAN SHIFT SUDAH DIHAPUS --}}
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Location / Plant
                                            <span class="text-rose-500">*</span></label>
                                        <select name="plant_id" x-model="form.plant_id" @change="filterMachines()"
                                            required
                                            class="w-full rounded-xl border-slate-200 text-sm py-3 bg-slate-50/50">
                                            <option value="">Select Plant...</option>
                                            @foreach ($plants as $plant)
                                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Category <span
                                                class="text-rose-500">*</span></label>
                                        <select name="category" x-model="form.category" required
                                            class="w-full rounded-xl border-slate-200 text-sm py-3 bg-slate-50/50">
                                            <option value="">Select Category...</option>
                                            <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                            <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                            <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                            <option value="Relokasi Mesin">Relokasi Mesin</option>
                                            <option value="Perbaikan">Perbaikan</option>
                                            <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                            <option value="Rakit Steel Drum">Rakit Steel Drum</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- LOGIKA KONDISIONAL MESIN --}}
                                {{-- 1. Jika Pemasangan Mesin -> Input Text Mesin Baru --}}
                                <div x-show="form.category == 'Pemasangan Mesin'" x-transition>
                                    <label class="block text-sm font-bold text-blue-700 mb-2">Nama Mesin Baru <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" name="new_machine_name" x-model="form.new_machine_name"
                                        :required="form.category == 'Pemasangan Mesin'"
                                        placeholder="Masukkan nama mesin baru..."
                                        class="w-full rounded-xl border-blue-200 bg-blue-50/30 text-sm py-3 text-slate-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                    <p class="text-xs text-slate-400 mt-1 italic">Mesin ini akan didaftarkan di Plant
                                        yang dipilih.</p>
                                </div>

                                {{-- 2. Jika Kategori Lain -> Dropdown Pilih Mesin --}}
                                <div x-show="form.category != 'Pemasangan Mesin' && needsMachineSelect()" x-transition>
                                    <label class="block text-sm font-bold text-blue-700 mb-2">Pilih Mesin <span
                                            class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <div x-show="!form.plant_id"
                                            class="absolute inset-0 bg-white/80 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-xl border border-dashed border-slate-300">
                                            <span class="text-xs text-slate-400 font-medium italic">Pilih Plant
                                                Terlebih Dahulu</span>
                                        </div>
                                        <select name="machine_id" x-model="form.machine_id"
                                            :required="form.category != 'Pemasangan Mesin' && needsMachineSelect()"
                                            class="w-full rounded-xl border-blue-200 bg-blue-50/30 text-sm py-3">
                                            <option value="">-- Pilih Mesin --</option>
                                            <template x-for="machine in filteredMachines" :key="machine.id">
                                                <option :value="machine.id" x-text="machine.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>


                                <div><label class="block text-sm font-bold text-slate-700 mb-2">Request Target
                                        Date</label><input type="text" name="target_completion_date"
                                        placeholder="Select date..."
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 bg-slate-50/50"
                                        x-init="flatpickr($el, { minDate: 'today', dateFormat: 'Y-m-d' })"></div>
                                <div><label class="block text-sm font-bold text-slate-700 mb-2">Description <span
                                            class="text-rose-500">*</span></label>
                                    <textarea name="description" rows="3" required
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 bg-slate-50/50"></textarea>
                                </div>
                                <div><label
                                        class="block text-sm font-bold text-slate-700 mb-2">Attachment</label><input
                                        name="photo" type="file"
                                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                </div>
                            </div>
                            <div
                                class="bg-gradient-to-r from-slate-50 to-slate-100 px-8 py-6 flex justify-end gap-3 border-t border-slate-200">
                                <button type="button" @click="showCreateModal = false"
                                    class="px-6 py-3 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-200 border border-slate-200 transition duration-200 hover:text-slate-800">Cancel</button><button
                                    type="submit"
                                    class="px-8 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#152a47] text-white rounded-xl text-sm font-bold shadow-lg hover:shadow-xl hover:from-[#162c46] hover:to-[#0f1f33] transition-all duration-300 hover:scale-105 active:scale-95">Create
                                    Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL 2: UPDATE STATUS (MULTI SELECT DROPDOWN FIX) --}}
        <template x-teleport="body">
            <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                    @click="showEditModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-visible transform transition-all">

                        {{-- Header --}}
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 flex justify-between items-center border-b border-slate-200/50">
                            <h3 class="text-white font-bold text-lg tracking-tight">Update Ticket Status</h3>
                            <button @click="showEditModal = false"
                                class="text-white/60 hover:text-white hover:bg-white/10 rounded-full p-2.5 transition duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form x-bind:action="'/fh/' + editForm.id + '/update-status'" method="POST"
                            class="p-8 space-y-6">
                            @csrf
                            @method('PUT')

                            {{-- 1. DROPDOWN TEKNISI (MULTI SELECT) --}}
                            <div x-data="{ openDropdown: false }">
                                <label class="block text-sm font-bold text-slate-700 mb-2">
                                    Pilih Teknisi <span class="text-xs font-normal text-slate-400">(Max 5)</span>
                                </label>

                                <div class="relative">
                                    {{-- TRIGGER BUTTON (Tampilan seperti Select Box) --}}
                                    <button type="button" @click="openDropdown = !openDropdown"
                                        class="w-full bg-white border border-slate-200 text-left rounded-xl px-4 py-3 text-sm font-medium text-slate-600 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 flex justify-between items-center">
                                        <span
                                            x-text="editForm.selectedTechs.length > 0 ? editForm.selectedTechs.length + ' Technician(s) Selected' : '-- Select Technicians --'"></span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform"
                                            :class="openDropdown ? 'rotate-180' : ''" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    {{-- DROPDOWN LIST (Muncul saat diklik) --}}
                                    <div x-show="openDropdown" @click.away="openDropdown = false"
                                        class="absolute z-50 mt-2 w-full bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar p-2"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100" style="display: none;">

                                        <template x-for="tech in techniciansData" :key="tech.id">
                                            <div @click="toggleTech(tech.id)"
                                                class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition hover:bg-blue-50 group">

                                                <div class="flex items-center gap-3">
                                                    {{-- Checkbox Icon Visual --}}
                                                    <div class="w-5 h-5 rounded border flex items-center justify-center transition"
                                                        :class="editForm.selectedTechs.includes(tech.id) ?
                                                            'bg-blue-500 border-blue-500 text-white' :
                                                            'border-slate-300 bg-white'">
                                                        <svg x-show="editForm.selectedTechs.includes(tech.id)"
                                                            class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    <span
                                                        class="text-sm text-slate-600 font-medium group-hover:text-blue-700"
                                                        x-text="tech.name"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="techniciansData.length === 0"
                                            class="px-4 py-3 text-sm text-slate-400 text-center">
                                            No technicians available.
                                        </div>
                                    </div>
                                </div>

                                {{-- TAGS HASIL PILIHAN (Muncul di bawah dropdown) --}}
                                <div class="flex flex-wrap gap-2 mt-3" x-show="editForm.selectedTechs.length > 0">
                                    <template x-for="id in editForm.selectedTechs" :key="id">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100 shadow-sm animate-fadeIn">
                                            <span x-text="getTechName(id)"></span>
                                            <button type="button" @click="toggleTech(id)"
                                                class="hover:text-red-500 hover:bg-blue-100 rounded-full p-0.5 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                            {{-- INPUT HIDDEN AGAR TERKIRIM KE CONTROLLER --}}
                                            <input type="hidden" name="facility_tech_ids[]" :value="id">
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- 2. STATUS --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
                                <div class="relative">
                                    <select name="status" x-model="editForm.status"
                                        class="w-full appearance-none rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition cursor-pointer">
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. START DATE --}}
                            <div x-show="editForm.status == 'in_progress' || editForm.status == 'completed'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                                <div class="relative">
                                    <input type="text" name="start_date" x-model="editForm.start_date"
                                        placeholder="YYYY-MM-DD"
                                        class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 date-picker-edit focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full py-3.5 bg-gradient-to-br from-[#1E3A5F] to-[#152a47] text-white font-bold rounded-xl hover:from-[#162c46] hover:to-[#0f1f33] shadow-lg hover:shadow-xl transition transform active:scale-[0.98]">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
        {{-- MODAL 3: DETAIL TICKET (VIEW) --}}
        <template x-teleport="body">
            <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                    @click="showDetailModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">

                        {{-- Header --}}
                        <div
                            class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 flex justify-between items-start border-b border-slate-200/50">
                            <div>
                                <h3 class="text-white font-extrabold text-2xl tracking-tight flex items-center gap-3">
                                    <span x-text="ticket ? ticket.ticket_num : ''"></span>
                                    <span
                                        class="px-3 py-1 rounded-lg bg-white/20 text-white text-xs font-bold uppercase tracking-wider backdrop-blur-sm"
                                        x-text="ticket ? ticket.status.replace('_', ' ') : ''"></span>
                                </h3>
                                <p class="text-blue-100 text-sm mt-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    Reported on <span class="font-bold text-white"
                                        x-text="ticket ? new Date(ticket.report_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : ''"></span>
                                </p>
                            </div>
                            <button @click="showDetailModal = false"
                                class="text-white/60 hover:text-white transition hover:bg-white/10 rounded-full p-2.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="p-8 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">

                            {{-- 1. INFORMASI UTAMA (Grid 2 Kolom) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {{-- Kiri: Detail Request --}}
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Pemohon</label>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-lg">
                                                <span x-text="ticket ? ticket.requester_name.charAt(0) : ''"></span>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-700 text-base"
                                                    x-text="ticket ? ticket.requester_name : '-'"></div>
                                                <div class="text-xs text-slate-500">ID Pemohon: <span
                                                        x-text="ticket ? ticket.requester_id : '-'"></span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Lokasi
                                            dan Mesin</label>
                                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-[#1E3A5F] mt-0.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                                <div>
                                                    <div class="font-bold text-slate-700 text-sm">Plant</div>
                                                    <div class="text-sm text-slate-600"
                                                        x-text="ticket ? ticket.plant : '-'"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 border-t border-slate-200 pt-3">
                                                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                                    </path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <div>
                                                    <div class="font-bold text-slate-700 text-sm">Mesin</div>
                                                    <div class="text-sm text-slate-600">
                                                        <span
                                                            x-text="ticket && ticket.machine ? ticket.machine.name : '-'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kanan: Category & PIC --}}
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Kategori
                                            Pekerjaan</label>
                                        <span
                                            class="inline-block px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-bold text-sm border border-blue-100"
                                            x-text="ticket ? ticket.category : '-'"></span>
                                    </div>

                                    <div>
                                        <label
                                            class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Teknisi
                                            (PIC)</label>

                                        {{-- LOGIKA MULTI PIC --}}
                                        <div class="flex flex-wrap gap-2">
                                            <template
                                                x-if="ticket && ticket.technicians && ticket.technicians.length > 0">
                                                <template x-for="tech in ticket.technicians" :key="tech.id">
                                                    <div
                                                        class="flex items-center gap-2 bg-white border border-slate-200 rounded-full pl-1 pr-3 py-1 shadow-sm">
                                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-white flex items-center justify-center text-xs font-bold"
                                                            x-text="tech.name.charAt(0)"></div>
                                                        <span class="text-xs font-bold text-slate-700"
                                                            x-text="tech.name"></span>
                                                    </div>
                                                </template>
                                            </template>

                                            {{-- Fallback jika kosong --}}
                                            <div x-show="!ticket || !ticket.technicians || ticket.technicians.length === 0"
                                                class="flex items-center gap-2 text-slate-400 italic text-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                    </path>
                                                </svg>
                                                Unassigned
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100">

                            {{-- 2. DESKRIPSI --}}
                            <div>
                                <label
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Deskripsi
                                    Pekerjaan</label>
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 text-slate-700 text-sm leading-relaxed whitespace-pre-line"
                                    x-text="ticket ? ticket.description : '-'"></div>
                            </div>

                            {{-- 3. FOTO BUKTI (Jika Ada) --}}
                            <template x-if="ticket && ticket.photo_path">
                                <div>
                                    <label
                                        class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Attachment</label>
                                    <div
                                        class="relative group rounded-2xl overflow-hidden border border-slate-200 w-full max-w-sm">
                                        <img :src="'/storage/' + ticket.photo_path" alt="Proof"
                                            class="w-full h-auto object-cover">
                                        <div
                                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                            <a :href="'/storage/' + ticket.photo_path" target="_blank"
                                                class="px-4 py-2 bg-white rounded-lg text-sm font-bold text-slate-800 hover:bg-slate-100">View
                                                Full Size</a>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <hr class="border-slate-100">

                            {{-- 4. TIMELINE --}}
                            <div>
                                <label
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-4">Timeline
                                    Progress</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Created --}}
                                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                        <div class="text-[10px] text-slate-400 uppercase font-bold mb-1">Dibuat pada
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                            <span class="text-sm font-bold text-slate-700"
                                                x-text="ticket ? new Date(ticket.created_at).toLocaleDateString('id-ID') : '-'"></span>
                                        </div>
                                    </div>
                                    {{-- Started --}}
                                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm"
                                        :class="ticket && ticket.start_date ? 'border-blue-200 bg-blue-50/30' : ''">
                                        <div class="text-[10px] text-slate-400 uppercase font-bold mb-1">Tanggal Mulai
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full"
                                                :class="ticket && ticket.start_date ? 'bg-blue-500' : 'bg-slate-200'">
                                            </div>
                                            <span class="text-sm font-bold"
                                                :class="ticket && ticket.start_date ? 'text-blue-700' : 'text-slate-400'"
                                                x-text="ticket && ticket.start_date ? ticket.start_date : '-'"></span>
                                        </div>
                                    </div>
                                    {{-- Completed --}}
                                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm"
                                        :class="ticket && ticket.actual_completion_date ?
                                            'border-emerald-200 bg-emerald-50/30' : ''">
                                        <div class="text-[10px] text-slate-400 uppercase font-bold mb-1">Tanggal
                                            Selesai</div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full"
                                                :class="ticket && ticket.actual_completion_date ? 'bg-emerald-500' :
                                                    'bg-slate-200'">
                                            </div>
                                            <span class="text-sm font-bold"
                                                :class="ticket && ticket.actual_completion_date ? 'text-emerald-700' :
                                                    'text-slate-400'"
                                                x-text="ticket && ticket.actual_completion_date ? ticket.actual_completion_date : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="bg-slate-50 px-8 py-5 border-t border-slate-100 flex justify-end">
                            <button @click="showDetailModal = false"
                                class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">Close
                                Detail</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</x-app-layout>
