@props(['plants'])

<div class="bg-white rounded-[1.5rem] shadow-lg shadow-slate-200/50 border border-slate-100 p-5">
    <form action="{{ route('fh.index') }}" method="GET"
        class="flex flex-col xl:flex-row gap-4 justify-between items-end xl:items-center">

        <div class="flex flex-col lg:flex-row gap-3 w-full xl:w-auto flex-1">
            <div class="relative w-full lg:w-64 group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tiket..."
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
                <option value="Modifikasi Mesin" {{ request('category') == 'Modifikasi Mesin' ? 'selected' : '' }}>
                    Modifikasi Mesin
                </option>
                <option value="Pemasangan Mesin" {{ request('category') == 'Pemasangan Mesin' ? 'selected' : '' }}>
                    Pemasangan Mesin
                </option>
                <option value="Pembongkaran Mesin" {{ request('category') == 'Pembongkaran Mesin' ? 'selected' : '' }}>
                    Pembongkaran Mesin
                </option>
                <option value="Relokasi Mesin" {{ request('category') == 'Relokasi Mesin' ? 'selected' : '' }}>Relokasi
                    Mesin</option>
                <option value="Perbaikan" {{ request('category') == 'Perbaikan' ? 'selected' : '' }}>
                    Perbaikan</option>
                <option value="Pembuatan Alat Baru"
                    {{ request('category') == 'Pembuatan Alat Baru' ? 'selected' : '' }}>Pembuatan Alat
                    Baru</option>
                <option value="Rakit Steel Drum" {{ request('category') == 'Rakit Steel Drum' ? 'selected' : '' }}>Rakit
                    Steel Drum
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
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                    Cancelled</option>
            </select>

            <select name="plant_id" onchange="this.form.submit()"
                class="w-full lg:w-40 rounded-2xl border border-slate-200 bg-slate-50 text-sm py-3 px-4 text-slate-600 font-medium focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer transition">
                <option value="">Semua Plant</option>
                @foreach ($plants as $plant)
                    <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                        {{ $plant->name }}
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

            {{-- 1. TOMBOL DASHBOARD (Diproteksi @auth agar tidak crash saat Guest) --}}
            @auth
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
            @endauth

            {{-- 2. TOMBOL EXPORT (Menggunakan Alpine x-show="isLoggedIn" agar tersembunyi jika Guest) --}}
            <button type="button" x-show="isLoggedIn" @click="submitExport()" style="display: none;"
                {{-- Mencegah kedip saat loading --}}
                class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 hover:text-green-600 transition flex items-center gap-2 bg-white shadow-sm hover:shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </button>

            {{-- 3. TOMBOL NEW TICKET (Menggunakan openCreateModalCheck untuk validasi) --}}
            <button type="button" @click="openCreateModalCheck()"
                class="px-6 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] hover:from-[#162c46] hover:to-[#1E3A5F] text-white rounded-2xl font-bold text-sm shadow-lg shadow-blue-900/20 transition transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{-- Ubah teks tombol sesuai login status (Opsional) --}}
                <span x-text="isLoggedIn ? 'New Ticket' : 'Login to Create Ticket'">New Ticket</span>
            </button>
        </div>
    </form>
</div>
