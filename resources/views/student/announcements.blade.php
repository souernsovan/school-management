<x-student-layout>

    <div class="p-4 sm:p-6 space-y-5">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Announcements</h2>
            <p class="text-sm text-slate-500 mt-0.5">Notices from your school administration</p>
        </div>

        @if($announcements->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <p class="text-sm font-medium">No announcements right now</p>
            <p class="text-xs">Check back later for school notices</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($announcements as $ann)
            @php
                $colors = [
                    'emerald' => ['card' => 'border-emerald-200 bg-emerald-50', 'icon' => 'text-emerald-500 bg-emerald-100', 'title' => 'text-emerald-800', 'badge' => 'bg-emerald-100 text-emerald-700'],
                    'amber'   => ['card' => 'border-amber-200 bg-amber-50',   'icon' => 'text-amber-500 bg-amber-100',   'title' => 'text-amber-800',   'badge' => 'bg-amber-100 text-amber-700'],
                    'red'     => ['card' => 'border-red-200 bg-red-50',       'icon' => 'text-red-500 bg-red-100',       'title' => 'text-red-800',     'badge' => 'bg-red-100 text-red-700'],
                    'blue'    => ['card' => 'border-blue-200 bg-blue-50',     'icon' => 'text-blue-500 bg-blue-100',     'title' => 'text-blue-800',    'badge' => 'bg-blue-100 text-blue-700'],
                ];
                $c = $colors[$ann->type_color] ?? $colors['blue'];
            @endphp
            <div class="bg-white rounded-2xl border {{ $c['card'] }} shadow-sm overflow-hidden">
                <div class="px-5 py-4 flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-xl {{ $c['icon'] }} flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ann->type_icon }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            @if($ann->pinned)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 border border-yellow-200">
                                📌 Pinned
                            </span>
                            @endif
                            <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full {{ $c['badge'] }}">
                                {{ ucfirst($ann->type) }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base leading-snug">{{ $ann->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $ann->body }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                            <span>Posted {{ $ann->created_at->diffForHumans() }}</span>
                            @if($ann->expires_at)
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Expires {{ $ann->expires_at->format('d M Y') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

</x-student-layout>
