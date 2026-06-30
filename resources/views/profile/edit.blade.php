@if(auth()->user()->hasRole('Student'))
<x-student-layout>
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.update-password-form')
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-student-layout>
@else
<x-app-layout>
    <x-slot name="header">{{ __('Update Profile') }}</x-slot>
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.update-password-form')
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
@endif
