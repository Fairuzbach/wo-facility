@props(['title' => config('app.name', 'Laravel')])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('browser_title', config('app.name', 'Laravel'))</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js (Pastikan terload jika belum ada di app.js) --}}
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 relative">

        {{-- ========================================================= --}}
        {{-- TAMBAHAN: TOMBOL LOGOUT & PROFIL DI POJOK KANAN ATAS --}}
        {{-- ========================================================= --}}
        @auth
            <div class="absolute top-4 right-4 z-50 flex items-center gap-3">
                <div class="relative ml-3" x-data="{ openNotif: false }">

                    <button @click="openNotif = !openNotif"
                        class="relative p-2 rounded-full text-slate-500 hover:text-[#1E3A5F] hover:bg-slate-100 transition-all duration-200 focus:outline-none">

                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>

                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span
                                    class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
                            </span>
                        @endif
                    </button>

                    <div x-show="openNotif" @click.away="openNotif = false"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl overflow-hidden z-50 border border-slate-100 origin-top-right"
                        style="display: none;">

                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-700">Notifikasi</h3>
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <a href="{{ route('notifications.readAll') }}"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Tandai semua dibaca
                                </a>
                            @endif
                        </div>

                        <div class="max-h-80 overflow-y-auto custom-scrollbar">
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                    class="block px-4 py-3 hover:bg-blue-50 transition-colors border-b border-slate-50 last:border-0 relative group">

                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="w-full">
                                            <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">
                                                {{ $notification->data['message'] ?? 'Notifikasi Baru' }}
                                            </p>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-xs text-slate-500">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-bold text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded">
                                                    {{ $notification->data['ticket_num'] ?? 'WO' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="absolute top-4 right-2 w-2 h-2 bg-blue-500 rounded-full"></div>
                                </a>
                            @empty
                                <div class="py-8 text-center">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    <p class="text-sm text-slate-500 font-medium">Tidak ada notifikasi baru</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="bg-slate-50 px-4 py-2 border-t border-slate-100 text-center">
                            <a href="#" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">Lihat
                                Semua Riwayat</a>
                        </div>
                    </div>
                </div>
                {{-- Nama User (Hidden di Mobile) --}}
                <div class="hidden sm:block text-right">
                    <p class="text-xs text-gray-500">Login sebagai</p>
                    <p class="text-sm font-bold text-gray-700">{{ Auth::user()->name }}</p>
                </div>

                {{-- Tombol Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="group flex items-center justify-center w-10 h-10 bg-white border border-gray-200 rounded-full shadow-sm hover:bg-red-50 hover:border-red-200 hover:text-red-600 text-gray-500 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        title="Keluar / Logout">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 group-hover:scale-110 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </form>
            </div>
        @endauth
        {{-- ========================================================= --}}


        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 pr-20"> {{-- Tambah padding kanan agar tidak tertutup tombol logout --}}
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
