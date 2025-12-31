export default () => ({
    // =========================================================================
    // 1. STATE VARIABLES
    // =========================================================================
    selectedTickets: [], 
    // Ambil pageIds tapi pastikan dikonversi jadi String agar cocok dengan checkbox HTML
    pageIds: (window.facilitiesConfig?.pageIds || []).map(id => String(id)),
    
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
        
        // Debugging: Cek apakah login status terbaca
        // console.log('Status Login:', this.isLoggedIn);
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
        // Logika checkbox: Jika semua sudah terpilih, kosongkan. Jika belum, pilih semua.
        if (this.selectedTickets.length === this.pageIds.length) {
            this.selectedTickets = [];
        } else {
            // Kita gunakan spread operator pada pageIds yang sudah distring-kan di atas
            this.selectedTickets = [...this.pageIds];
        }
    },

    async submitExport() {
        if (!this.isLoggedIn) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Akses Ditolak', 'Silakan login terlebih dahulu.', 'warning');
            } else {
                alert('Silakan login terlebih dahulu.');
            }
            return;
        }

        // 1. Ambil Parameter URL saat ini (contoh: ?category=Mesin&status=pending)
        const currentParams = new URLSearchParams(window.location.search);

        // 2. Tambahkan parameter trigger Export
        currentParams.set('export', 'true');

        // 3. Jika ada tiket yang dicentang manual (Checkbox), tambahkan ID-nya
        // Catatan: Biasanya jika ada 'selected_ids', Backend akan memprioritaskan ini.
        if (this.selectedTickets.length > 0) {
            currentParams.set('selected_ids', this.selectedTickets.join(','));
        }
        
        // 4. Bangun URL lengkap dengan parameter gabungan
        // window.routes.export berisi base URL (route('fh.index'))
        const exportUrl = `${window.routes.export}?${currentParams.toString()}`;
        
        // 5. Redirect browser untuk download
        window.location.href = exportUrl;
    }
});