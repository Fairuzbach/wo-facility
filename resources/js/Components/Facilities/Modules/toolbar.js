export default () => ({
    // =========================================================================
    // 1. STATE VARIABLES (WAJIB ADA UNTUK TABLE)
    // =========================================================================
    selectedTickets: [], // State untuk Checkbox
    pageIds: window.facilitiesConfig?.pageIds || [],
    isLoggedIn: window.facilitiesConfig?.isLoggedIn || false,
    apiToken: window.facilitiesConfig?.apiToken || null,
    
    // Jam Digital
    currentTime: '',
    currentDate: '',

    // =========================================================================
    // 2. METHODS
    // =========================================================================
    init() {
        this.updateTime();
        setInterval(() => this.updateTime(), 1000);
    },

    updateTime() {
        const now = new Date();
        this.currentDate = now.toLocaleDateString('id-ID', {
            day: '2-digit', month: 'long', year: 'numeric'
        });
        this.currentTime = now.toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit', hour12: false
        });
    },

    toggleSelectAll() {
        if (this.selectedTickets.length === this.pageIds.length) {
            this.selectedTickets = [];
        } else {
            this.selectedTickets = [...this.pageIds];
        }
    },

    async submitExport() {
        if (!this.isLoggedIn) {
            Swal.fire('Akses Ditolak', 'Silakan login terlebih dahulu.', 'warning');
            return;
        }

        let url = window.routes.export + '?export=true';
        if (this.selectedTickets.length > 0) {
            url += '&selected_ids=' + this.selectedTickets.join(',');
        }
        
        // Redirect browser untuk download file
        window.location.href = url;
    }
});