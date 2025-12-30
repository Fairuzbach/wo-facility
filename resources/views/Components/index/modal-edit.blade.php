@props(['technicians'])

<template x-teleport="body">
    {{-- PANGGIL KOMPONEN JS DI SINI --}}
    <div x-data="facilityEdit" x-show="showEditModal" @open-edit-modal.window="openModal($event.detail)"
        {{-- Oper data teknisi dari PHP ke JS --}} x-init="techniciansData = {{ Js::from($technicians) }}" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false">
        </div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-visible transform transition-all">

                {{-- Header --}}
                <div
                    class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 rounded-t-[2.5rem] flex justify-between items-center">
                    <h3 class="text-white font-extrabold text-xl">Update Status</h3>
                    <button @click="showEditModal = false"
                        class="text-white/60 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition">&times;</button>
                </div>

                <form :action="'/fh/' + editForm.id + '/update-status'" method="POST" class="p-8 space-y-6">
                    @csrf @method('PUT')

                    {{-- Dropdown Teknisi --}}
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Teknisi <span
                                class="text-xs font-normal text-slate-400">(Max 5)</span></label>
                        <button type="button" @click="open = !open"
                            class="w-full text-left border border-slate-200 rounded-xl px-4 py-3 text-sm bg-white flex justify-between items-center font-medium text-slate-600 shadow-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition">
                            <span
                                x-text="editForm.selectedTechs.length > 0 ? editForm.selectedTechs.length + ' Selected' : '-- Select --'"></span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        :class="editForm.selectedTechs.includes(tech.id) ? 'bg-blue-600 border-blue-600' :
                                            'bg-white border-slate-300'">
                                        <svg x-show="editForm.selectedTechs.includes(tech.id)"
                                            class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-blue-700"
                                        x-text="tech.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Status Dropdown --}}
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

                    {{-- Cancellation Note --}}
                    <div x-show="editForm.status === 'cancelled'" x-transition class="mb-4">
                        <label class="font-bold text-sm text-red-600">Alasan Pembatalan <span
                                class="text-red-500">*</span></label>
                        <textarea name="completion_note" x-model="editForm.completion_note" :required="editForm.status === 'cancelled'"
                            class="w-full border p-2 rounded border-red-300 focus:ring-red-500 focus:border-red-500" rows="3"></textarea>
                    </div>

                    {{-- Completion Note --}}
                    <div x-show="editForm.status === 'completed'" x-transition
                        class="mb-4 bg-green-50 p-4 rounded-xl border border-green-100">
                        <label class="block text-sm font-semibold text-green-800 mb-1">Catatan Penyelesaian</label>
                        <textarea name="note" x-model="editForm.note" rows="3"
                            class="w-full rounded-xl border-green-200 focus:border-green-500 text-sm"></textarea>
                    </div>

                    {{-- Dates --}}
                    <div x-show="editForm.status == 'in_progress' || editForm.status == 'completed'" x-transition>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="text" name="start_date" x-model="editForm.start_date"
                            class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 date-picker-edit"
                            placeholder="YYYY-MM-DD">
                    </div>
                    <div x-show="editForm.status == 'completed'" x-transition>
                        <label class="block text-sm font-bold text-emerald-700 mb-2">Tanggal Selesai (Actual)</label>
                        <input type="text" name="actual_completion_date" x-model="editForm.actual_completion_date"
                            class="w-full rounded-xl border-emerald-200 bg-emerald-50 text-emerald-800 text-sm py-3 px-4 date-picker-edit font-bold"
                            placeholder="YYYY-MM-DD">
                    </div>

                    <button type="button" @click="submitUpdateStatus()"
                        class="w-full py-3.5 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition transform">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
