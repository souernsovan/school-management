<x-student-layout>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Page title --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">My Grades</h2>
            <p class="text-sm text-slate-500 mt-0.5">Your grade for each exam you have taken.</p>
        </div>

        @if(!$student)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <div class="mx-auto w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-slate-700 font-semibold text-sm">No student record found</p>
            <p class="text-slate-400 text-xs mt-1">Your account email does not match any student. Please contact your administrator.</p>
        </div>

        @elseif($results->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-12 h-12 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <p class="text-slate-400 text-sm font-medium">No grades yet</p>
            <p class="text-slate-300 text-xs mt-1">Your exam marks haven't been entered yet.</p>
        </div>

        @else

        @php
            $typeColors = \App\Models\ExamType::tailwindMap();

            $gradeStyle = fn(float $pct): array => match(true) {
                $pct >= 95 => ['label' => 'A+', 'bg' => 'bg-emerald-500', 'text' => 'text-white', 'bar' => 'bg-emerald-500'],
                $pct >= 90 => ['label' => 'A',  'bg' => 'bg-emerald-400', 'text' => 'text-white', 'bar' => 'bg-emerald-400'],
                $pct >= 85 => ['label' => 'B+', 'bg' => 'bg-blue-500',    'text' => 'text-white', 'bar' => 'bg-blue-500'],
                $pct >= 80 => ['label' => 'B',  'bg' => 'bg-blue-400',    'text' => 'text-white', 'bar' => 'bg-blue-400'],
                $pct >= 70 => ['label' => 'C',  'bg' => 'bg-teal-500',    'text' => 'text-white', 'bar' => 'bg-teal-500'],
                $pct >= 60 => ['label' => 'D',  'bg' => 'bg-yellow-400',  'text' => 'text-white', 'bar' => 'bg-yellow-400'],
                $pct >= 50 => ['label' => 'E',  'bg' => 'bg-orange-400',  'text' => 'text-white', 'bar' => 'bg-orange-400'],
                default    => ['label' => 'F',  'bg' => 'bg-red-500',     'text' => 'text-white', 'bar' => 'bg-red-500'],
            };
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($results as $result)
            @php
                $examTotal = $result->exam?->total_marks ?? 0;
                $pct       = $examTotal > 0 ? ($result->marks_obtained / $examTotal) * 100 : 0;
                $gs        = $gradeStyle($pct);
                $type      = $result->exam?->type ?? 'Other';
                $tc        = $typeColors[$type] ?? ['ring' => 'ring-slate-200', 'badge_bg' => 'bg-slate-100', 'badge_text' => 'text-slate-600'];
            @endphp

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden ring-1 {{ $tc['ring'] }} hover:shadow-md transition-shadow">

                {{-- Card header: subject + type badge --}}
                <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 text-base leading-tight truncate">
                            {{ $result->exam?->subject?->name ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $result->exam?->exam_date?->format('d M Y') ?? '' }}
                        </p>
                    </div>
                    <span class="shrink-0 inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $tc['badge_bg'] }} {{ $tc['badge_text'] }}">
                        {{ $type }}
                    </span>
                </div>

                {{-- Grade circle + marks --}}
                <div class="px-5 py-3 flex items-center gap-5">
                    {{-- Big grade letter --}}
                    <div class="w-16 h-16 rounded-2xl {{ $gs['bg'] }} flex items-center justify-center shrink-0 shadow-sm">
                        <span class="text-2xl font-black {{ $gs['text'] }}">{{ $gs['label'] }}</span>
                    </div>
                    {{-- Marks detail --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-slate-800">{{ number_format($result->marks_obtained, 0) }}</span>
                            <span class="text-slate-400 text-sm font-medium">/ {{ $examTotal }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ number_format($pct, 1) }}%</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="px-5 pb-5">
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $gs['bar'] }} transition-all"
                             style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        @endif
    </div>
</x-student-layout>
