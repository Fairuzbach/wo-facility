<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Chart: Category --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-500 rounded-full"></span> Request Categories
            </h4>
        </div>
        <div class="h-72"><canvas id="catChart"></canvas></div>
    </div>

    {{-- Chart: Status Distribution --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                <span class="w-2 h-6 bg-amber-500 rounded-full"></span> Status Distribution
            </h4>
        </div>
        <div class="h-72"><canvas id="statusChart"></canvas></div>
    </div>

    {{-- Chart: Plant --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                <span class="w-2 h-6 bg-indigo-500 rounded-full"></span> Requests by Plant
            </h4>
        </div>
        <div class="h-72"><canvas id="plantChart"></canvas></div>
    </div>

    {{-- Chart: Technician --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h4 class="font-bold text-[#1E3A5F] flex items-center gap-2">
                <span class="w-2 h-6 bg-purple-500 rounded-full"></span> Tech Assignments
            </h4>
        </div>
        <div class="h-72"><canvas id="techChart"></canvas></div>
    </div>
</div>
