<div class="flex flex-col md:flex-row justify-between items-center gap-4 -my-2">

    {{-- LEFT SIDE: BACK BUTTON & TITLE --}}
    <div class="flex items-center gap-4 w-full md:w-auto">
        <a href="{{ route('fh.index') }}"
            class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-white hover:bg-[#1E3A5F] hover:border-[#1E3A5F] transition-all duration-300 shadow-sm">
            <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>

        <h2
            class="font-extrabold text-2xl text-[#1E3A5F] leading-tight uppercase tracking-wider flex items-center gap-3">
            <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
            {{ __('Facilities Dashboard') }}
        </h2>
    </div>

    {{-- RIGHT SIDE: FILTER & EXPORT --}}
    <div class="flex items-center gap-3 w-full md:w-auto justify-end">
        <form method="GET" action="{{ route('fh.dashboard') }}" class="flex items-center gap-2">
            <div class="relative group">
                <input type="month" name="month" value="{{ $selectedMonth ?? '' }}"
                    class="rounded-xl border border-slate-200 pl-4 pr-2 py-2 text-sm font-bold text-slate-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition shadow-sm hover:shadow-md cursor-pointer">
            </div>
            <button type="submit"
                class="p-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
            </button>
        </form>

        <div class="relative">
            <button onclick="toggleExportMenu()"
                class="bg-[#1E3A5F] hover:bg-[#152a45] text-white px-5 py-2.5 rounded-xl font-bold text-sm uppercase shadow-lg shadow-blue-900/20 transition-all duration-300 flex items-center gap-2 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </button>
            {{-- Export Menu Dropdown --}}
            <div id="exportMenu"
                class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl z-50 border border-slate-100 overflow-hidden transform transition-all origin-top-right">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-500 uppercase">Download Options</p>
                </div>
                <button onclick="exportToPDF(); toggleExportMenu();"
                    class="w-full text-left px-4 py-3 text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition-colors flex items-center gap-3">
                    <span class="text-lg">📄</span>
                    <span class="text-sm font-bold">Save as PDF</span>
                </button>
            </div>
        </div>
    </div>
</div>
