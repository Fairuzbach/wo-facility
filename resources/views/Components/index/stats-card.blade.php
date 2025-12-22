@props(['countTotal', 'countPending', 'countProgress', 'countDone'])
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
                    <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
