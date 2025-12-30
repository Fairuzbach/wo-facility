export default () => ({
    // =========================================================================
    // 1. STATE VARIABLES (WAJIB ADA!)
    // =========================================================================
    showEditModal: false,
    
    // Ambil data global dari config
    apiToken: window.facilitiesConfig?.apiToken || null,
    isLoggedIn: window.facilitiesConfig?.isLoggedIn || false,
    technicians: window.facilitiesConfig?.technicians || [], // Data teknisi

    // Form Object (Penyebab Error "editForm is not defined")
    editForm: {
        id: '',
        status: '',
        note: '',
        completion_note: '',
        start_date: '',
        actual_completion_date: '',
        selectedTechs: []
    },

    // =========================================================================
    // 2. METHODS
    // =========================================================================

    init() {
        // Init jika diperlukan
    },

    openModal(wo) { // Ganti nama jadi 'openModal' agar standar, atau sesuaikan dengan blade
        this.openEditModal(wo);
    },

    // Fungsi asli Anda (tetap dipertahankan)
    openEditModal(wo) {
        if (!this.isLoggedIn) {
            Swal.fire('Akses Ditolak', 'Silakan login terlebih dahulu', 'warning');
            return;
        }

        // Reset form dulu
        this.resetEditForm();

        // Isi form dengan data tiket
        this.editForm.id = wo.id;
        this.editForm.status = wo.status;
        this.editForm.start_date = wo.start_date;
        this.editForm.actual_completion_date = wo.actual_completion_date;
        
        // Mapping teknisi (pastikan wo.technicians ada)
        this.editForm.selectedTechs = wo.technicians 
            ? wo.technicians.map(t => t.id) 
            : [];
            
        this.editForm.note = wo.completion_note || '';
        this.editForm.completion_note = wo.completion_note || '';

        this.showEditModal = true;

        // Init Datepicker
        setTimeout(() => {
            document.querySelectorAll('.date-picker-edit').forEach(el => {
                if (typeof flatpickr !== 'undefined') {
                    flatpickr(el, { dateFormat: 'Y-m-d' });
                }
            });
        }, 100);
    },

    closeEditModal() {
        this.showEditModal = false;
        setTimeout(() => this.resetEditForm(), 300);
    },

    toggleTech(id) {
        if (this.editForm.selectedTechs.includes(id)) {
            this.editForm.selectedTechs = this.editForm.selectedTechs.filter(t => t !== id);
        } else {
            if (this.editForm.selectedTechs.length >= 5) {
                Swal.fire('Limit Reached', 'Maksimal 5 Teknisi!', 'warning');
                return;
            }
            this.editForm.selectedTechs.push(id);
        }
    },

    getTechName(id) {
        const tech = this.technicians.find(t => t.id == id);
        return tech ? tech.name : 'Unknown';
    },

    async submitUpdateStatus() { // Sesuaikan nama fungsi dengan tombol di Blade
        // Validasi Sederhana
        if (this.editForm.status === 'completed' && !this.editForm.actual_completion_date) {
            Swal.fire('Data Belum Lengkap', 'Mohon isi Tanggal Selesai (Actual)!', 'warning');
            return;
        }

        if (this.editForm.status === 'cancelled' && !this.editForm.completion_note) {
            Swal.fire('Wajib Diisi', 'Mohon isi Alasan Pembatalan!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Updating...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const baseUrl = window.facilitiesConfig?.apiUrl || '/api/facility-wo';
            const url = `${baseUrl}/${this.editForm.id}/update-status`;

            const response = await fetch(url, {
                method: 'PUT', // Pastikan method sesuai route (PUT/POST)
                headers: {
                    'Authorization': `Bearer ${this.apiToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    status: this.editForm.status,
                    note: this.editForm.note,
                    completion_note: this.editForm.completion_note,
                    start_date: this.editForm.start_date,
                    actual_completion_date: this.editForm.actual_completion_date,
                    facility_tech_ids: this.editForm.selectedTechs
                })
            });

            const result = await response.json();

            if (response.ok) {
                Swal.fire('Berhasil', 'Status tiket diperbarui!', 'success')
                    .then(() => window.location.reload());
                this.closeEditModal();
            } else {
                throw new Error(result.message || 'Gagal update status');
            }

        } catch (error) {
            console.error(error);
            Swal.fire('Gagal', error.message || 'Terjadi kesalahan sistem', 'error');
        }
    },

    resetEditForm() {
        this.editForm = {
            id: '',
            status: '',
            note: '',
            completion_note: '',
            start_date: '',
            actual_completion_date: '',
            selectedTechs: []
        };
    }
});