<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show register page
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ AUTO ASSIGN ROLE = STUDENT
        $user->assignRole('Student');

        $recipients = User::role(['Admin', 'Teacher'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new SystemNotification(
                title: 'New student registered',
                message: "{$user->name} has created a student account and is waiting for linking.",
                url: route('notifications.index'),
                type: 'student-registered'
            ));
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', false));
    }
}
