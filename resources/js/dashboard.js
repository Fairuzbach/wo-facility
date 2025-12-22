document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Chart jika Chart.js tersedia
    if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);

        const config = window.dashboardConfig || {};
        const createChart = (canvasId, type, data, options) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            // Hancurkan chart lama jika ada (Fix error Canvas used)
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            return new Chart(canvas, { type, data, options });
        };
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    grid: {
                        borderDash: [2, 4],
                        color: '#f1f5f9'
                    }
                }
            }
        };

        // 1. Cat Chart
        if (config.chartCatLabels && config.chartCatLabels.length > 0) {
            new Chart(document.getElementById('catChart'), {
                type: 'bar',
                data: {
                    labels: config.chartCatLabels,
                    datasets: [{
                        label: 'Total',
                        data: config.chartCatValues,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y'
                }
            });
        }

        // 2. Status Chart (Doughnut)
        const statusLabels = config.chartStatusLabels || [];
        if (statusLabels.length > 0) {
            const statusColors = {
                'pending': '#fbbf24',
                'in_progress': '#3b82f6',
                'completed': '#10b981',
                'cancelled': '#ef4444'
            };
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: config.chartStatusValues || [],
                        backgroundColor: statusLabels.map(l => statusColors[l] ?? '#cbd5e1'),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold'
                            },
                            formatter: (val, ctx) => {
                                let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return (val * 100 / sum).toFixed(0) + "%";
                            }
                        }
                    }
                }
            });
        }

        // 3. Plant Chart
        if (config.chartPlantLabels && config.chartPlantLabels.length > 0) {
            new Chart(document.getElementById('plantChart'), {
                type: 'bar',
                data: {
                    labels: config.chartPlantLabels,
                    datasets: [{
                        label: 'Reqs',
                        data: config.chartPlantValues,
                        backgroundColor: '#6366f1',
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y'
                }
            });
        }

        // 4. Tech Chart
        if (config.chartTechLabels && config.chartTechLabels.length > 0) {
            new Chart(document.getElementById('techChart'), {
                type: 'bar',
                data: {
                    labels: config.chartTechLabels,
                    datasets: [{
                        label: 'Assigns',
                        data: config.chartTechValues,
                        backgroundColor: '#a855f7',
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y'
                }
            });
        }
    }
});

// Fungsi Export PDF (dibuat global agar bisa dipanggil dari HTML)
window.toggleExportMenu = function() {
    const menu = document.getElementById('exportMenu');
    if (menu) menu.classList.toggle('hidden');
};

document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportMenu');
    if (menu && !e.target.closest('.relative')) menu.classList.add('hidden');
});

window.exportToPDF = async function() {
    try {
        // 1. Sembunyikan menu export agar tidak ikut ter-screenshot
        const menu = document.getElementById('exportMenu');
        if (menu) menu.classList.add('hidden');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Mohon tunggu, sedang memproses seluruh halaman.',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false
            });
        }

        const element = document.getElementById('dashboard-content');
        if (!element) throw new Error('Dashboard content element not found');

        // 2. Opsi html2canvas untuk menangkap FULL HEIGHT
        const canvas = await html2canvas(element, {
            scale: 2, // Tingkatkan kualitas (2 = tajam, 1 = standar)
            backgroundColor: '#F8FAFC',
            useCORS: true, // Penting jika ada gambar dari storage
            scrollY: -window.scrollY, // Fix agar tidak terpotong bagian atas saat di-scroll
            windowHeight: element.scrollHeight, // Paksa canvas setinggi konten asli
            height: element.scrollHeight // Pastikan tinggi canvas sesuai konten
        });
        
        const imgData = canvas.toDataURL('image/png');
        
        // 3. Setup PDF (A4 Landscape)
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4'); // 'l' = Landscape
        
        // Dimensi A4 Landscape
        const pageWidth = 297;
        const pageHeight = 210; 
        
        // Hitung dimensi gambar agar pas dengan lebar PDF
        const imgWidth = pageWidth; 
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        let heightLeft = imgHeight;
        let position = 0;

        // 4. Logika Multi-Page (Cetak Halaman Pertama)
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        // 5. Jika konten lebih panjang dari 1 halaman, tambah halaman baru
        while (heightLeft > 0) {
            position = position - pageHeight; // Geser posisi gambar ke atas
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        
        pdf.save('Facilities_Dashboard_Full.pdf');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Downloaded!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    } catch (e) {
        console.error(e);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', e.message, 'error');
        } else {
            alert('Error: ' + e.message);
        }
    }
};