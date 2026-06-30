<x-app-layout>
    <x-slot name="header">Notifications</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-5">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Notification Center</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        You have {{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}.
                    </p>
                </div>

                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Mark all as read
                    </button>
                </form>
            </div>

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $isUnread = is_null($notification->read_at);
                        $title = $data['title'] ?? 'Notification';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? null;
                    @endphp

                    <div class="rounded-2xl border {{ $isUnread ? 'border-blue-100 bg-blue-50/50' : 'border-slate-100 bg-white' }} shadow-sm p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $isUnread ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.157V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.157c0 .538-.214 1.055-.595 1.438L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $title }}
                                            @if($isUnread)
                                                <span class="ml-2 inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">New</span>
                                            @endif
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $message }}</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($url)
                                            <a href="{{ route('notifications.read', $notification->id) }}"
                                               class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                                Open
                                            </a>
                                        @endif

                                        @if($isUnread)
                                            <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                                    Mark read
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-3 text-xs text-slate-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm p-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">No notifications yet</p>
                        <p class="mt-1 text-sm text-slate-400">New system updates will appear here automatically.</p>
                    </div>
                @endforelse
            </div>

            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
