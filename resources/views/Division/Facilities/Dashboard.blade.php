@section('browser_title', 'Facilities Dashboard')

<x-app-layout>
    <x-slot name="header">
        <x-dashboard.toolbar-btn />
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])

    {{-- COnfiguration Data --}}
    <script>
        window.dashboardConfig = {
            chartCatLabels: @json($chartCatLabels ?? []),
            chartCatValues: @json($chartCatValues ?? []),
            chartStatusLabels: @json($chartStatusLabels ?? []),
            chartStatusValues: @json($chartStatusValues ?? []),
            chartPlantLabels: @json($chartPlantLabels ?? []),
            chartPlantValues: @json($chartPlantValues ?? []),
            chartTechLabels: @json($chartTechLabels ?? []),
            chartTechValues: @json($chartTechValues ?? [])
        };
    </script>

    <div class="py-10 bg-[#F8FAFC] min-h-screen">
        <div id="dashboard-content" class="max-w-[95rem] mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. VIBRANT GRADIENT CARDS --}}
            <x-dashboard.stats-card :countTotal="$countTotal" :countPending="$countPending" :countProgress="$countProgress" :countDone="$countDone"
                :selectedMonth="$selectedMonth" :completionPct="$completionPct" />

            {{-- 2. CHARTS AREA --}}
            <x-dashboard.charts-area />

            {{-- 3. GANTT CHART --}}
            <x-dashboard.gantt-chart :groupedGantt="$groupedGantt" :ganttStartDate="$ganttStartDate" :ganttTotalDays="$ganttTotalDays" />

        </div>
    </div>

</x-app-layout>
