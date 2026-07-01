<x-app-layout>

    <x-slot name="header">Dashboard</x-slot>

    <div class="p-4 sm:p-6 space-y-6">

        {{-- ===== STAT CARDS ===== --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

            @php
            $cards = [
                ['label'=>'Students', 'value'=>$stats['students'], 'icon'=>'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'bg'=>'bg-blue-500',    'route'=>'students.index',       'perm'=>['view students','manage students','create students']],
                ['label'=>'Teachers', 'value'=>$stats['teachers'], 'icon'=>'M17 20h5v-2a4 4 0 00-5.916-3.519M9 20H4v-2a4 4 0 015.916-3.519M15 7a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z',  'bg'=>'bg-teal-500',    'route'=>'teachers.index',       'perm'=>['view teachers','manage teachers']],
                ['label'=>'Classes',  'value'=>$stats['classes'],  'icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',              'bg'=>'bg-indigo-500',  'route'=>'school-classes.index', 'perm'=>['view classes','manage classes','create classes']],
                ['label'=>'Subjects', 'value'=>$stats['subjects'], 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'bg'=>'bg-emerald-500', 'route'=>'subjects.index',       'perm'=>['view subjects','manage subjects','create subjects']],
                ['label'=>'Exams',    'value'=>$stats['exams'],    'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                                                 'bg'=>'bg-purple-500',  'route'=>'exams.index',          'perm'=>['view results','manage exams']],
            ];
            @endphp

            @foreach($cards as $card)
            @canany($card['perm'])
            <a href="{{ route($card['route']) }}"
               class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition group">
                <div class="w-11 h-11 {{ $card['bg'] }} rounded-xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-slate-800 leading-tight">{{ number_format($card['value']) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $card['label'] }}</p>
                </div>
            </a>
            @endcanany
            @endforeach

        </div>

        {{-- ===== ROW 2: ATTENDANCE CHART + TODAY'S ATTENDANCE ===== --}}
        @canany(['manage attendance'])
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Weekly attendance stacked bar chart --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800">Attendance This Week</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Last 7 days by status</p>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
                </div>
                <div style="position:relative; height:200px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            {{-- Today's Attendance Summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800">Today</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('d M Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                        {{ $todayAtt['total'] }} records
                    </span>
                </div>

                @if($todayAtt['total'] === 0)
                    <div class="flex-1 flex flex-col items-center justify-center py-4 text-slate-400 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm font-medium">No records yet</p>
                        <a href="{{ route('attendances.create') }}" class="text-xs text-blue-600 hover:underline">Take attendance →</a>
                    </div>
                @else
                    <div class="flex justify-center mb-4">
                        <canvas id="todayDonut" width="150" height="150"></canvas>
                    </div>
                    <div class="space-y-2.5">
                        @foreach([
                            ['Present',    $todayAtt['present'],    'bg-green-500',  'bg-green-50 text-green-700'],
                            ['Absent',     $todayAtt['absent'],     'bg-red-500',    'bg-red-50 text-red-700'],
                            ['Late',       $todayAtt['late'],       'bg-amber-500',  'bg-amber-50 text-amber-700'],
                            ['Permission', $todayAtt['permission'], 'bg-blue-500',   'bg-blue-50 text-blue-700'],
                        ] as [$lbl, $cnt, $dot, $badge])
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $dot }} shrink-0"></span>
                                <span class="text-sm text-slate-600">{{ $lbl }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400">{{ $todayAtt['total'] > 0 ? round($cnt / $todayAtt['total'] * 100) : 0 }}%</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg {{ $badge }} text-xs font-semibold">{{ $cnt }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
        @endcanany

        {{-- ===== ROW 3: CLASS DISTRIBUTION + EXAM OVERVIEW ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Students by Class --}}
            @canany(['view classes', 'manage classes', 'create classes'])
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800">Students by Class</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Enrollment breakdown</p>
                    </div>
                    <a href="{{ route('school-classes.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
                </div>

                @if($classDist->isEmpty())
                    <p class="text-sm text-slate-400 py-6 text-center">No classes yet</p>
                @else
                @php $maxStudents = $classDist->max('students_count') ?: 1; @endphp
                <div class="space-y-3.5">
                    @foreach($classDist as $cls)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-slate-700">
                                {{ $cls->name }}
                                @if($cls->section)<span class="text-xs text-slate-400 font-normal">— {{ $cls->section }}</span>@endif
                            </span>
                            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $cls->students_count }}</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500"
                                 style="width: {{ round($cls->students_count / $maxStudents * 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endcanany

            {{-- Exam Overview --}}
            @canany(['manage exams', 'view results'])
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800">Exam Overview</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Upcoming exams and recent activity</p>
                    </div>
                    <a href="{{ route('exams.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
                </div>

                {{-- Donut chart + legend --}}
                <div class="flex items-center gap-4 mb-4">
                    <div class="relative shrink-0">
                        <canvas id="examDonut" width="110" height="110"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-lg font-bold text-slate-800">{{ $examStats['upcoming'] + $examStats['thisWeek'] + $examStats['past'] }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">Total</span>
                        </div>
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                                <span class="text-xs text-slate-500">Upcoming</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ $examStats['upcoming'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="text-xs text-slate-500">This week</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ $examStats['thisWeek'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300 shrink-0"></span>
                                <span class="text-xs text-slate-500">Past</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ $examStats['past'] }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $typeColors = array_map(fn($c) => $c['single'], \App\Models\ExamType::tailwindMap());
                    $examFeed = $upcomingExams->isNotEmpty() ? $upcomingExams : $recentExams;
                @endphp

                @if($examFeed->isEmpty())
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 py-10 text-center">
                        <p class="text-sm text-slate-400">No exam records yet</p>
                        <a href="{{ route('exams.create') }}" class="mt-2 inline-flex text-xs font-medium text-blue-600 hover:underline">Create an exam →</a>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($examFeed as $exam)
                        <a href="{{ route('exams.show', $exam) }}"
                           class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition">
                            <div class="w-9 h-9 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center shrink-0 font-bold text-sm">
                                {{ strtoupper(substr($exam->subject->name ?? 'E', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $exam->subject->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400 truncate">
                                    {{ $exam->schoolClass->name ?? '—' }} · {{ $exam->exam_date->format('d M Y') }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColors[$exam->type] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $exam->type }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ $upcomingExams->contains($exam) ? 'Upcoming' : $exam->results_count . ' results' }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
            @endcanany

        </div>

        {{-- ===== ROW 4: RECENTLY ENROLLED STUDENTS ===== --}}
        @canany(['view students', 'manage students', 'create students'])
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800">Recently Enrolled</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Latest 5 students</p>
                </div>
                <a href="{{ route('students.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
            </div>

            @if($recentStudents->isEmpty())
                <p class="text-sm text-slate-400 py-4 text-center">No students yet</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wide border-b border-slate-100">
                            <th class="pb-3 pr-6">Student</th>
                            <th class="pb-3 pr-6">Class</th>
                            <th class="pb-3 pr-6 hidden sm:table-cell">Email</th>
                            <th class="pb-3">Enrolled</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($recentStudents as $s)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 pr-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($s->first_name, 0, 1)) }}
                                    </div>
                                    <a href="{{ route('students.show', $s) }}" class="font-medium text-slate-800 hover:text-blue-600 transition whitespace-nowrap">
                                        {{ $s->first_name }} {{ $s->last_name }}
                                    </a>
                                </div>
                            </td>
                            <td class="py-3 pr-6 text-slate-500 whitespace-nowrap">
                                {{ $s->schoolClass ? $s->schoolClass->name.' — '.$s->schoolClass->section : '—' }}
                            </td>
                            <td class="py-3 pr-6 text-slate-400 hidden sm:table-cell">{{ $s->email ?? '—' }}</td>
                            <td class="py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($s->created_at)->format('d M Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endcanany

        {{-- ===== ANNOUNCEMENTS ===== --}}
        @if($announcements->isNotEmpty())
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Announcements</h3>
                @can('manage students')
                <a href="{{ route('announcements.index') }}" class="text-xs text-blue-600 hover:underline font-medium">Manage →</a>
                @endcan
            </div>
            @foreach($announcements as $ann)
            @php $c = $ann->type_color; @endphp
            <div class="bg-white rounded-2xl border border-{{ $c }}-200 shadow-sm px-4 py-3.5 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-{{ $c }}-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-{{ $c }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ann->type_icon }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($ann->pinned)<span class="text-[10px] font-bold uppercase tracking-widest text-amber-600">📌 Pinned</span>@endif
                        <p class="text-sm font-semibold text-slate-800">{{ $ann->title }}</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $ann->body }}</p>
                    <p class="text-[10px] text-slate-400 mt-1">{{ $ann->author->name ?? '' }} · {{ $ann->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        Chart.defaults.font.family = "'Figtree', sans-serif";
        Chart.defaults.color = '#94a3b8';

        // Weekly attendance stacked bar
        var weekEl = document.getElementById('attendanceChart');
        if (weekEl) {
            var wd = @json($week);
            new Chart(weekEl, {
                type: 'bar',
                data: {
                    labels: wd.map(function(d){ return d.label; }),
                    datasets: [
                        { label:'Present',    data: wd.map(function(d){ return d.present; }),    backgroundColor:'#22c55e', borderRadius:4, borderSkipped:false },
                        { label:'Late',       data: wd.map(function(d){ return d.late; }),       backgroundColor:'#f59e0b', borderRadius:4, borderSkipped:false },
                        { label:'Permission', data: wd.map(function(d){ return d.permission; }), backgroundColor:'#3b82f6', borderRadius:4, borderSkipped:false },
                        { label:'Absent',     data: wd.map(function(d){ return d.absent; }),     backgroundColor:'#ef4444', borderRadius:4, borderSkipped:false },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position:'top', labels:{ boxWidth:10, padding:14, font:{ size:11 } } }
                    },
                    scales: {
                        x: { stacked:true, grid:{ display:false }, ticks:{ font:{ size:11 } } },
                        y: { stacked:true, beginAtZero:true, grid:{ color:'#f1f5f9' }, ticks:{ precision:0, font:{ size:11 } } }
                    }
                }
            });
        }

        // Exam donut
        var examDonutEl = document.getElementById('examDonut');
        if (examDonutEl) {
            var es = @json($examStats);
            new Chart(examDonutEl, {
                type: 'doughnut',
                data: {
                    labels: ['Upcoming', 'This Week', 'Past'],
                    datasets: [{
                        data: [es.upcoming, es.thisWeek, es.past],
                        backgroundColor: ['#3b82f6', '#22c55e', '#cbd5e1'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(ctx){ return ' ' + ctx.label + ': ' + ctx.parsed; } } }
                    }
                }
            });
        }

        // Today donut
        var donutEl = document.getElementById('todayDonut');
        if (donutEl) {
            var td = @json($todayAtt);
            new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    labels: ['Present','Absent','Late','Permission'],
                    datasets:[{
                        data: [td.present, td.absent, td.late, td.permission],
                        backgroundColor: ['#22c55e','#ef4444','#f59e0b','#3b82f6'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display:false },
                        tooltip: { callbacks:{ label:function(ctx){ return ' '+ctx.label+': '+ctx.parsed; } } }
                    }
                }
            });
        }
    })();
    </script>

</x-app-layout>
