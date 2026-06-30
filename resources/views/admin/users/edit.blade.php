<x-app-layout>

    <x-slot name="header">
        Edit User
    </x-slot>

    <div class="p-6">

        <div class="max-w-3xl mx-auto">

            <form method="POST" action="{{ route('users.update', $user) }}"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden">

                @csrf
                @method('PUT')

                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Update User Information
                    </h2>
                    <p class="text-sm text-slate-500">
                        Edit existing user details
                    </p>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-5">

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Full Name</label>
                        <input name="name" type="text"
                               value="{{ $user->name }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email Address</label>
                        <input name="email" type="email"
                               value="{{ $user->email }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Role</label>

                        <select name="role"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">

                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t flex justify-between">


                    <div class="flex gap-3">

                        <a href="{{ route('users.index') }}"
                           class="px-4 py-2 rounded-xl border hover:bg-slate-100">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow">
                            Update User
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>