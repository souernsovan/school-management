<x-app-layout>
    <x-slot name="header">Announcements</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Announcements</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ $canPost ? 'Post notices visible to staff and students' : 'Notices from school administration' }}
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 {{ $canPost ? 'lg:grid-cols-3' : '' }} gap-5">

            @if($canPost)
            {{-- Create form (admin only) --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4 sticky top-6">
                    <h3 class="font-bold text-slate-800">Post Announcement</h3>
                    <form method="POST" action="{{ route('announcements.store') }}" class="space-y-3">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Title <span class="text-red-400">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                                   placeholder="Announcement title…"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Message <span class="text-red-400">*</span></label>
                            <textarea name="body" rows="4" required maxlength="2000"
                                      placeholder="Write your announcement…"
                                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 resize-none">{{ old('body') }}</textarea>
                            @error('body')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Type</label>
                                <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach(['info' => 'Info', 'success' => 'Good News', 'warning' => 'Warning', 'urgent' => 'Urgent'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Audience</label>
                                <select name="audience" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">Teachers &amp; Students</option>
                                    <option value="teachers">Teachers only</option>
                                    <option value="students">Students only</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Expires at <span class="font-normal text-slate-400">(optional)</span></label>
                            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="pinned" value="1" {{ old('pinned') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-600">Pin to top</span>
                        </label>

                        <button type="submit"
                                class="w-full bg-blue-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-blue-700 transition shadow-sm">
                            Post Announcement
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- List --}}
            <div class="{{ $canPost ? 'lg:col-span-2' : '' }} space-y-3">
                @forelse($announcements as $ann)
                @php $c = $ann->type_color; @endphp
                <div class="bg-white rounded-2xl border border-{{ $c }}-200 shadow-sm p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-{{ $c }}-100 flex items-center justify-center shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-{{ $c }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ann->type_icon }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    @if($ann->pinned)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5v6h2v-6h5v-2l-2-2z"/></svg>
                                            Pinned
                                        </span>
                                    @endif
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-200">
                                        {{ ucfirst($ann->type) }}
                                    </span>
                                    @if($canPost)
                                    <span class="text-xs text-slate-400 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-full">
                                        {{ $ann->audience === 'all' ? 'Teachers & Students' : ucfirst($ann->audience).' only' }}
                                    </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $ann->title }}</h4>
                                <p class="text-sm text-slate-600 mt-1 whitespace-pre-line leading-relaxed">{{ $ann->body }}</p>
                                <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                    <span>By {{ $ann->author->name ?? '—' }}</span>
                                    <span>·</span>
                                    <span>{{ $ann->created_at->diffForHumans() }}</span>
                                    @if($ann->expires_at)
                                        <span>·</span>
                                        <span>Expires {{ $ann->expires_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($canPost)
                        <form method="POST" action="{{ route('announcements.destroy', $ann) }}" onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        <p class="text-slate-400 text-sm">No announcements for you right now</p>
                    </div>
                @endforelse

                @if($announcements->hasPages())
                    <div class="pt-2">{{ $announcements->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
