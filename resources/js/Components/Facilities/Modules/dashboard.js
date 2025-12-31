
export default (initialData) => ({
    // Data dari Controller
    data: initialData || {},

    init() {
        console.log('Facility Dashboard Loaded. Data:', this.data);
        
        // Beri jeda sedikit agar Canvas element di DOM siap
        setTimeout(() => {
            this.renderCharts();
        }, 100);
    },

    renderCharts() {
        // Safety Check: Pastikan Chart.js ada
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js belum dimuat/tidak ditemukan.');
            return;
        }

        const canvasCheck = document.getElementById('catChart');
        if (!canvasCheck) return; 

        // Helper Function untuk membuat chart
        const createChart = (canvasId, type, labels, values, labelName, color) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            // Hapus chart lama jika ada (agar tidak tumpang tindih)
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            return new Chart(canvas, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelName,
                        data: values,
                        backgroundColor: color,
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: type === 'bar' ? 'y' : 'x', // Bar horizontal
                    plugins: { 
                        legend: { display: type === 'doughnut' } 
                    },
                    scales: type === 'doughnut' ? {} : {
                        x: { grid: { display: false } },
                        y: { grid: { borderDash: [2, 4], color: '#f1f5f9' } }
                    }
                }
            });
        };

        // 1. Category Chart
        if (this.data.catLabels?.length) {
            createChart('catChart', 'bar', this.data.catLabels, this.data.catValues, 'Total', '#3B82F6');
        }

        // 2. Status Chart (Custom Color Logic)
        if (this.data.statusLabels?.length) {
            const statusColors = this.data.statusLabels.map(l => {
                if(l==='pending') return '#fbbf24'; // Kuning
                if(l==='in_progress') return '#3b82f6'; // Biru
                if(l==='completed') return '#10b981'; // Hijau
                return '#ef4444'; // Merah/Lainnya
            });

            // Manual creation for specific Doughnut options
            const ctx = document.getElementById('statusChart');
            if(ctx) {
                const existing = Chart.getChart(ctx);
                if (existing) existing.destroy();
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.data.statusLabels,
                        datasets: [{
                            data: this.data.statusValues,
                            backgroundColor: statusColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }
        }

        // 3. Plant Chart
        if (this.data.plantLabels?.length) {
            createChart('plantChart', 'bar', this.data.plantLabels, this.data.plantValues, 'Requests', '#6366f1');
        }

        // 4. Tech Chart
        if (this.data.techLabels?.length) {
            createChart('techChart', 'bar', this.data.techLabels, this.data.techValues, 'Jobs', '#a855f7');
        }
    },

    async exportToPDF() {
        console.log('Export triggered...');
        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ 
                    title: 'Generating PDF...', 
                    text: 'Mohon tunggu sebentar...',
                    didOpen: () => Swal.showLoading() 
                });
            }

            const element = document.getElementById('dashboard-content');
            if (!element) throw new Error('Elemen dashboard tidak ditemukan');
            
            // Gunakan library html2canvas & jsPDF dari window (CDN)
            const canvas = await html2canvas(element, { 
                scale: 2, 
                useCORS: true, 
                scrollY: -window.scrollY 
            });
            
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            
            const pdf = new jsPDF('l', 'mm', 'a4'); // Landscape
            const pageWidth = 297; 
            const imgHeight = (canvas.height * pageWidth) / canvas.width;
            
            // Logic Multipage sederhana (atau single page panjang fit width)
            pdf.addImage(imgData, 'PNG', 0, 0, pageWidth, imgHeight);
            
            pdf.save('Dashboard-Facility.pdf');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire('Berhasil', 'File PDF telah didownload.', 'success');
            }
        } catch (e) {
            console.error(e);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Gagal export PDF: ' + e.message, 'error');
            } else {
                alert('Gagal export PDF.');
            }
        }
    }
});