export default () => ({
    // =========================================================================
    // 1. STATE VARIABLES
    // =========================================================================
    showCreateModal: false,
    isSubmitting: false,
    isLoadingMachines: false,
    isCheckingNik: false,

    // Data Master
    machinesData: [],
    plantsData: [],

    // Input Guest / Pelapor
    currentNikInput: '',
    currentNameInput: '',
    currentDivInput: '',
    addedEmployees: [],

    // Variabel Root
    machine_origin_plant_id: '', // <--- PENTING: Untuk Plant Asal (MT/PE)
    target_plant_id: '',
    new_machine_name: '',

    // Form Utama
    form: {
        requester_name: '',
        requester_email: '',
        plant_id: '',
        machine_id: '',     
        category: '',
        description: '',
        location_details: '',
        target_completion_date: '',
        photo: null
    },

    // =========================================================================
    // 2. GETTERS (LOGIKA UI DINAMIS)
    // =========================================================================

    get plantsList() {
        return this.plantsData || [];
    },

    // Helper: Cari data object plant yang sedang dipilih
    get selectedPlant() {
        if (!this.form.plant_id) return null;
        return this.plantsData.find(p => p.id == this.form.plant_id);
    },

    // [LOGIKA BARU] Cek apakah Lokasi yang dipilih adalah MT atau PE
    get isSpecialDepartment() {
        const p = this.selectedPlant;
        if (!p) return false;
        
        const name = p.name.toUpperCase();
        // Sesuaikan dengan nama persis di Database Anda
        return name === 'MT' || name === 'PE' || name.includes('MAINTENANCE') || name.includes('ENGINEERING');
    },

    // [LOGIKA FILTER MESIN]
    get machinesList() {
        // KASUS 1: User memilih MT atau PE
        // Maka daftar mesin harus diambil dari "Plant Asal" (machine_origin_plant_id), bukan dari "Plant Lokasi"
        if (this.isSpecialDepartment) {
            if (!this.machine_origin_plant_id) return [];
            return this.machinesData.filter(m => m.plant_id == this.machine_origin_plant_id);
        }

        // KASUS 2: User memilih Plant Biasa (misal: Plant A)
        // Maka daftar mesin diambil langsung dari Plant A
        if (!this.form.plant_id) return [];
        return this.machinesData.filter(m => m.plant_id == this.form.plant_id);
    },

    get showMachineSection() {
        const categoriesWithMachine = [
            'Modifikasi Mesin', 'Pembongkaran Mesin', 'Relokasi Mesin', 
            'Perbaikan Mesin', 'Pembuatan Alat Baru', 'Pemasangan Mesin'
        ];
        return categoriesWithMachine.includes(this.form.category);
    },

    get isNewMachineInstallation() {
        return this.form.category === 'Pemasangan Mesin';
    },

    // =========================================================================
    // 3. METHODS
    // =========================================================================

    init() {
        // Reset jika lokasi utama berubah
        this.$watch('form.plant_id', () => {
            this.form.machine_id = '';
            this.machine_origin_plant_id = '';
        });

        // Auto Check NIK
        let debounceTimer;
        this.$watch('currentNikInput', (value) => {
            clearTimeout(debounceTimer);
            if (value && value.length >= 3) {
                this.isCheckingNik = true;
                debounceTimer = setTimeout(() => this.checkNik(value), 500);
            } else {
                this.currentNameInput = '';
                this.currentDivInput = '';
                this.isCheckingNik = false;
            }
        });
    },

    openCreateModalCheck() {
        this.showCreateModal = true;
    },

    async checkNik(nik) {
        try {
            const response = await fetch(`/check-employee/${nik}`);
            const data = await response.json();
            if (data.success) {
                this.currentNameInput = data.name;
                this.currentDivInput = data.division || '-';
            } else {
                this.currentNameInput = '';
                this.currentDivInput = '';
            }
        } catch (e) { console.error(e); } 
        finally { this.isCheckingNik = false; }
    },

    async addEmployee() {
        if (!this.currentNikInput) return Swal.fire('Data Kurang', 'Isi NIK', 'warning');
        if (!this.currentNameInput) await this.checkNik(this.currentNikInput);
        
        if (this.currentNameInput) {
            if (this.addedEmployees.some(e => e.nik === this.currentNikInput)) return Swal.fire('Info', 'Sudah ada', 'info');
            this.addedEmployees.push({ nik: this.currentNikInput, name: this.currentNameInput, division: this.currentDivInput });
            this.currentNikInput = ''; this.currentNameInput = ''; this.currentDivInput = '';
        } else {
            Swal.fire('Gagal', 'NIK tidak ditemukan', 'error');
        }
    },

    removeEmployee(index) {
        this.addedEmployees.splice(index, 1);
    },

    async submitToApi() {
        // 1. Handle Single Pelapor
        if (this.addedEmployees.length === 0) {
            if (this.currentNikInput) {
                if (!this.currentNameInput) await this.checkNik(this.currentNikInput);
                if (this.currentNameInput) {
                    this.addedEmployees.push({ nik: this.currentNikInput, name: this.currentNameInput, division: this.currentDivInput });
                } else {
                    return Swal.fire('Gagal', 'NIK tidak valid.', 'error');
                }
            } else {
                return Swal.fire('Error', 'Wajib isi NIK Pelapor.', 'warning');
            }
        }

        // 2. Validasi Form
        if (!this.form.plant_id || !this.form.category || !this.form.description) {
            return Swal.fire('Error', 'Lengkapi data: Lokasi, Kategori, dan Deskripsi.', 'warning');
        }

        // 3. Submit
        this.isSubmitting = true;
        let formData = new FormData();

        this.addedEmployees.forEach((emp, i) => {
            formData.append(`requester_nik[${i}]`, emp.nik);
            formData.append(`requester_name[${i}]`, emp.name);
            formData.append(`requester_division[${i}]`, emp.division);
        });
        if(this.form.requester_email) formData.append('requester_email', this.form.requester_email);

        formData.append('plant_id', this.form.plant_id);
        formData.append('category', this.form.category);
        formData.append('description', this.form.description);
        formData.append('location_details', this.form.location_details || '');

        // --- LOGIKA PENGIRIMAN DATA MESIN ---
        if (this.form.category === 'Pemasangan Mesin') {
            if(this.new_machine_name) formData.append('new_machine_name', this.new_machine_name);
            if(this.target_plant_id) formData.append('target_plant_id', this.target_plant_id);
        } else {
            // Jika MT/PE, kita kirim ID mesin yang dipilih (meskipun dari plant lain)
            if (this.form.machine_id) formData.append('machine_id', this.form.machine_id);
            // Opsional: Kirim origin plant id jika backend butuh
            if (this.machine_origin_plant_id) formData.append('machine_origin_plant_id', this.machine_origin_plant_id);
        }
        
        if (this.form.target_completion_date) formData.append('target_completion_date', this.form.target_completion_date);

        const fileInput = document.querySelector('input[name="photo"]');
        if (fileInput?.files[0]) formData.append('photo', fileInput.files[0]);

        try {
            let url = window.facilitiesConfig?.createUrl || '/fh/store';
            let res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            let result = await res.json();

            if (res.ok) {
                Swal.fire('Berhasil!', result.message, 'success').then(() => window.location.reload());
                this.showCreateModal = false;
            } else {
                let msg = result.errors ? Object.values(result.errors).flat()[0] : result.message;
                Swal.fire('Gagal', msg, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        } finally {
            this.isSubmitting = false;
        }
    }
});