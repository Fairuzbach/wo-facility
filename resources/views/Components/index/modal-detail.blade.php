<template x-teleport="body">
    <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showDetailModal = false">
        </div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">
                <div class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-7 flex justify-between items-start">
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
                                class="font-bold text-slate-800 text-lg" x-text="ticket ? ticket.plant : ''"></span>
                        </div>
                        <div><span
                                class="block text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-1">Kategori</span><span
                                class="font-bold text-slate-800 text-lg" x-text="ticket ? ticket.category : ''"></span>
                        </div>
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
