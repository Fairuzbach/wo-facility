console.log('FACILITIES JS LOADED');

document.addEventListener('alpine:init', () => {
    console.log('ALPINE INIT FACILITIES');

    const config = window.facilitiesConfig ?? {};

    Alpine.data('facilitiesData', () => ({
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,

        ticket: config.openTicket ?? null,
        machinesData: config.machines ?? [],
        techniciansData: config.technicians ?? [],
        pageIds: config.pageIds ?? [],

        currentDate: '',
        currentTime: '',
        currentDateDB: '',

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

        editForm: {
            id: '',
            status: '',
            start_date: '',
            actual_completion_date: '',
            selectedTechs: []
        },

        filteredMachines: [],
        selectedTickets: [],
        needsMachineSelect() {
    return [
        'Modifikasi Mesin',
        'Pembongkaran Mesin',
        'Relokasi Mesin',
        'Perbaikan',
        'Pembuatan Alat Baru'
    ].includes(this.form.category);
},
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);

            if (this.ticket) {
                this.showDetailModal = true;
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
        }
    }));
});
