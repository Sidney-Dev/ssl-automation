<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="min-h-screen flex flex-col items-center sm:pt-0 bg-gray-100">
            <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <x-jet-validation-errors class="mb-4" />
                <form method="POST" action="{{ route('registration') }}">
                    @csrf

                    <div>
                        <x-jet-label for="name" value="{{ __('Name') }}" />
                        <x-jet-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    </div>

                    <div class="mt-4">
                        <x-jet-label for="email" value="{{ __('Email') }}" />
                        <x-jet-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                    </div>

                    <div class="mt-4">
                        <x-jet-label for="password" value="{{ __('Password') }}" />
                        <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password"  autocomplete="new-password" />
                    </div>

                    <div class="mt-4">
                        <x-jet-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                        <x-jet-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation"  autocomplete="new-password" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-jet-button class="ml-4">
                            {{ __('Register') }}
                        </x-jet-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


