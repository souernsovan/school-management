<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $target = $notification->data['url'] ?? null;

        // Fix old notification URLs that predate the /admin prefix
        if ($target && !str_starts_with($target, '/admin')) {
            $adminPaths = [
                '/students', '/teachers', '/school-classes', '/subjects',
                '/timetables', '/attendances', '/exams', '/exam-types',
                '/reports', '/student-results', '/link-accounts', '/users',
            ];
            foreach ($adminPaths as $path) {
                if (str_starts_with($target, $path)) {
                    $target = '/admin' . $target;
                    break;
                }
            }
        }

        return $target
            ? redirect()->to($target)
            : redirect()->route('notifications.index');
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'notifications-read');
    }
}
