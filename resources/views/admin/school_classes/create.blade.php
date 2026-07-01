<x-app-layout>

    <x-slot name="header">
        Create School Class
    </x-slot>

    <div class="p-4 sm:p-6">

        <div class="max-w-3xl mx-auto">

            <form method="POST" action="{{ route('school-classes.store') }}"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden">

                @csrf

                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        New Class Information
                    </h2>
                    <p class="text-sm text-slate-500">
                        Add a new class to the school system
                    </p>
                </div>

                <!-- Body -->
                <div class="p-4 sm:p-6 space-y-5">

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Class Name</label>
                        <input name="name" type="number"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g, 7">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Section</label>
                        <input name="section" type="text"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g, A B">
                        @error('section')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Room -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Room</label>
                        <input name="room" type="text" value="{{ old('room') }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g. Room 101, Room 7A">
                        @error('room')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Enter class description"></textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>



                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t flex justify-end gap-3">

                    <a href="{{ route('school-classes.index') }}"
                       class="px-4 py-2 rounded-xl border hover:bg-slate-100">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow">
                        Create Class
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>