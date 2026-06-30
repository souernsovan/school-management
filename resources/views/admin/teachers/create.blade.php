<x-app-layout>

    <x-slot name="header">
        Create Teacher
    </x-slot>

    <div class="p-4 sm:p-6">

        <div class="max-w-3xl mx-auto">

            <form method="POST" action="{{ route('teachers.store') }}"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden">

                @csrf

                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        New Teacher Information
                    </h2>
                    <p class="text-sm text-slate-500">
                        Add a new teacher to the school system
                    </p>
                </div>

                <!-- Body -->
                <div class="p-4 sm:p-6 space-y-5">

                    <!-- First Name -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- First Name -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">First Name</label>
                        <input name="first_name" type="text"
                            value="{{ old('first_name') }}"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter first name">
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Last Name</label>
                        <input name="last_name" type="text"
                            value="{{ old('last_name') }}"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter last name">
                    </div>

                </div>

                    <!-- Email name -->

                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input name="email" type="email"
                               value="{{ old('email') }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Enter email address">
                    </div>



                    <!-- Phone -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <input name="phone" type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Enter phone number">
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Date of Birth</label>
                        <input name="dob" type="date"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Gender</label>
                        <select name="gender"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">

                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>

                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Hire Date</label>
                        <input name="hire_date" type="date"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Qualification -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Qualification</label>
                        <input name="qualification" type="text"
                            value="{{ old('qualification') }}"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter qualification">
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Specialization</label>
                        <input name="specialization" type="text"
                            value="{{ old('specialization') }}"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter specialization">
                    </div>

                </div>

                    <!-- Address -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address"
                                  class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Enter full address"></textarea>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Status</label>
                        <select name="status"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t flex justify-end gap-3">

                    <a href="{{ route('teachers.index') }}"
                       class="px-4 py-2 rounded-xl border hover:bg-slate-100">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow">
                        Create Teacher
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>