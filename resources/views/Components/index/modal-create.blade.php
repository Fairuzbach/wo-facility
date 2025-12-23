@props(['plants'])

{{-- 1. SIMPAN DATA DARI LARAVEL KE GLOBAL VARIABLE JS --}}
<script>
    window.dbPlants = @json($plants);
    console.log('✅ Plants data loaded:', window.dbPlants);
</script>

<template x-teleport="body">
    <div x-data="ticketModal" @open-create-modal.window="console.log('🔔 Event received'); openModal()"
        x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all">

                {{-- Header --}}
                <div
                    class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 border-b border-white/10 flex justify-between items-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/5 pattern-dots"></div>
                    <h3 class="font-extrabold text-xl text-white tracking-tight relative z-10">Create New Ticket
                        (Multiple)</h3>
                    <button type="button" @click="closeModal()"
                        class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition relative z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- FORM --}}
                <form x-ref="formTicket" @submit.prevent="submitForm()" enctype="multipart/form-data">
                    @csrf
                    <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">

                        {{-- === SECTION 1: PELAPOR === --}}
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/60 mb-6">
                            <h4
                                class="text-xs font-bold text-slate-500 uppercase mb-3 tracking-wider flex items-center gap-2">
                                Daftar Pelapor</h4>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Tambah NIK</label>
                                <div class="relative flex gap-2">
                                    <input type="text" x-model="currentNikInput"
                                        @keydown.enter.prevent="addEmployee()"
                                        class="w-full rounded-xl border-slate-200 text-sm py-2.5 px-4 font-bold uppercase"
                                        placeholder="Ketik NIK lalu tekan ENTER...">
                                    <button type="button" @click="addEmployee()"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">TAMBAH</button>
                                </div>
                                <div x-show="isLoadingEmployee" class="text-xs text-blue-500 mt-1">Mencari data...</div>
                            </div>

                            {{-- Tabel Pelapor --}}
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-slate-100 text-xs font-bold text-slate-500 uppercase">
                                        <tr>
                                            <th class="px-4 py-3">NIK</th>
                                            <th class="px-4 py-3">Nama</th>
                                            <th class="px-4 py-3">Divisi</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(emp, index) in addedEmployees" :key="index">
                                            <tr>
                                                <td class="px-4 py-2 font-bold" x-text="emp.nik"></td>
                                                <td class="px-4 py-2" x-text="emp.name"></td>
                                                <td class="px-4 py-2 text-slate-500" x-text="emp.department"></td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" @click="removeEmployee(index)"
                                                        class="text-red-400 hover:text-red-600 font-bold">Hapus</button>
                                                    <input type="hidden" name="requester_nik[]" :value="emp.nik">
                                                    <input type="hidden" name="requester_name[]"
                                                        :value="emp.name">
                                                    <input type="hidden" name="requester_division[]"
                                                        :value="emp.department">
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="addedEmployees.length === 0">
                                            <td colspan="4"
                                                class="px-4 py-6 text-center text-slate-400 italic text-xs">
                                                Belum ada karyawan.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- === SECTION 2: INPUT DATA === --}}
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Plant</label>
                                <select name="plant_id" x-model="form.plant_id"
                                    @change="fetchMachines($event.target.value)"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4" required>
                                    <option value="">Select...</option>
                                    <template x-for="p in plantsList" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Category</label>
                                <select name="category" x-model="form.category"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4" required>
                                    <option value="">Select...</option>
                                    <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                    <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                    <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                    <option value="Relokasi Mesin">Relokasi Mesin</option>
                                    <option value="Perbaikan Mesin">Perbaikan Mesin</option>
                                    <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                    <option value="Rakit Steel Drum">Rakit Steel Drum</option>
                                    <option value="Lain-Lain">Lain-Lain</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="needsMachineSelect" style="display: none;">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Machine</label>
                            <select name="machine_id" x-model="form.machine_id"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4"
                                :disabled="isLoadingMachines || !form.plant_id">
                                <option value="" x-text="selectMachineText"></option>
                                <template x-for="machine in machinesList" :key="machine.id">
                                    <option :value="machine.id" x-text="machine.name"></option>
                                </template>
                            </select>
                            <p x-show="form.plant_id && machinesList.length === 0 && !isLoadingMachines"
                                class="text-xs text-slate-500 mt-1">
                                Tidak ada mesin di plant ini.
                            </p>
                        </div>

                        <div x-show="form.category == 'Pemasangan Mesin'" style="display: none;">
                            <label class="block text-sm font-bold text-slate-700 mb-2">New Machine Name</label>
                            <input type="text" name="new_machine_name" x-model="form.new_machine_name"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                            <textarea name="description" x-model="form.description" rows="3"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4" required></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Target Date</label>
                            <input type="text" name="target_completion_date"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4" x-init="flatpickr($el, { minDate: 'today', dateFormat: 'Y-m-d' })">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Attachment</label>
                            <input type="file" name="photo"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" />
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="bg-slate-50 px-8 py-6 border-t border-slate-200 flex justify-end gap-3 rounded-b-[2.5rem]">
                        <button type="button" @click="closeModal()"
                            class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>

                        <button type="submit" :disabled="isSubmitting"
                            class="px-8 py-3 bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] text-white rounded-xl text-sm font-bold transition transform flex items-center gap-2"
                            :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-lg hover:scale-105'">
                            <span x-show="!isSubmitting">Create Ticket</span>
                            <span x-show="isSubmitting">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('alpine:init', () => {
        console.log('🚀 Alpine.js initializing ticketModal...');

        Alpine.data('ticketModal', () => ({
            // === STATE ===
            showCreateModal: false,
            currentNikInput: '',
            isLoadingEmployee: false,
            isSubmitting: false,
            addedEmployees: [],
            plantsList: window.dbPlants || [],

            // STATE UNTUK MESIN
            machinesList: [],
            isLoadingMachines: false,

            form: {
                plant_id: '',
                category: '',
                description: '',
                machine_id: '',
                new_machine_name: '',
                target_completion_date: '',
            },

            // === COMPUTED ===
            get needsMachineSelect() {
                return ['Modifikasi Mesin', 'Pembongkaran Mesin', 'Relokasi Mesin',
                    'Perbaikan Mesin', 'Pembuatan Alat Baru'
                ].includes(this.form.category);
            },

            get selectMachineText() {
                if (!this.form.plant_id) return 'Pilih Plant terlebih dahulu...';
                if (this.isLoadingMachines) return 'Loading mesin...';
                return 'Select Machine...';
            },

            // === METHODS ===
            openModal() {
                console.log('📂 Opening modal...');
                this.showCreateModal = true;
            },

            closeModal() {
                console.log('❌ Closing modal...');
                this.showCreateModal = false;
            },

            async fetchMachines(plantId) {
                console.log('🏭 fetchMachines called with plantId:', plantId);

                // Reset dulu
                this.machinesList = [];
                this.form.machine_id = '';

                if (!plantId) {
                    console.log('⚠️ No plantId, skipping fetch');
                    return;
                }

                this.isLoadingMachines = true;
                console.log('🔍 Fetching machines for plant:', plantId);
                console.log('📍 URL:', `/api/machines/plant/${plantId}`);

                try {
                    const response = await fetch(`/api/machines/plant/${plantId}`);
                    console.log('📡 Response status:', response.status);
                    console.log('📡 Response ok:', response.ok);

                    // Coba baca response text dulu untuk debug
                    const responseText = await response.text();
                    console.log('📄 Response text:', responseText);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${responseText}`);
                    }

                    // Parse JSON
                    const data = JSON.parse(responseText);
                    this.machinesList = data;
                    console.log('✅ Machines loaded:', data);
                } catch (error) {
                    console.error('❌ Error fetching machines:', error);
                    console.error('❌ Error details:', {
                        message: error.message,
                        stack: error.stack
                    });
                    this.machinesList = [];
                    alert('Gagal memuat data mesin: ' + error.message);
                } finally {
                    this.isLoadingMachines = false;
                }
            },

            addEmployee() {
                let nik = this.currentNikInput.trim();
                if (!nik) return;

                if (this.addedEmployees.some(e => e.nik === nik)) {
                    alert('NIK ini sudah ada di daftar!');
                    this.currentNikInput = '';
                    return;
                }

                this.isLoadingEmployee = true;

                fetch('/api/employee/' + nik)
                    .then(res => {
                        if (!res.ok) throw new Error('Not found');
                        return res.json();
                    })
                    .then(data => {
                        if (data && data.name) {
                            this.addedEmployees.push({
                                nik: data.nik,
                                name: data.name,
                                department: data.department
                            });
                            this.currentNikInput = '';
                            console.log('✅ Employee added:', data);
                        } else {
                            alert('Data karyawan tidak valid.');
                        }
                    })
                    .catch(err => {
                        console.error('❌ Employee fetch error:', err);
                        alert('NIK Tidak Ditemukan!');
                    })
                    .finally(() => {
                        this.isLoadingEmployee = false;
                    });
            },

            removeEmployee(index) {
                console.log('🗑️ Removing employee at index:', index);
                this.addedEmployees.splice(index, 1);
            },

            async submitForm() {
                console.log('🎯 Submit function called!');
                console.log('📊 Current state:', {
                    employees: this.addedEmployees,
                    form: this.form,
                    isSubmitting: this.isSubmitting
                });

                if (this.addedEmployees.length === 0) {
                    alert('Harap masukkan minimal satu Pelapor (NIK)!');
                    return;
                }

                let formEl = this.$refs.formTicket;
                if (!formEl.checkValidity()) {
                    console.warn('⚠️ Form validation failed');
                    formEl.reportValidity();
                    return;
                }

                this.isSubmitting = true;
                console.log('⏳ Submitting to server...');

                try {
                    let formData = new FormData(formEl);

                    // Debug FormData
                    console.log('📦 FormData contents:');
                    for (let pair of formData.entries()) {
                        console.log(`  ${pair[0]}: ${pair[1]}`);
                    }

                    let response = await fetch('/fh/store', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    let result = await response.json();
                    console.log('📬 Server response:', result);

                    if (response.ok) {
                        alert('Tiket berhasil dibuat!');
                        this.closeModal();
                        window.location.reload();
                    } else {
                        console.error('❌ Server error:', result);
                        let pesan = result.message || 'Gagal menyimpan tiket.';
                        if (result.errors) {
                            pesan += '\n' + Object.values(result.errors).flat().join('\n');
                        }
                        alert(pesan);
                    }
                } catch (error) {
                    console.error('💥 Network error:', error);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isSubmitting = false;
                    console.log('✅ Submit process finished');
                }
            }
        }));

        console.log('✅ ticketModal registered');
    });
</script>
