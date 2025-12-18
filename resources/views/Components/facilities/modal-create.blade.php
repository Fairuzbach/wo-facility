@props(['plants'])
<template x-teleport="body">
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showCreateModal = false">
        </div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">
                <div
                    class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 border-b border-white/10 flex justify-between items-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/5 pattern-dots"></div>
                    <h3 class="font-extrabold text-xl text-white tracking-tight relative z-10">Create New
                        Ticket</h3>
                    <button type="button" @click="showCreateModal = false"
                        class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition relative z-10"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="{{ route('fh.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        {{-- Form Content sama seperti sebelumnya tapi class rounded-xl --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Requester Name</label>
                            <input type="text" name="requester_name" x-model="form.requester_name"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                required>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-4 bg-blue-50/50 p-4 rounded-2xl border border-blue-100 text-center">
                            <div><span class="text-[10px] uppercase text-blue-400 font-bold tracking-wider">Date</span>
                                <div class="text-lg font-black text-[#1E3A5F]" x-text="currentDate"></div>
                                <input type="hidden" name="report_date" x-model="currentDateDB">
                            </div>
                            <div><span class="text-[10px] uppercase text-blue-400 font-bold tracking-wider">Time</span>
                                <div class="text-lg font-black text-[#1E3A5F]" x-text="currentTime"></div>
                                <input type="hidden" name="report_time" x-model="currentTime">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Plant</label>
                                <select name="plant_id" x-model="form.plant_id" @change="filterMachines()"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                    required>
                                    <option value="">Select...</option>
                                    @foreach ($plants as $plant)
                                        <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Category</label>
                                <select name="category" x-model="form.category"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                    required>
                                    <option value="">Select...</option>
                                    <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                    <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                    <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                    <option value="Relokasi Mesin">Relokasi Mesin</option>
                                    <option value="Perbaikan">Perbaikan</option>
                                    <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                    <option value="Rakit Steel Drum">Rakit Steel Drum</option>
                                    <option value="Lain-Lain">Lain-Lain</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="form.category != 'Pemasangan Mesin' && needsMachineSelect()" x-transition>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Machine</label>
                            <div class="relative">
                                <div x-show="!form.plant_id"
                                    class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center text-xs text-slate-400 italic rounded-xl border border-dashed border-slate-200">
                                    Select Plant first</div>
                                <select name="machine_id" x-model="form.machine_id"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                                    <option value="">Select Machine...</option>
                                    <template x-for="m in filteredMachines" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div x-show="form.category == 'Pemasangan Mesin'">
                            <label class="block text-sm font-bold text-slate-700 mb-2">New Machine Name</label>
                            <input type="text" name="new_machine_name" x-model="form.new_machine_name"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                            <textarea name="description" x-model="form.description" rows="3"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                required></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Target Date</label>
                            <input type="text" name="target_completion_date"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition"
                                x-init="flatpickr($el, { minDate: 'today', dateFormat: 'Y-m-d' })" placeholder="Select date...">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Attachment</label>
                            <input type="file" name="photo"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" />
                        </div>
                    </div>
                    <div
                        class="bg-slate-50 px-8 py-6 border-t border-slate-200 flex justify-end gap-3 rounded-b-[2.5rem]">
                        <button type="button" @click="showCreateModal = false"
                            class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition">Cancel</button>
                        <button type="submit"
                            class="px-8 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-900/20 hover:scale-105 active:scale-95 transition transform">Create
                            Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
