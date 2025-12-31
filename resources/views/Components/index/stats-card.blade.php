@props(['countTotal', 'countWaitingSpv', 'countPending', 'countProgress', 'countDone'])

{{-- 
    GRID CONTAINER
    Menggunakan logika dinamis:
    - Mobile: 1 Kolom (grid-cols-1) agar kartu terlihat detail/besar
    - Tablet: 2/3 Kolom
    - Desktop: 5 Kolom (jika Login) atau 4 Kolom (jika Guest)
--}}
<div
    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 {{ auth()->check() ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4">

    {{-- 1. Total Tiket (Biru/Indigo Gelap) --}}
    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 p-5 text-white shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div
            class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/5 blur-xl group-hover:scale-110 transition-transform duration-500">
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mb-1">Total Tiket</p>
            <div class="flex items-center justify-between">
                <h3 class="text-3xl font-black">{{ $countTotal }}</h3>
                <div class="p-2 bg-white/10 rounded-lg backdrop-blur-md">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Waiting Approval (Rose/Pink) - HANYA MUNCUL JIKA LOGIN --}}
    @auth
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 p-5 text-white shadow-lg shadow-rose-500/20 hover:-translate-y-1 transition-all duration-300 group">
            <div
                class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/10 blur-xl group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-bold text-rose-100 uppercase tracking-widest mb-1">Waiting Approval</p>
                <div class="flex items-center justify-between">
                    <h3 class="text-3xl font-black">{{ $countWaitingSpv }}</h3>
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    {{-- 3. Pending (Orange) --}}
    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 p-5 text-white shadow-lg shadow-orange-500/20 hover:-translate-y-1 transition-all duration-300 group">
        <div
            class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/10 blur-xl group-hover:scale-110 transition-transform duration-500">
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-orange-100 uppercase tracking-widest mb-1">Pending</p>
            <div class="flex items-center justify-between">
                <h3 class="text-3xl font-black">{{ $countPending }}</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. In Progress (Blue/Cyan) --}}
    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 p-5 text-white shadow-lg shadow-cyan-500/20 hover:-translate-y-1 transition-all duration-300 group">
        <div
            class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/10 blur-xl group-hover:scale-110 transition-transform duration-500">
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-cyan-100 uppercase tracking-widest mb-1">In Progress</p>
            <div class="flex items-center justify-between">
                <h3 class="text-3xl font-black">{{ $countProgress }}</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Completed (Emerald) --}}
    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-1 transition-all duration-300 group">
        <div
            class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/10 blur-xl group-hover:scale-110 transition-transform duration-500">
        </div>
        <div class="relative z-10 w-full">
            <div class="flex justify-between items-start mb-1">
                <p class="text-[10px] font-bold text-emerald-100 uppercase tracking-widest">Completed</p>
                <span class="text-xs font-bold bg-white/20 px-1.5 py-0.5 rounded shadow-sm">
                    {{ $countTotal > 0 ? round(($countDone / $countTotal) * 100) : 0 }}%
                </span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-3xl font-black">{{ $countDone }}</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            {{-- Progress Bar --}}
            <div class="w-full bg-black/20 h-1 rounded-full overflow-hidden">
                <div class="bg-white h-full rounded-full transition-all duration-1000"
                    style="width: {{ $countTotal > 0 ? ($countDone / $countTotal) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>

</div>
