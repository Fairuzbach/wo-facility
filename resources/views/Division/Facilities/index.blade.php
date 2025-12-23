@section('browser_title', 'Facilities Work Order')

{{-- 1. LOGIKA TOKEN (Hanya untuk Admin/SPV yg Login) --}}
@php
    $apiToken = '';
    // Token hanya digenerate jika user login (untuk keperluan Edit/Update/Approval)
    if (Auth::check()) {
        $apiToken = Auth::user()->createToken('web-access')->plainTextToken;
    }
@endphp

<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex justify-between items-center py-2">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#1E3A5F] to-[#2d5285] flex items-center justify-center text-white shadow-lg shadow-blue-900/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-2xl text-slate-800 tracking-tight">WO Facilities</h2>
                    <p class="text-xs text-slate-500 font-medium">Manage maintenance & repair tickets</p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- LIBRARIES --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- 2. KONFIGURASI JAVASCRIPT GLOBAL --}}
    <script>
        window.facilitiesConfig = {
            // Status Login
            isLoggedIn: {{ Auth::check() ? 'true' : 'false' }},

            // Token (Kosong jika tamu)
            apiToken: "{{ $apiToken }}",

            // URL PENTING:
            // 1. createUrl: Mengarah ke Public Route di web.php
            createUrl: "{{ route('fh.store') }}",

            // 2. apiUrl: Mengarah ke API Admin (jika diperlukan untuk edit/update)
            apiUrl: "{{ url('/api/facility-wo') }}",

            // Data Pendukung
            machines: @json($machines),
            technicians: @json($technicians),
            pageIds: @json($pageIds),
            openTicket: @json($openTicket ?? null)
        };

        window.routes = {
            export: "{{ route('fh.index') }}"
        };
    </script>

    @vite(['resources/css/facilities.css', 'resources/js/facilities.js'])

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- MAIN CONTENT --}}
    <div class="py-8 bg-[#F8FAFC] min-h-screen font-sans" x-data="facilitiesData({
        isLogged: {{ Auth::check() ? 'true' : 'false' }},
        token: '{{ $apiToken }}',
        createUrl: '{{ route('fh.store') }}'
    })">

        {{-- ALERT SUCCESS (SESSION) --}}
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '{{ session('success') }}',
                        confirmButtonColor: '#1E3A5F',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-3xl'
                        }
                    });
                });
            </script>
        @endif

        <div class="max-w-[95rem] mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- 1. STATS OVERVIEW --}}
            <x-index.stats-card :countTotal="$countTotal" :countPending="$countPending" :countProgress="$countProgress" :countDone="$countDone" />

            {{-- 2. TOOLBAR --}}
            <x-index.toolbar :plants="$plants" />

            {{-- 3. TABLE DATA  --}}
            <x-index.table-data :workOrders="$workOrders" />
        </div>

        {{-- MODAL CREATE  --}}
        <x-index.modal-create :plants="$plants" :machines="$machines" />

        {{-- MODAL EDIT / UPDATE --}}
        <x-index.modal-edit />

        {{-- MODAL DETAIL (VIEW) --}}
        <x-index.modal-detail />
    </div>
</x-app-layout>
