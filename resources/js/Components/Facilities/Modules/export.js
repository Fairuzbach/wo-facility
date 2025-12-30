// resources/js/facilities/modules/export.js

export default() => ({
    /**
     * Toggle select all tickets
     */
    toggleSelectAll() {
        this.selectedTickets = (this.selectedTickets.length === this.pageIds.length) 
            ? [] 
            : [...this.pageIds];
    },

    /**
     * Export tickets to Excel
     */
    async submitExport() {
        if (!this.isLoggedIn) {
            Swal.fire('Error', 'Silakan login terlebih dahulu', 'warning');
            return;
        }

        Swal.fire({
            title: 'Exporting...',
            text: 'Sedang menyiapkan file excel',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const blob = await this.api.exportTickets(this.selectedTickets);

            // Create download link
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `Facilities-WO-${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(downloadUrl);

            Swal.close();

        } catch (error) {
            console.error('Export failed:', error);
            Swal.fire('Error', 'Gagal melakukan export', 'error');
        }
    }
}); 