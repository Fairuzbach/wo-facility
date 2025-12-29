@props(['plants'])

{{-- 1. SIMPAN DATA DARI LARAVEL KE GLOBAL VARIABLE JS --}}
<script>
    window.dbPlants = @json($plants);
    console.log('✅ Plants data loaded:', window.dbPlants);
</script>

<template x-teleport="body">
    <div x-data="ticketModal" @open-create-modal.window="openModal()" x-show="showCreateModal"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-3xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-[#1E3A5F] to-[#2d5285] px-8 py-6 flex justify-between items-center">
                    <h3 class="font-extrabold text-xl text-white">Buat Tiket Baru</h3>
                    <button @click="closeModal()" class="text-white/70 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form x-ref="formTicket" @submit.prevent="submitForm()" enctype="multipart/form-data">
                    <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto">

                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 mb-6">
                            <label class="text-xs font-bold text-slate-500 uppercase mb-3 block">Pelapor</label>
                            <div class="flex gap-2 mb-2">
                                <input type="text" x-model="currentNikInput" @keydown.enter.prevent="addEmployee()"
                                    class="w-full rounded-xl border-slate-200 text-sm py-2 px-4 uppercase"
                                    placeholder="NIK...">
                                <button type="button" @click="addEmployee()"
                                    class="bg-blue-600 text-white px-4 rounded-xl text-xs font-bold hover:bg-blue-700">TAMBAH</button>
                            </div>
                            <div class="text-sm space-y-1">
                                <template x-for="(emp, index) in addedEmployees" :key="index">
                                    <div class="flex justify-between bg-white p-2 border rounded-lg shadow-sm">
                                        <span x-text="emp.name + ' (' + emp.nik + ')'"
                                            class="font-medium text-slate-700"></span>
                                        <button type="button" @click="removeEmployee(index)"
                                            class="text-red-500 text-xs font-bold hover:text-red-700">Hapus</button>

                                        <input type="hidden" name="requester_nik[]" :value="emp.nik">
                                        <input type="hidden" name="requester_name[]" :value="emp.name">
                                        <input type="hidden" name="requester_division[]" :value="emp.department">
                                    </div>
                                </template>
                                <div x-show="addedEmployees.length === 0" class="text-xs text-slate-400 italic">Belum
                                    ada pelapor.</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">
                                Email Notifikasi (Opsional)
                            </label>
                            <input type="email" name="requester_email" class="w-full border p-2 rounded"
                                placeholder="Email perwakilan / PIC...">
                            <small class="text-gray-500">
                                Isi jika ingin mendapatkan update status tiket via email. Kosongkan jika tidak perlu.
                            </small>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi </label>
                                <select name="plant_id" x-model="form.plant_id" @change="handleMainPlantChange()"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-white focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    <template x-for="p in plantsList" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kategori Pekerjaan</label>
                                <select name="category" x-model="form.category"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-white focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Perbaikan Mesin">Perbaikan Mesin</option>
                                    <option value="Modifikasi Mesin">Modifikasi Mesin</option>
                                    <option value="Pembongkaran Mesin">Pembongkaran Mesin</option>
                                    <option value="Relokasi Mesin">Relokasi Mesin</option>
                                    <option value="Pembuatan Alat Baru">Pembuatan Alat Baru</option>
                                    <option value="Pemasangan Mesin">Pemasangan Mesin</option>
                                    <option value="General">General / Lain-lain</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="showMachineSection" x-transition
                            class="bg-blue-50 p-5 rounded-xl border border-blue-100 space-y-4 shadow-inner">

                            <div x-show="isSpecialDepartment">
                                <label class="block text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span
                                        x-text="form.category == 'Pemasangan Mesin' ? 'Mau dipasang di Plant mana?' : 'Mesin ada di Plant mana?'"></span>
                                </label>

                                <select name="target_plant_id" x-model="machine_origin_plant_id"
                                    @change="handleOriginPlantChange()"
                                    class="w-full rounded-xl border-blue-300 bg-white text-sm py-3 px-4 focus:ring-blue-500">
                                    <option value="">-- Pilih Plant Target --</option>
                                    <template x-for="p in plantsList.filter(x => x.name.includes('Plant'))"
                                        :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div x-show="form.category !== 'Pemasangan Mesin'">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Mesin</label>
                                <select name="machine_id" x-model="form.machine_id"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-white"
                                    :required="showMachineSection && form.category !== 'Pemasangan Mesin'"
                                    :disabled="!machine_origin_plant_id || isLoadingMachines">
                                    <option value=""
                                        x-text="isLoadingMachines ? 'Loading...' : '-- Pilih Mesin --'"></option>
                                    <template x-for="m in machinesList" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                                <p x-show="isSpecialDepartment && !machine_origin_plant_id"
                                    class="text-[10px] text-orange-600 mt-1 italic font-semibold">
                                    * Pilih Plant terlebih dahulu.
                                </p>
                            </div>

                            <div x-show="form.category === 'Pemasangan Mesin'">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Mesin Baru</label>
                                <input type="text" name="new_machine_name" x-model="form.new_machine_name"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 focus:ring-blue-500"
                                    placeholder="Contoh: Mesin Drawing Baru Unit 5"
                                    :required="form.category === 'Pemasangan Mesin'">
                                <p class="text-[10px] text-blue-600 mt-1 italic">
                                    * Mesin ini akan otomatis didaftarkan ke database sesuai Plant yang dipilih di atas.
                                </p>
                            </div>

                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Pekerjaan</label>
                            <textarea name="description" x-model="form.description" rows="3"
                                class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 focus:ring-2 focus:ring-blue-500" required
                                placeholder="Jelaskan detail kerusakan atau permintaan..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Target Selesai</label>
                                <input type="date" name="target_completion_date"
                                    x-model="form.target_completion_date"
                                    class="w-full rounded-xl border-slate-200 text-sm py-3 px-4 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Foto / Lampiran</label>
                                <input type="file" name="photo"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                            </div>
                        </div>

                    </div>

                    <div
                        class="bg-slate-50 px-8 py-6 border-t border-slate-200 flex justify-end gap-3 rounded-b-[2.5rem]">
                        <button type="button" @click="closeModal()"
                            class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" :disabled="isSubmitting"
                            class="px-8 py-3 bg-[#1E3A5F] text-white rounded-xl text-sm font-bold hover:bg-[#2d5285] transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Buat Tiket'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ticketModal', () => ({
            // === STATE ===
            showCreateModal: false,
            plantsList: window.dbPlants || [],
            machinesList: [],

            // Pelapor
            currentNikInput: '',
            addedEmployees: [],
            isLoadingEmployee: false,

            // Logika Mesin
            isLoadingMachines: false,
            machine_origin_plant_id: '', // Variable kunci utk dropdown ke-2

            // Form Data
            isSubmitting: false,
            form: {
                plant_id: '',
                category: '',
                machine_id: '',
                new_machine_name: '',
                description: '',
                target_completion_date: ''
            },

            // === COMPUTED ===

            // 1. Cek User MT/PE
            get isSpecialDepartment() {
                if (!this.form.plant_id) return false;
                const selected = this.plantsList.find(p => p.id == this.form.plant_id);
                const name = selected ? selected.name.toUpperCase() : '';
                return name.includes('MT') || name.includes('PE');
            },

            // 2. Cek Kategori Mesin
            get isMachineCategory() {
                const cats = [
                    'Modifikasi Mesin', 'Pembongkaran Mesin', 'Relokasi Mesin',
                    'Perbaikan Mesin', 'Pembuatan Alat Baru', 'Pemasangan Mesin'
                ];
                return cats.includes(this.form.category);
            },

            // 3. Tampilkan Section Mesin?
            get showMachineSection() {
                return this.isMachineCategory;
            },

            // === METHODS ===

            openModal() {
                this.showCreateModal = true;
            },
            closeModal() {
                this.showCreateModal = false;
            },

            // Logika Dropdown 1 (User Location)
            handleMainPlantChange() {
                this.machine_origin_plant_id = '';
                this.machinesList = [];
                this.form.machine_id = '';

                // Jika user BUKAN MT/PE, lokasi mesin otomatis ikut lokasi user
                if (!this.isSpecialDepartment && this.form.plant_id) {
                    this.machine_origin_plant_id = this.form.plant_id;
                    this.fetchMachines(this.form.plant_id);
                }
            },

            // Logika Dropdown 2 (Origin Plant - Khusus MT/PE)
            handleOriginPlantChange() {
                if (this.machine_origin_plant_id) {
                    this.fetchMachines(this.machine_origin_plant_id);
                } else {
                    this.machinesList = [];
                }
            },

            async fetchMachines(plantId) {
                this.isLoadingMachines = true;
                try {
                    const res = await fetch(`/api/machines/plant/${plantId}`);
                    if (res.ok) {
                        this.machinesList = await res.json();
                    } else {
                        this.machinesList = [];
                    }
                } catch (e) {
                    console.error("Error fetching machines:", e);
                } finally {
                    this.isLoadingMachines = false;
                }
            },

            async addEmployee() {
                let nik = this.currentNikInput.trim();
                if (!nik) return;

                if (this.addedEmployees.some(e => e.nik === nik)) {
                    Swal.fire('Info', 'NIK sudah ada!', 'info');
                    this.currentNikInput = '';
                    return;
                }

                this.isLoadingEmployee = true;
                try {
                    const res = await fetch('/api/employee/' + nik);
                    if (!res.ok) throw new Error('Not found');
                    const data = await res.json();

                    this.addedEmployees.push({
                        nik: data.nik,
                        name: data.name,
                        department: data.department || data.department_name
                    });
                    this.currentNikInput = '';
                } catch (err) {
                    Swal.fire('Gagal', 'NIK tidak ditemukan!', 'error');
                } finally {
                    this.isLoadingEmployee = false;
                }
            },

            removeEmployee(index) {
                this.addedEmployees.splice(index, 1);
            },

            async submitForm() {
                if (this.addedEmployees.length === 0) {
                    Swal.fire('Warning', 'Minimal satu pelapor!', 'warning');
                    return;
                }

                this.isSubmitting = true;
                try {
                    let formData = new FormData(this.$refs.formTicket);
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

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Nomor Tiket: ' + result.data.ticket_num,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', result.message || 'Gagal', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Kesalahan jaringan', 'error');
                } finally {
                    this.isSubmitting = false;
                }
            }
        }));
    });
</script>
