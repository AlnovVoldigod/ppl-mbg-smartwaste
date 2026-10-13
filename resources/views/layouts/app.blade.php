<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - MBG SmartWaste' : 'MBG SmartWaste - Sistem Monitoring & Pengurangan Sisa Pangan' }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Assets (Tailwind CSS v4 & Alpine.js / Chart.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body 
    x-data="{ 
        sidebarOpen: true, 
        currentRole: '{{ $role ?? request('role', 'admin') }}', 
        showNotification: false,
        userDropdownOpen: false,
        datePickerOpen: false,
        startDate: '{{ request('start_date', now()->subDays(6)->format('Y-m-d')) }}',
        endDate: '{{ request('end_date', now()->format('Y-m-d')) }}',
        switchRole(role) {
            this.currentRole = role;
            const url = new URL(window.location.href);
            url.searchParams.set('role', role);
            window.location.href = url.toString();
        },
        updateDateFilter() {
            const url = new URL(window.location.href);
            url.searchParams.set('start_date', this.startDate);
            url.searchParams.set('end_date', this.endDate);
            window.location.href = url.toString();
        }
    }" 
    class="h-full font-sans antialiased bg-slate-50 text-slate-800"
>
    <div class="min-h-screen flex">
        {{-- Mobile Sidebar Backdrop --}}
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-xs lg:hidden"
            style="display: none;"
        ></div>

        {{-- SIDEBAR UTAMA (Left, Fixed, w-64) --}}
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-[#064E3B] via-[#065F46] to-[#022c22] text-white flex flex-col transition-transform duration-300 ease-in-out border-r border-emerald-800/40 shadow-xl"
        >
            {{-- Top Section: Logo & Unit Name --}}
            <div class="px-5 py-4 border-b border-emerald-800/60 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center shrink-0 shadow-inner">
                        {{-- Leaf Icon Inline SVG --}}
                        <svg class="w-6 h-6 text-emerald-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 3.824 2.144 7.155 5.292 8.853A9.972 9.972 0 017 18c0-5.523 4.477-10 10-10 .708 0 1.398.074 2.064.214C18.17 5.568 15.358 2 12 2zm7.934 8.79A10.027 10.027 0 0122 12c0 5.523-4.477 10-10 10-2.31 0-4.436-.786-6.136-2.107C7.432 20.67 9.61 21 12 21c4.97 0 9-4.03 9-9 0-.41-.027-.813-.066-1.21z"/>
                            <path d="M17 8c-4.418 0-8 3.582-8 8 0 1.08.214 2.112.605 3.053A7.962 7.962 0 0012 20c4.418 0 8-3.582 8-8 0-.416-.032-.824-.093-1.223A7.957 7.957 0 0017 8z" opacity="0.3"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <h1 class="text-base font-extrabold tracking-tight text-white flex items-center gap-1.5">
                                MBG <span class="text-emerald-300 font-semibold">SmartWaste</span>
                            </h1>
                            {{-- Tombol Tutup Sidebar di Layar Kecil --}}
                            <button 
                                type="button" 
                                @click="sidebarOpen = false" 
                                class="lg:hidden text-emerald-200 hover:text-white p-1 rounded-md"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-[11px] font-medium text-emerald-200/90 truncate mt-0.5">
                            {{ $unitName ?? 'Monitoring Pusat' }}
                        </p>
                    </div>
                </div>

                {{-- Status Role Terpilih --}}
                <div class="mt-3 px-2.5 py-1.5 rounded-lg bg-emerald-950/60 border border-emerald-800/70 flex items-center justify-between text-xs">
                    <span class="text-emerald-300 text-[11px]">Mode Akses:</span>
                    <span class="font-bold text-white uppercase tracking-wider text-[11px] px-2 py-0.5 rounded bg-emerald-700/80" x-text="currentRole">
                        {{ strtoupper($role ?? 'admin') }}
                    </span>
                </div>
            </div>

            {{-- Navigasi Menu Berbasis Peran --}}
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto scrollbar-thin">

                {{-- 1. ADMIN MENU (currentRole === 'admin') --}}
                <div x-show="currentRole === 'admin'" x-transition:enter="transition ease-out duration-200" class="space-y-1">
                    <div class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-300/80">
                        Menu Administrator
                    </div>

                    {{-- Dashboard Ringkasan --}}
                    <a 
                        href="{{ url('/dashboard?role=admin') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ (request()->is('dashboard*') && (request('role', 'admin') === 'admin')) ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon chart-bar --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span>Dashboard Ringkasan</span>
                    </a>

                    {{-- Monitoring Sisa Pangan --}}
                    <a 
                        href="{{ url('/admin/monitoring-sisa-pangan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/monitoring-sisa-pangan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon exclamation-triangle --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-amber-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <span>Monitoring Sisa Pangan</span>
                    </a>

                    {{-- Monitoring Kemasan --}}
                    <a 
                        href="{{ url('/admin/monitoring-kemasan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/monitoring-kemasan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon archive-box --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <span>Monitoring Kemasan</span>
                    </a>

                    {{-- Verifikasi Laporan --}}
                    <a 
                        href="{{ url('/admin/verifikasi') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/verifikasi*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon clipboard-check --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Verifikasi Laporan</span>
                    </a>

                    {{-- Data Master Dropdown --}}
                    <div x-data="{ masterOpen: {{ request()->is('admin/master*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button 
                            type="button" 
                            @click="masterOpen = !masterOpen" 
                            class="w-full group flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/master*') ? 'bg-emerald-800/40 text-white font-semibold' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                        >
                            <div class="flex items-center">
                                {{-- Icon database --}}
                                <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                </svg>
                                <span>Data Master</span>
                            </div>
                            <svg 
                                class="w-4 h-4 transition-transform duration-200" 
                                :class="masterOpen ? 'rotate-180 text-emerald-200' : 'text-emerald-300/70'" 
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="masterOpen" x-collapse class="pl-7 pr-2 py-1 space-y-1">
                            <a 
                                href="{{ url('/admin/master/sppg') }}" 
                                class="flex items-center px-3 py-2 text-xs rounded-lg transition-colors {{ request()->is('admin/master/sppg*') ? 'bg-emerald-800/80 text-white font-semibold' : 'text-emerald-200/90 hover:bg-emerald-800/30 hover:text-white' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2.5"></span>
                                <span>SPPG</span>
                            </a>
                            <a 
                                href="{{ url('/admin/master/sekolah') }}" 
                                class="flex items-center px-3 py-2 text-xs rounded-lg transition-colors {{ request()->is('admin/master/sekolah*') ? 'bg-emerald-800/80 text-white font-semibold' : 'text-emerald-200/90 hover:bg-emerald-800/30 hover:text-white' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2.5"></span>
                                <span>Sekolah</span>
                            </a>
                            <a 
                                href="{{ url('/admin/master/menu') }}" 
                                class="flex items-center px-3 py-2 text-xs rounded-lg transition-colors {{ request()->is('admin/master/menu*') ? 'bg-emerald-800/80 text-white font-semibold' : 'text-emerald-200/90 hover:bg-emerald-800/30 hover:text-white' }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2.5"></span>
                                <span>Menu Pangan</span>
                            </a>
                        </div>
                    </div>

                    {{-- Laporan Eksekutif --}}
                    <a 
                        href="{{ url('/admin/laporan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/laporan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon document-chart-bar --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Laporan Eksekutif</span>
                    </a>
                </div>

                {{-- 2. SPPG MENU (currentRole === 'sppg') --}}
                <div x-show="currentRole === 'sppg'" x-transition:enter="transition ease-out duration-200" class="space-y-1">
                    <div class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-300/80">
                        Menu SPPG (Dapur Produksi)
                    </div>

                    {{-- Dashboard --}}
                    <a 
                        href="{{ url('/dashboard?role=sppg') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ (request()->is('dashboard*') && request('role') === 'sppg') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon home --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard SPPG</span>
                    </a>

                    {{-- Input Produksi Porsi --}}
                    <a 
                        href="{{ url('/sppg/produksi') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sppg/produksi*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon fire --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-amber-400 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 3.75 3.75 0 001.43 3.921z" />
                        </svg>
                        <span>Input Produksi Porsi</span>
                    </a>

                    {{-- Catatan Distribusi --}}
                    <a 
                        href="{{ url('/sppg/distribusi') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sppg/distribusi*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon truck --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.808H16.5M7.5 7.5h6.75M7.5 10.5h6.75m-6.75 3h6.75m-6.75 3h6.75" />
                        </svg>
                        <span>Catatan Distribusi</span>
                    </a>

                    {{-- Pengajuan Limbah Dapur --}}
                    <a 
                        href="{{ url('/sppg/limbah-dapur') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sppg/limbah-dapur*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon trash --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-rose-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        <span>Pengajuan Limbah Dapur</span>
                    </a>

                    {{-- Log Riwayat --}}
                    <a 
                        href="{{ url('/sppg/riwayat') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sppg/riwayat*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon clock --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Log Riwayat</span>
                    </a>
                </div>

                {{-- 3. SEKOLAH MENU (currentRole === 'sekolah') --}}
                <div x-show="currentRole === 'sekolah'" x-transition:enter="transition ease-out duration-200" class="space-y-1">
                    <div class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-300/80">
                        Menu Satuan Pendidikan
                    </div>

                    {{-- Dashboard --}}
                    <a 
                        href="{{ url('/dashboard?role=sekolah') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ (request()->is('dashboard*') && request('role') === 'sekolah') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon home --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard Sekolah</span>
                    </a>

                    {{-- Konfirmasi Penerimaan --}}
                    <a 
                        href="{{ url('/sekolah/penerimaan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sekolah/penerimaan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon inbox --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.375v4.5A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25v-4.5m-19.5 0l2.625-7.875A2.25 2.25 0 016.99 4.5h10.02a2.25 2.25 0 012.115 1.625L21.75 13.875" />
                        </svg>
                        <span>Konfirmasi Penerimaan</span>
                    </a>

                    {{-- Pencatatan Sisa Makanan --}}
                    <a 
                        href="{{ url('/sekolah/sisa-pangan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sekolah/sisa-pangan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon scale --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-amber-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.541 1.258l-3.329 1.761a2.25 2.25 0 01-2.138 0l-3.329-1.761a1.125 1.125 0 01-.541-1.258L6.75 4.97m-3 .52c-.99-.203-1.99-.377-3-.52m3 .52L1.13 15.696c-.122.499.106 1.028.541 1.258l3.329 1.761c.66.35 1.478.35 2.138 0l3.329-1.761c.435-.23.663-.759.541-1.258L8.25 5.49" />
                        </svg>
                        <span>Pencatatan Sisa Makanan</span>
                    </a>

                    {{-- Pencatatan Sampah Kemasan --}}
                    <a 
                        href="{{ url('/sekolah/sampah-kemasan') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sekolah/sampah-kemasan*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon cube --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                        <span>Pencatatan Sampah Kemasan</span>
                    </a>

                    {{-- Riwayat Laporan --}}
                    <a 
                        href="{{ url('/sekolah/riwayat') }}" 
                        class="group flex items-center px-3 py-2.5 text-xs font-medium rounded-xl transition-all duration-150 {{ request()->is('sekolah/riwayat*') ? 'bg-emerald-800/60 text-white font-semibold shadow-inner border border-emerald-700/40' : 'text-emerald-100/90 hover:bg-emerald-800/30 hover:text-white' }}"
                    >
                        {{-- Icon document-text --}}
                        <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-300 group-hover:scale-105 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Riwayat Laporan</span>
                    </a>
                </div>

            </nav>

            {{-- Sidebar Footer / Informasi MBG --}}
            <div class="p-4 border-t border-emerald-800/60 bg-emerald-950/40 shrink-0 text-xs">
                <div class="flex items-center justify-between text-emerald-300/80 mb-1.5">
                    <span class="text-[11px] font-medium">BGN & Kemendikdasmen</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-[10px] text-emerald-200/60 leading-tight">
                    Program Makan Bergizi Gratis (MBG) Nasional • Nol Sisa Makanan
                </div>
            </div>
        </aside>

        {{-- AREA UTAMA --}}
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

            {{-- TOP NAVBAR (Header, Right of Sidebar, Fixed) --}}
            <header class="fixed top-0 right-0 left-0 lg:left-64 h-16 z-30 bg-white border-b border-slate-200 shadow-2xs px-4 sm:px-6 flex items-center justify-between transition-all duration-300">
                
                {{-- Sisi Kiri: Hamburger Toggle & Page Title / Breadcrumbs --}}
                <div class="flex items-center gap-3 min-w-0">
                    <button 
                        type="button" 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="p-2 -ml-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-none lg:hidden"
                        aria-label="Buka navigasi"
                    >
                        <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="min-w-0">
                        {{-- Breadcrumb Ringkas --}}
                        <div class="hidden sm:flex items-center gap-1.5 text-[11px] font-medium text-slate-400">
                            <a href="{{ url('/dashboard') }}" class="hover:text-emerald-600 transition-colors">MBG SmartWaste</a>
                            <span>/</span>
                            <span class="text-slate-600 truncate">{{ $breadcrumb ?? ($pageTitle ?? 'Monitoring') }}</span>
                        </div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight truncate leading-tight">
                            {{ $pageTitle ?? 'Dashboard' }}
                        </h2>
                    </div>
                </div>

                {{-- Sisi Tengah & Kanan: Filter Tanggal, Role Switcher, Notifikasi, User Avatar --}}
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">

                    {{-- 1. Date Range Picker (Alpine.js Component) --}}
                    <div class="relative hidden xl:block" x-data="{ open: false }">
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs hover:border-slate-300 transition-colors">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                            </svg>
                            <span class="text-[11px] font-bold text-slate-500 uppercase mr-1">Periode:</span>
                            <input 
                                type="date" 
                                x-model="startDate" 
                                @change="updateDateFilter()" 
                                class="border-0 bg-transparent text-xs text-slate-700 font-semibold p-0 focus:ring-0 focus:outline-none w-28 cursor-pointer"
                            />
                            <span class="text-slate-400 text-xs">-</span>
                            <input 
                                type="date" 
                                x-model="endDate" 
                                @change="updateDateFilter()" 
                                class="border-0 bg-transparent text-xs text-slate-700 font-semibold p-0 focus:ring-0 focus:outline-none w-28 cursor-pointer"
                            />
                        </div>
                    </div>

                    {{-- 2. Role Switcher Pill Buttons --}}
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold shadow-2xs">
                        <button 
                            type="button" 
                            @click="switchRole('admin')" 
                            :class="currentRole === 'admin' ? 'bg-emerald-700 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg transition-all duration-150"
                        >
                            Admin
                        </button>
                        <button 
                            type="button" 
                            @click="switchRole('sppg')" 
                            :class="currentRole === 'sppg' ? 'bg-emerald-700 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg transition-all duration-150"
                        >
                            SPPG
                        </button>
                        <button 
                            type="button" 
                            @click="switchRole('sekolah')" 
                            :class="currentRole === 'sekolah' ? 'bg-emerald-700 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg transition-all duration-150"
                        >
                            Sekolah
                        </button>
                    </div>

                    {{-- 3. Notification Bell Dropdown --}}
                    <div class="relative" @click.outside="showNotification = false">
                        <button 
                            type="button" 
                            @click="showNotification = !showNotification" 
                            class="relative p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            aria-label="Notifikasi sistem"
                        >
                            {{-- Bell Icon --}}
                            <svg class="w-5 h-5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            {{-- Red dot badge --}}
                            <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-white"></span>
                            </span>
                        </button>

                        {{-- Panel Dropdown Notifikasi --}}
                        <div 
                            x-show="showNotification" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            style="display: none;" 
                            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white border border-slate-200 shadow-2xl z-50 overflow-hidden"
                        >
                            <div class="p-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900">Pemberitahuan Sistem</h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">3 Baru</span>
                                </div>
                                <button type="button" @click="showNotification = false" class="text-xs text-emerald-700 hover:underline font-semibold">
                                    Tandai Dibaca
                                </button>
                            </div>

                            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto scrollbar-thin text-xs">
                                {{-- Item 1: Alert Kritis --}}
                                <a href="{{ url('/admin/monitoring-sisa-pangan') }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition-colors block">
                                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-slate-800 flex items-center justify-between">
                                            <span>Sisa Pangan Melebihi Ambang</span>
                                            <span class="text-[10px] text-slate-400 font-normal">10m lalu</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] mt-0.5 line-clamp-2">
                                            SDN 01 Gondangdia mencatat rasio sisa pangan 12.4% (Status: Kritis). Segera evaluasi porsi.
                                        </p>
                                    </div>
                                </a>

                                {{-- Item 2: Verifikasi SPPG --}}
                                <a href="{{ url('/admin/verifikasi') }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition-colors block">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-slate-800 flex items-center justify-between">
                                            <span>Pengajuan Verifikasi SPPG</span>
                                            <span class="text-[10px] text-slate-400 font-normal">35m lalu</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] mt-0.5 line-clamp-2">
                                            SPPG Melati mengajukan catatan produksi 450 porsi untuk batch makan siang hari ini.
                                        </p>
                                    </div>
                                </a>

                                {{-- Item 3: Pengangkutan Limbah --}}
                                <a href="{{ url('/sppg/limbah-dapur') }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition-colors block">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.808H16.5M7.5 7.5h6.75" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-slate-800 flex items-center justify-between">
                                            <span>Jadwal Angkut Kompos</span>
                                            <span class="text-[10px] text-slate-400 font-normal">1 jam lalu</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] mt-0.5 line-clamp-2">
                                            Mitra pengolah kompos organik dijadwalkan tiba di SPPG Melati pukul 14:00 WIB.
                                        </p>
                                    </div>
                                </a>
                            </div>

                            <div class="p-3 border-t border-slate-100 bg-slate-50 text-center">
                                <a href="{{ url('/dashboard') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                    Lihat Semua Aktivitas &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 4. User Avatar & Dropdown --}}
                    <div class="relative" @click.outside="userDropdownOpen = false">
                        <button 
                            type="button" 
                            @click="userDropdownOpen = !userDropdownOpen" 
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-700 to-emerald-500 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                <span>{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                            </div>
                            <div class="hidden md:block text-left pr-1">
                                <div class="text-xs font-bold text-slate-800 leading-tight">
                                    {{ Auth::user()->name ?? 'Administrator MBG' }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium capitalize" x-text="currentRole">
                                    {{ $role ?? 'admin' }}
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {{-- User Dropdown Panel --}}
                        <div 
                            x-show="userDropdownOpen" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            style="display: none;" 
                            class="absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-slate-200 shadow-2xl z-50 py-2 text-xs"
                        >
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <p class="text-[11px] text-slate-400 font-medium">Masuk sebagai:</p>
                                <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->email ?? 'admin@mbg.kemendikdasmen.go.id' }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 uppercase" x-text="currentRole">
                                    {{ strtoupper($role ?? 'admin') }}
                                </span>
                            </div>

                            <a href="{{ url('/dashboard') }}" class="flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                                <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span>Profil Pengguna</span>
                            </a>

                            <a href="{{ url('/admin/laporan') }}" class="flex items-center px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                                <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                                </svg>
                                <span>Statistik Monitoring</span>
                            </a>

                            <div class="my-1 border-t border-slate-100"></div>

                            <a 
                                href="{{ url('/') }}" 
                                class="flex items-center px-4 py-2.5 text-rose-600 hover:bg-rose-50 transition-colors font-semibold"
                            >
                                <svg class="w-4 h-4 mr-2.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span>Keluar (Logout)</span>
                            </a>
                        </div>
                    </div>

                </div>
            </header>

            {{-- MAIN CONTENT AREA (Offset ml-64 via parent flex-col, pt-16 for navbar offset) --}}
            <main class="flex-1 pt-16 bg-slate-50">
                <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                    {{-- Flash Session Alerts --}}
                    @if(session('success'))
                        <x-alert-banner type="success" :message="session('success')" class="mb-6" />
                    @endif

                    @if(session('error'))
                        <x-alert-banner type="danger" :message="session('error')" class="mb-6" />
                    @endif

                    @if(session('warning'))
                        <x-alert-banner type="warning" :message="session('warning')" class="mb-6" />
                    @endif

                    @if(session('info'))
                        <x-alert-banner type="info" :message="session('info')" class="mb-6" />
                    @endif

                    {{-- Konten Halaman --}}
                    @yield('content')
                </div>
            </main>

            {{-- Footer Sederhana --}}
            <footer class="py-4 px-6 border-t border-slate-200/80 bg-white text-center text-xs text-slate-400">
                <span>MBG SmartWaste &copy; {{ date('Y') }} - Badan Gizi Nasional & Kementerian Pendidikan Dasar dan Menengah RI.</span>
            </footer>
        </div>
    </div>

    {{-- Stack untuk Modal Dialog --}}
    @stack('modals')

    {{-- Stack untuk Script JavaScript & Chart.js --}}
    @stack('scripts')
</body>
</html>
