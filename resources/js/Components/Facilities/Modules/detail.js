export default () => ({
    // === STATE VARIABLES (WAJIB) ===
    showDetailModal: false,
    ticket: null, // Menampung data tiket yang sedang dilihat

    // === METHODS ===
    openModal(ticket) {
        this.ticket = ticket;
        this.showDetailModal = true;
    },

    closeModal() {
        this.showDetailModal = false;
        setTimeout(() => {
            this.ticket = null;
        }, 300);
    }
});