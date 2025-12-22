{{-- Stats Card --}}
@props(['countTotal', 'countPending', 'countProgress', 'countDone', 'selectedMonth', 'completionPct'])
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
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
