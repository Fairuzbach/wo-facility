@props(['workOrders'])
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
                    <th class="px-6 py-5 text-xs font-extrabold text-slate-500 uppercase tracking-widest text-right">
                        Aksi</th>
                </tr>
            </thead>
            {{-- Body Table --}}
            <tbody class="divide-y divide-slate-100">
                @forelse($workOrders as $wo)
                    {{-- ZEBRA STRIPING: odd:bg-white, even:bg-slate-50 --}}
                    <tr class="group transition-all duration-200 hover:bg-blue-50 odd:bg-white even:bg-slate-100">
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
                                    <div class="font-bold text-[#1E3A5F] text-sm group-hover:text-blue-600 transition">
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
                                <button @click='ticket = @json($wo); showDetailModal = true'
                                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                                @if (Auth::user()?->role == 'fh.admin')
                                    <button @click='openEditModal(@json($wo))'
                                        class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition border border-transparent hover:border-amber-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
