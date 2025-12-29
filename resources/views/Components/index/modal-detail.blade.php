<template x-teleport="body">
    <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showDetailModal = false">
        </div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">

                <div
                    class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 flex justify-between items-start shrink-0">
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

                <div class="p-8 space-y-8 overflow-y-auto custom-scrollbar grow">

                    <div class="grid grid-cols-2 gap-8 text-sm">
                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Pemohon</span>
                            <span class="font-bold text-slate-800 text-lg"
                                x-text="ticket ? ticket.requester_name : ''"></span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Lokasi</span>
                            <span class="font-bold text-slate-800 text-lg" x-text="ticket ? ticket.plant : ''"></span>
                        </div>

                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">NIK</span>
                            <span class="font-bold text-slate-800 text-lg"
                                x-text="ticket && ticket.nik_pelapor ? ticket.nik_pelapor : '-'"></span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Divisi</span>
                            <span class="font-bold text-slate-800 text-lg"
                                x-text="ticket && ticket.divisi_pelapor ? ticket.divisi_pelapor : '-'"></span>
                        </div>

                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Kategori</span>
                            <span class="font-bold text-slate-800 text-lg"
                                x-text="ticket ? (ticket.category === 'Pemasangan Mesin' && ticket.new_machine_name ? ticket.category + ' (' + ticket.new_machine_name + ')' : ticket.category) : ''">
                            </span>
                        </div>
                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Mesin</span>
                            <span class="font-bold text-slate-800 text-lg"
                                x-text="ticket ? (ticket.machine ? ticket.machine.name : (ticket.new_machine_name ? ticket.new_machine_name + ' (Baru)' : '-')) : '-'">
                            </span>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <span
                            class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-3">Deskripsi
                            Pekerjaan</span>
                        <div class="text-slate-700 leading-relaxed whitespace-pre-wrap font-medium"
                            x-text="ticket ? ticket.description : ''"></div>
                    </div>

                    <template x-if="ticket && ticket.status === 'completed'">
                        <div class="mt-4 bg-emerald-50 p-6 rounded-2xl border border-emerald-100">
                            <span class="block text-xs font-extrabold text-emerald-600 uppercase tracking-widest mb-3">
                                Laporan Penyelesaian
                            </span>

                            <div class="text-slate-700 leading-relaxed whitespace-pre-wrap font-medium mb-3"
                                x-text="ticket.completion_note ? ticket.completion_note : '(Tidak ada catatan teknisi)'">
                            </div>

                            <template x-if="ticket.tanggal_selesai_indo">
                                <div
                                    class="pt-3 border-t border-emerald-200/50 flex items-center text-xs text-emerald-700 font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Diselesaikan pada:
                                    <span class="ml-1" x-text="ticket.tanggal_selesai_indo"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="ticket && ticket.status === 'cancelled'">
                        <div class="mt-4 bg-red-50 p-6 rounded-2xl border border-red-100">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 text-red-500 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="grow">
                                    <span
                                        class="block text-xs font-extrabold text-red-600 uppercase tracking-widest mb-2">
                                        Alasan Pembatalan
                                    </span>
                                    <div class="text-slate-800 leading-relaxed whitespace-pre-wrap font-medium"
                                        x-text="ticket.completion_note ? ticket.completion_note : 'Permintaan dibatalkan oleh Admin tanpa catatan spesifik.'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="ticket && ticket.photo_path">
                        <div>
                            <span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-3">Foto
                                Lampiran</span>
                            <a :href="'/storage/' + ticket.photo_path" target="_blank"
                                class="block group relative overflow-hidden rounded-2xl max-w-sm border border-slate-200 shadow-sm">
                                <img :src="'/storage/' + ticket.photo_path"
                                    class="w-full h-auto object-cover transition duration-500 group-hover:scale-110">
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <span
                                        class="text-white font-bold px-4 py-2 bg-white/20 backdrop-blur-md rounded-lg shadow-lg border border-white/30">
                                        Lihat Foto Asli
                                    </span>
                                </div>
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
