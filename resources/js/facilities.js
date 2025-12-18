console.log('FACILITIES JS LOADED');

document.addEventListener('alpine:init', () => {
    console.log('ALPINE INIT FACILITIES');

    // Ambil config dari window, atau object kosong jika undefined
    const config = window.facilitiesConfig || {};

    Alpine.data('facilitiesData', () => ({
        // State Modal
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,

        // Data Init
        ticket: config.openTicket || null,
        machinesData: config.machines || [],
        techniciansData: config.technicians || [],
        pageIds: config.pageIds || [],

        // Time State
        currentDate: '',
        currentTime: '',
        currentDateDB: '',

        // Create Form
        form: {
            requester_name: '',
            plant_id: '',
            machine_id: '',
            new_machine_name: '',
            category: '',
            description: '',
            target_completion_date: '',
            photo: null
        },

        // Edit Form
        editForm: {
            id: '',
            status: '',
            start_date: '',
            actual_completion_date: '',
            selectedTechs: []
        },

        // Helper Data
        filteredMachines: [],
        selectedTickets: [],

        // --- METHODS ---

        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);

            // Auto open ticket jika ada (dari notifikasi)
            if (this.ticket) {
                this.showDetailModal = true;
                // Bersihkan URL
                const url = new URL(window.location);
                url.searchParams.delete('open_ticket_id');
                window.history.replaceState({}, '', url);
            }
        },

        updateTime() {
            const now = new Date();
            this.currentDate = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            this.currentDateDB = `${y}-${m}-${d}`;

            this.currentTime = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        },

        resetForm() {
            this.form = {
                requester_name: '',
                plant_id: '',
                machine_id: '',
                new_machine_name: '',
                category: '',
                description: '',
                target_completion_date: '',
                photo: null
            };
            this.filteredMachines = [];
        },

        filterMachines() {
            this.form.machine_id = '';
            this.filteredMachines = this.machinesData.filter(
                m => m.plant_id == this.form.plant_id
            );
        },

        needsMachineSelect() {
            return [
                'Modifikasi Mesin',
                'Pembongkaran Mesin',
                'Relokasi Mesin',
                'Perbaikan',
                'Pembuatan Alat Baru'
            ].includes(this.form.category);
        },

        // --- METHOD YANG HILANG SEBELUMNYA (WAJIB ADA) ---

        openEditModal(wo) {
            this.ticket = wo;
            this.editForm.id = wo.id;
            this.editForm.status = wo.status;
            this.editForm.start_date = wo.start_date;
            this.editForm.actual_completion_date = wo.actual_completion_date;
            // Map technicians object ke array ID
            this.editForm.selectedTechs = wo.technicians ? wo.technicians.map(t => t.id) : [];
            
            this.showEditModal = true;

            // Re-init flatpickr jika ada di modal edit
            setTimeout(() => {
                document.querySelectorAll('.date-picker-edit').forEach(el => flatpickr(el, { dateFormat: 'Y-m-d' }));
            }, 100);
        },

        toggleSelectAll() {
            // Jika semua di halaman ini terpilih, kosongkan. Jika belum, pilih semua ID di halaman ini.
            this.selectedTickets = (this.selectedTickets.length === this.pageIds.length) ? [] : [...this.pageIds];
        },

        submitExport() {
            // Logic export berdasarkan item yang diceklis
            let url = window.routes.export + '?export=true';
            
            if (this.selectedTickets.length > 0) {
                url += '&selected_ids=' + this.selectedTickets.join(',');
            }
            
            // Redirect untuk download
            window.location.href = url;
        },

        // Logic Multi-Select Teknisi
        toggleTech(id) {
            if (this.editForm.selectedTechs.includes(id)) {
                this.editForm.selectedTechs = this.editForm.selectedTechs.filter(t => t !== id);
            } else {
                if (this.editForm.selectedTechs.length >= 5) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Limit Reached',
                        text: 'Max 5 technicians allowed!',
                        confirmButtonColor: '#1E3A5F'
                    });
                    return;
                }
                this.editForm.selectedTechs.push(id);
            }
        },

        getTechName(id) {
            let tech = this.techniciansData.find(t => t.id == id);
            return tech ? tech.name : 'Unknown';
        }
    }));
});