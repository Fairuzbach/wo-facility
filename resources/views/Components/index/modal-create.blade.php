@props(['machines', 'plants'])

<template x-teleport="body">
    <div x-data="facilityCreate" x-show="showCreateModal" @open-create-modal.window="openCreateModalCheck()"
        x-init="plantsData = {{ Js::from($plants) }};
        machinesData = {{ Js::from($machines) }};" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showCreateModal = false">
        </div>

        {{-- Wrapper --}}
        <div class="flex min-h-full items-center justify-center p-4" @click.self="showCreateModal = false">

            <div
                class="relative w-full max-w-4xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div
                    class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 flex justify-between items-center shrink-0">
                    <h3 class="text-white font-extrabold text-2xl tracking-tight">Buat Tiket Baru</h3>

                    {{-- Tombol Close --}}
                    <button type="button" @click="showCreateModal = false"
                        class="text-white/60 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition cursor-pointer z-10">
                        <span class="text-2xl leading-none">&times;</span>
                    </button>
                </div>

                {{-- Body Form --}}
                <div class="p-8 space-y-6 overflow-y-auto custom-scrollbar grow">

                    {{-- 1. INFORMASI PELAPOR --}}
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-extrabold text-[#1E3A5F] uppercase tracking-widest">Informasi
                                Pelapor</h4>

                            <button type="button" @click="addEmployee()"
                                class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-200 transition flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Pelapor
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Input NIK --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">NIK <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" id="input_nik" x-model="currentNikInput"
                                        @keydown.enter.prevent = "addEmployee()" title="Tekan enter untuk menambahkan"
                                        class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-slate-700"
                                        placeholder="Contoh: 12345">

                                    {{-- Loading Spinner --}}
                                    <div x-show="isCheckingNik" class="absolute right-3 top-3">
                                        <svg class="animate-spin h-5 w-5 text-blue-500"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Input Nama (Readonly) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama <span
                                        class="text-slate-400 font-normal">(Otomatis)</span></label>
                                <input type="text" x-model="currentNameInput" readonly
                                    class="w-full rounded-xl border-slate-200 bg-slate-100 font-bold text-slate-500 cursor-not-allowed"
                                    placeholder="Nama Pelapor">
                            </div>

                            {{-- Input Divisi (Readonly) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Divisi <span
                                        class="text-slate-400 font-normal">(Otomatis)</span></label>
                                <input type="text" x-model="currentDivInput" readonly
                                    class="w-full rounded-xl border-slate-200 bg-slate-100 font-bold text-slate-500 cursor-not-allowed"
                                    placeholder="Divisi">
                            </div>
                        </div>

                        {{-- List Pelapor --}}
                        <template x-if="addedEmployees.length > 0">
                            <div class="mt-4 flex flex-wrap gap-2">
                                <template x-for="(emp, index) in addedEmployees" :key="index">
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 shadow-sm">
                                        <span x-text="emp.name + ' (' + emp.nik + ')'"></span>
                                        <button type="button" @click="removeEmployee(index)"
                                            class="text-red-400 hover:text-red-600 font-bold text-lg leading-none">&times;</button>
                                    </span>
                                </template>
                            </div>
                        </template>

                        {{-- Email --}}
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email Notifikasi <span
                                    class="text-slate-300 font-normal">(Opsional)</span></label>
                            <input type="email" x-model="form.requester_email"
                                class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Masukkan email jika ingin menerima notifikasi progress">
                        </div>
                    </div>

                    {{-- 2. DETAIL PEKERJAAN --}}
                    <div>
                        <h4 class="text-sm font-extrabold text-[#1E3A5F] uppercase tracking-widest mb-4">Detail
                            Pekerjaan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                            {{-- Plant (FIXED: Hapus @change="handleMainPlantChange") --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Lokasi (Plant)
                                    <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select x-model="form.plant_id"
                                        class="w-full rounded-xl border-slate-200 py-3 px-4 bg-white font-bold text-slate-700 appearance-none focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Pilih Lokasi --</option>
                                        <template x-for="plant in plantsList" :key="plant.id">
                                            <option :value="plant.id" x-text="plant.name"></option>
                                        </template>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Kategori --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kategori <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select x-model="form.category"
                                        class="w-full rounded-xl border-slate-200 py-3 px-4 bg-white font-bold text-slate-700 appearance-none focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Perbaikan Mesin">Perbaikan Mesin</option>
                                        <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                        <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                        <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                        <option value="Relokasi Mesin">Relokasi Mesin</option>
                                        <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                        <option value="Rakit Steel Drum">Rakit Steel Drum</option>
                                        <option value="Lain-Lain">Lain-Lain
                                        </option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section Mesin (Wrapper) --}}
                        {{-- Muncul untuk semua kategori yang butuh data mesin (baik baru maupun lama) --}}
                        <div x-show="showMachineSection" x-transition
                            class="mb-6 bg-blue-50/50 p-6 rounded-2xl border border-blue-100">

                            {{-- A. JIKA KATEGORI = "PEMASANGAN MESIN" (INPUT MANUAL) --}}
                            {{-- Gunakan x-if untuk memastikan blok ini eksklusif --}}
                            <template x-if="isNewMachineInstallation">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded border border-green-200">MODE
                                            PEMASANGAN BARU</span>
                                    </div>

                                    {{-- Input Nama Mesin Baru --}}
                                    <div>
                                        <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Nama Mesin
                                            Baru <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="new_machine_name"
                                            class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-slate-700"
                                            placeholder="Contoh: CNC Lathe X1 (New)">
                                    </div>
                                </div>
                            </template>

                            {{-- B. JIKA KATEGORI = PERBAIKAN / MODIFIKASI (PILIH MESIN LAMA) --}}
                            <template x-if="!isNewMachineInstallation">
                                <div class="space-y-4">

                                    {{-- [LOGIKA KHUSUS MT/PE] --}}
                                    {{-- Dropdown ini HANYA muncul jika User memilih Kategori 'Pilih Mesin' DAN Lokasinya MT/PE --}}
                                    <template x-if="isSpecialDepartment">
                                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 mb-2">
                                            <label class="block text-xs font-bold text-amber-700 uppercase mb-2">
                                                Pilih Plant Asal Mesin <span class="text-red-500">*</span>
                                            </label>
                                            <select x-model="machine_origin_plant_id"
                                                class="w-full rounded-xl border-amber-200 py-3 px-4 bg-white font-bold text-slate-700 focus:border-amber-500 focus:ring-amber-500">
                                                <option value="">-- Pilih Plant Pemilik Mesin --</option>
                                                <template x-for="plant in plantsList" :key="plant.id">
                                                    <option :value="plant.id" x-text="plant.name"></option>
                                                </template>
                                            </select>
                                            <p class="text-[10px] text-amber-600 mt-2 italic">
                                                <span class="font-bold">Info:</span> Karena Anda berada di Departemen
                                                Support (MT/PE), silakan pilih lokasi asal mesin yang sedang dikerjakan.
                                            </p>
                                        </div>
                                    </template>

                                    {{-- Dropdown Pilih Mesin --}}
                                    <div>
                                        <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Pilih Mesin
                                            <span class="text-red-500">*</span></label>

                                        {{-- Disable logic: --}}
                                        {{-- 1. Jika Lokasi Belum Dipilih --}}
                                        {{-- 2. ATAU Jika Lokasi MT/PE TAPI Origin Plant Belum Dipilih --}}
                                        <select x-model="form.machine_id"
                                            :disabled="!form.plant_id || (isSpecialDepartment && !machine_origin_plant_id)"
                                            class="w-full rounded-xl border-blue-200 py-3 px-4 bg-white font-bold text-slate-700 disabled:bg-slate-100 disabled:text-slate-400 cursor-pointer disabled:cursor-not-allowed">

                                            <option value=""
                                                x-text="isLoadingMachines ? 'Loading...' : '-- Pilih Mesin --'">
                                            </option>

                                            {{-- Loop machinesList (Sudah difilter otomatis di JS) --}}
                                            <template x-for="machine in machinesList" :key="machine.id">
                                                <option :value="machine.id" x-text="machine.name"></option>
                                            </template>
                                        </select>

                                        {{-- Helper Text untuk UX --}}
                                        <div class="mt-2">
                                            <p x-show="!form.plant_id" class="text-xs text-slate-400">
                                                &larr; Pilih Lokasi (Plant) di atas terlebih dahulu.
                                            </p>
                                            <p x-show="isSpecialDepartment && !machine_origin_plant_id"
                                                class="text-xs text-amber-500 font-bold animate-pulse">
                                                &uarr; Pilih Plant Asal Mesin terlebih dahulu.
                                            </p>
                                            <p x-show="machinesList.length === 0 && (form.plant_id && (!isSpecialDepartment || machine_origin_plant_id))"
                                                class="text-xs text-red-400">
                                                (Tidak ada data mesin ditemukan di lokasi ini)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Deskripsi Masalah /
                                Pekerjaan <span class="text-red-500">*</span></label>
                            <textarea x-model="form.description" rows="4"
                                class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-slate-700 leading-relaxed"
                                placeholder="Jelaskan detail pekerjaan atau masalah yang terjadi..."></textarea>
                        </div>

                        {{-- Target & Foto --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Target Selesai
                                    (Req)</label>
                                <input type="date" x-model="form.target_completion_date"
                                    class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Foto
                                    Lampiran</label>
                                <input type="file" name="photo"
                                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Action (FIXED: Ganti @click="submitForm" jadi "submitToApi") --}}
                <div class="bg-slate-50 px-8 py-6 border-t border-slate-100 flex justify-end shrink-0">
                    <button type="button" @click="submitToApi()" :disabled="isSubmitting"
                        class="w-full md:w-auto bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white font-bold py-3.5 px-8 rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition transform disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center">

                        <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Buat Tiket'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
