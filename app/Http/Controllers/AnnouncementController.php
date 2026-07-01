<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $canPost = $user->hasRole('Admin');

        $announcements = Announcement::with('author')
            ->active()
            ->when(!$canPost, fn($q) => $q->forUser($user))
            ->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.announcements.index', compact('announcements', 'canPost'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:2000',
            'type'       => 'required|in:info,success,warning,urgent',
            'audience'   => 'required|in:all,teachers,students',
            'pinned'     => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        Announcement::create([
            ...$data,
            'pinned'  => $request->boolean('pinned'),
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}
