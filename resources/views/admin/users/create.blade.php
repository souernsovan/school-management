<x-app-layout>

    <x-slot name="header">
        Create User
    </x-slot>

    <div class="p-4 sm:p-6">

        <div class="max-w-3xl mx-auto">

            <form method="POST" action="{{ route('users.store') }}"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden">

                @csrf

                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        New User Information
                    </h2>
                    <p class="text-sm text-slate-500">
                        Add a new user to the school system
                    </p>
                </div>

                <!-- Body -->
                <div class="p-4 sm:p-6 space-y-5">

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Full Name</label>
                        <input name="name" type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Enter full name">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email Address</label>
                        <input name="email" type="email"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="example@mail.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Password</label>
                        <input name="password" type="password"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="••••••••">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                        <input name="password_confirmation" type="password"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="••••••••">
                        @error('password_confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Role -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Role</label>

                        <select name="role"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">

                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t flex justify-end gap-3">

                    <a href="{{ route('users.index') }}"
                       class="px-4 py-2 rounded-xl border hover:bg-slate-100">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow">
                        Create User
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>