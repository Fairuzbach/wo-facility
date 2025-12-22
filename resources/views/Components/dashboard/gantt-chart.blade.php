@props(['groupedGantt', 'ganttStartDate', 'ganttTotalDays'])
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
            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai
            </div>
            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Proses
            </div>
            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Pending
            </div>
            <div class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-rose-200 shadow-sm">
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
                <div class="w-64 flex-shrink-0 p-3 border-r border-slate-200 bg-slate-50 sticky left-0 z-30 shadow-sm">
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
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
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
                        x-transition:leave-start="opacity-100 max-h-[500px]" x-transition:leave-end="opacity-0 max-h-0"
                        class="bg-slate-50/50 shadow-inner overflow-hidden">

                        @foreach ($group['items'] as $item)
                            <div
                                class="flex items-center h-10 border-b border-slate-100/50 hover:bg-white transition-colors">

                                {{-- Label Item --}}
                                <div
                                    class="w-64 flex-shrink-0 px-4 pl-12 border-r border-slate-200 flex flex-col justify-center sticky left-0 bg-slate-50/50 z-10">
                                    <div class="text-[11px] font-bold text-slate-700 truncate font-mono">
                                        {{ $item['ticket'] }}</div>
                                    <div class="text-[9px] text-slate-400 truncate" title="{{ $item['pic'] }}">
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
