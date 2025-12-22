console.log('FACILITIES JS LOADED');

document.addEventListener('alpine:init', () => {
    console.log('ALPINE INIT FACILITIES');

    const config = window.facilitiesConfig || {};
    
    // AMBIL TOKEN DARI LOCALSTORAGE
    // Ini penting agar sinkron dengan fitur login yang kita bahas sebelumnya
    const localToken = localStorage.getItem('token'); 

    Alpine.data('facilitiesData', (initialData) => ({
        // --- AUTH STATE ---
        isLoggedIn: initialData.isLogged,
        apiToken: initialData.token || localStorage.getItem('token'),

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
            selectedTechs: [],
            note: ''
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
                const url = new URL(window.location);
                url.searchParams.delete('open_ticket_id');
                window.history.replaceState({}, '', url);
            }
        },

        // --- NEW: FUNGSI BUKA MODAL CREATE DENGAN CEK LOGIN ---
        openCreateModalCheck() {
            console.log("Status Login:", this.isLoggedIn);
            console.log("Token:", this.apiToken);
            if (this.isLoggedIn) {
                this.showCreateModal = true;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Terbatas',
                    text: 'Silakan login terlebih dahulu untuk membuat tiket.',
                    showCancelButton: true,
                    confirmButtonText: 'Login Sekarang',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#3085d6',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/login'; 
                    }
                });
            }
        },

        async submitToApi() {
            try {
                // 1. Validasi Sederhana
                if (!this.form.plant_id || !this.form.category || !this.form.description) {
                    Swal.fire('Error', 'Mohon lengkapi data wajib (*)', 'warning');
                    return;
                }

                // 2. Siapkan Data
                let formData = new FormData();
                formData.append('requester_name', this.form.requester_name);
                formData.append('plant_id', this.form.plant_id);
                formData.append('category', this.form.category);
                formData.append('description', this.form.description);
                formData.append('location_details', 'Area Produksi');
                
                if (this.form.target_completion_date) {
                    formData.append('target_completion_date', this.form.target_completion_date);
                }

                if (this.form.machine_id) formData.append('machine_id', this.form.machine_id);
                if (this.form.new_machine_name) formData.append('machine_name', this.form.new_machine_name);
                
                const fileInput = document.querySelector('input[name="photo"]');
                if (fileInput && fileInput.files[0]) {
                    formData.append('photo', fileInput.files[0]);
                }

                // 3. Kirim Request
                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                let response = await fetch(config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + this.apiToken, // Pakai token dari state
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                let result = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Tiket berhasil dibuat!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                    
                    this.showCreateModal = false;
                    this.resetForm();
                } else {
                    // Handle Token Expired (401)
                    if (response.status === 401) {
                         Swal.fire('Session Expired', 'Silakan login ulang', 'error')
                             .then(() => window.location.href = '/login');
                         return;
                    }
                    console.error('API Error:', result);
                    Swal.fire('Error', result.message || 'Gagal menyimpan data', 'error');
                }

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        },
        async submitUpdateStatus() {
            // --- 1. VALIDASI FRONTEND ---
            // Jika status 'completed', pastikan tanggal selesai diisi
            if (this.editForm.status === 'completed' && !this.editForm.actual_completion_date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Mohon isi Tanggal Selesai (Actual Date) untuk status Completed!',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }

            // --- 2. LOADING STATE ---
            Swal.fire({
                title: 'Updating...',
                text: 'Sedang memperbarui status tiket',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                // --- 3. PERSIAPAN DATA ---
                // Kita gunakan JSON.stringify karena lebih rapi untuk data array (teknisi)
                const payload = {
                    status: this.editForm.status,
                    note: this.editForm.note, // <--- Field Note Baru
                    actual_completion_date: this.editForm.actual_completion_date,
                    start_date: this.editForm.start_date,
                    facility_tech_ids: this.editForm.selectedTechs // Array ID teknisi
                };

                // URL: /api/facility-wo/{id}/update-status
                // Pastikan config.apiUrl mengarah ke /api/facility-wo
                const url = `${config.apiUrl}/${this.editForm.id}/update-status`;

                // --- 4. KIRIM REQUEST (PUT) ---
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + this.apiToken,
                        'Content-Type': 'application/json', // Wajib JSON
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                // --- 5. HANDLE RESPONSE ---
                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status tiket berhasil diperbarui!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload(); // Refresh untuk update tabel
                    });
                    
                    this.showEditModal = false;
                } else {
                    // Handle Error Validasi Laravel
                    console.error('Update Error:', result);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message || 'Terjadi kesalahan saat update data.',
                        confirmButtonColor: '#EF4444'
                    });
                }

            } catch (error) {
                console.error('System Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan koneksi ke server', 'error');
            }
        },
        updateTime() {
            const now = new Date();
            this.currentDate = now.toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric'
            });

            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            this.currentDateDB = `${y}-${m}-${d}`;

            this.currentTime = now.toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', hour12: false
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
                'Perbaikan Mesin',
                'Pembuatan Alat Baru'
            ].includes(this.form.category);
        },

        openEditModal(wo) {
            // Edit juga butuh login, proteksi di sini juga boleh
            if (!this.isLoggedIn) return; 

            this.ticket = wo;
            this.editForm.id = wo.id;
            this.editForm.status = wo.status;
            this.editForm.start_date = wo.start_date;
            this.editForm.actual_completion_date = wo.actual_completion_date;
            this.editForm.selectedTechs = wo.technicians ? wo.technicians.map(t => t.id) : [];
            this.editForm.note = wo.completion_note || '';
            
            this.showEditModal = true;

            setTimeout(() => {
                document.querySelectorAll('.date-picker-edit').forEach(el => flatpickr(el, { dateFormat: 'Y-m-d' }));
            }, 100);
        },

        toggleSelectAll() {
            this.selectedTickets = (this.selectedTickets.length === this.pageIds.length) ? [] : [...this.pageIds];
        },

        // --- REVISI EXPORT LOGIC ---
        // Kita tidak bisa pakai window.location.href karena harus kirim Header Authorization
        async submitExport() {
            if (!this.isLoggedIn) return;

            let url = window.routes.export + '?export=true';
            
            if (this.selectedTickets.length > 0) {
                url += '&selected_ids=' + this.selectedTickets.join(',');
            }
            
            Swal.fire({
                title: 'Exporting...',
                text: 'Sedang menyiapkan file excel',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + this.apiToken, // Header Token
                        'Accept': 'application/json' 
                    }
                });

                if (response.status === 401) {
                     Swal.fire('Error', 'Sesi habis, silakan login ulang', 'error');
                     return;
                }

                // Ambil sebagai Blob (File)
                const blob = await response.blob();
                
                // Buat link download palsu
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = 'Data-WorkOrder.xlsx'; // Sesuaikan nama file/ekstensi
                document.body.appendChild(a);
                a.click();
                a.remove();
                
                Swal.close();

            } catch (error) {
                console.error('Export gagal:', error);
                Swal.fire('Error', 'Gagal melakukan export', 'error');
            }
        },

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