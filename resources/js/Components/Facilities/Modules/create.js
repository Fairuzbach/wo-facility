export default () => ({
    // === STATE VARIABLES (WAJIB) ===
    showCreateModal: false,
    isSubmitting: false,
    isLoadingMachines: false,
    isCheckingNik: false,

    // Data Master
    machinesData: [],
    plantsData: [],

    // Form Inputs
    currentNikInput: '',
    currentNameInput: '',
    currentDivInput: '',
    addedEmployees: [],
    
    // Root Variables (Penting agar tidak error di HTML)
    machine_origin_plant_id: '', 
    target_plant_id: '',
    new_machine_name: '',

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

    // === GETTERS ===
    get plantsList() { return this.plantsData || []; },
    get machinesList() {
        if (!this.form.plant_id) return [];
        return this.machinesData.filter(m => m.plant_id == this.form.plant_id);
    },
    get showMachineSection() {
        const categories = ['Modifikasi Mesin', 'Pembongkaran Mesin', 'Relokasi Mesin', 'Perbaikan Mesin', 'Pembuatan Alat Baru'];
        return categories.includes(this.form.category);
    },

    // === METHODS ===
    init() {
        this.$watch('form.plant_id', () => { this.form.machine_id = ''; this.machine_origin_plant_id = ''; });
        
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
        if (!this.currentNameInput) return Swal.fire('Gagal', 'NIK tidak ditemukan', 'error');
        
        if (this.addedEmployees.some(e => e.nik === this.currentNikInput)) return Swal.fire('Info', 'Sudah ada', 'info');

        this.addedEmployees.push({
            nik: this.currentNikInput,
            name: this.currentNameInput,
            division: this.currentDivInput
        });
        this.currentNikInput = ''; this.currentNameInput = ''; this.currentDivInput = '';
    },

    removeEmployee(index) {
        this.addedEmployees.splice(index, 1);
    },

    async submitToApi() {
        // Handle Single Requester
        if (this.addedEmployees.length === 0) {
            if (!this.currentNikInput) return Swal.fire('Error', 'Wajib isi NIK', 'warning');
            if (!this.currentNameInput) {
                Swal.fire({ title: 'Cek NIK...', didOpen: () => Swal.showLoading() });
                await this.checkNik(this.currentNikInput);
                Swal.close();
            }
            if (this.currentNameInput) {
                this.addedEmployees.push({ nik: this.currentNikInput, name: this.currentNameInput, division: this.currentDivInput });
            } else {
                return Swal.fire('Gagal', 'NIK tidak valid', 'error');
            }
        }

        // Validasi
        if (!this.form.plant_id || !this.form.category || !this.form.description) {
            return Swal.fire('Error', 'Lengkapi data wajib (*)', 'warning');
        }

        this.isSubmitting = true;
        let formData = new FormData();
        
        // Append Data
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
        
        if(this.new_machine_name) formData.append('new_machine_name', this.new_machine_name);
        if(this.target_plant_id) formData.append('target_plant_id', this.target_plant_id);
        if(this.machine_origin_plant_id) formData.append('machine_origin_plant_id', this.machine_origin_plant_id);
        if(this.form.target_completion_date) formData.append('target_completion_date', this.form.target_completion_date);
        if(this.form.machine_id) formData.append('machine_id', this.form.machine_id);
        
        const fileInput = document.querySelector('input[name="photo"]');
        if (fileInput?.files[0]) formData.append('photo', fileInput.files[0]);

        try {
            let url = window.facilitiesConfig?.createUrl || '/fh/store';
            let res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: formData
            });
            let result = await res.json();
            
            if (res.ok) {
                Swal.fire('Berhasil', result.message, 'success').then(() => window.location.reload());
            } else {
                let msg = result.errors ? Object.values(result.errors).flat()[0] : result.message;
                Swal.fire('Gagal', msg, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Kesalahan Sistem', 'error');
        } finally {
            this.isSubmitting = false;
        }
    }
});