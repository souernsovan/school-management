<x-app-layout>

    <x-slot name="header">Teacher Management</x-slot>

    <div class="p-4 sm:p-6">
        <div class="max-w-3xl mx-auto">
            <form method="POST" action="{{ route('teachers.update', $teacher) }}"
                  class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">Edit Teacher</h2>
                    <p class="text-sm text-slate-500">Update teacher information</p>
                </div>

                <div class="p-4 sm:p-6 space-y-5">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">First Name</label>
                            <input name="first_name" type="text" value="{{ old('first_name', $teacher->first_name) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Last Name</label>
                            <input name="last_name" type="text" value="{{ old('last_name', $teacher->last_name) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('last_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', $teacher->email) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <input name="phone" type="text" value="{{ old('phone', $teacher->phone) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Date of Birth</label>
                            <input name="dob" type="date" value="{{ old('dob', $teacher->dob) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Gender</label>
                            <select name="gender" class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $teacher->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $teacher->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Hire Date</label>
                        <input name="hire_date" type="date" value="{{ old('hire_date', $teacher->hire_date) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Qualification</label>
                            <input name="qualification" type="text" value="{{ old('qualification', $teacher->qualification) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Specialization</label>
                            <input name="specialization" type="text" value="{{ old('specialization', $teacher->specialization) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" rows="2"
                                  class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">{{ old('address', $teacher->address) }}</textarea>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="Active" {{ old('status', $teacher->status) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $teacher->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="bg-slate-50 px-6 py-4 border-t flex justify-end gap-3">
                    <a href="{{ route('teachers.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-sm">Cancel</a>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium shadow-sm">Update Teacher</button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>
