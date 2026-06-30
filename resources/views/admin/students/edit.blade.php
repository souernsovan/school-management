<x-app-layout>

    <x-slot name="header">Update Student</x-slot>

    <div class="p-4 sm:p-6">
        <div class="max-w-3xl mx-auto">

            <form method="POST" action="{{ route('students.update', $student) }}"
                  class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
                  x-data="{
                      email: '{{ old('email', $student->email) }}',
                      linked: {{ $linkedUser ? 'true' : 'false' }},
                      linkedName: '{{ $linkedUser?->name }}',
                      pickUser(e) {
                          const opt = e.target.options[e.target.selectedIndex];
                          if (opt.value) {
                              this.email      = opt.dataset.email;
                              this.linked     = true;
                              this.linkedName = opt.dataset.name;
                          } else {
                              this.email      = '';
                              this.linked     = false;
                              this.linkedName = '';
                          }
                      }
                  }">
                @csrf
                @method('PUT')

                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-800">Update Student Information</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Edit details for {{ $student->first_name }} {{ $student->last_name }}</p>
                </div>

                <!-- Body -->
                <div class="p-5 sm:p-6 space-y-5">

                    @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Name -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                            <input name="first_name" type="text" value="{{ old('first_name', $student->first_name) }}"
                                   class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                   placeholder="Enter first name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input name="last_name" type="text" value="{{ old('last_name', $student->last_name) }}"
                                   class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                   placeholder="Enter last name">
                        </div>
                    </div>

                    <!-- Link to User + Email -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Account Linking</p>

                        <!-- User picker -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Link to User Account
                                <span class="text-slate-400 font-normal text-xs">(optional)</span>
                            </label>
                            <select @change="pickUser($event)"
                                    class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm bg-white">
                                <option value="" data-email="" data-name="">— No link, enter email manually —</option>
                                @foreach($studentUsers as $u)
                                <option value="{{ $u->id }}"
                                        data-email="{{ $u->email }}"
                                        data-name="{{ $u->name }}"
                                        {{ $linkedUser && $linkedUser->id === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} — {{ $u->email }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Selecting a user will update the email and link their portal access.</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <div class="relative">
                                <input name="email" type="email"
                                       x-model="email"
                                       :readonly="linked"
                                       :class="linked ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-slate-300'"
                                       class="w-full rounded-xl border focus:border-blue-500 focus:ring-blue-500 text-sm pr-20"
                                       placeholder="Enter email address">
                                <span x-show="linked"
                                      class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-emerald-600 text-xs font-semibold pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Linked
                                </span>
                            </div>
                            <p x-show="linked" class="text-[11px] text-emerald-600 mt-1" x-text="'Linked to: ' + linkedName"></p>
                            <p x-show="!linked && email" class="text-[11px] text-amber-500 mt-1">⚠ Not linked to any user account</p>
                        </div>
                    </div>

                    <!-- Class -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Class & Section</label>
                        <select name="class_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Class and Section</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} – {{ $class->section }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Phone + DOB -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input name="phone" type="text" value="{{ old('phone', $student->phone) }}"
                                   class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                   placeholder="Enter phone number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input name="dob" type="date" value="{{ old('dob', $student->dob) }}"
                                   class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                        <select name="gender" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Gender</option>
                            <option value="Male"   {{ old('gender', $student->gender) == 'Male'   ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea name="address" rows="2"
                                  class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                  placeholder="Enter full address">{{ old('address', $student->address) }}</textarea>
                    </div>

                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-5 sm:px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('students.index') }}"
                       class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 text-sm font-semibold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-sm shadow-blue-600/20 transition">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>
