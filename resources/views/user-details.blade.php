<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="min-h-screen bg-gray-100">
                <div class="pt-6 pb-12">
                    <div class="max-w-7xl mx-auto relative">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-4">
                            <div class="bg-white border-b border-gray-200">

                                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <tbody>
                                            @isset($user)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4" width="30%">
                                                    ID
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->id }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    Name
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->name }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    Email
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->email }}
                                                </td>
                                            </tr>

                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    Created Date
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->created_at }}
                                                </td>
                                            </tr>

                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    Updated Date
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->updated_at }}
                                                </td>
                                            </tr>
                                            @endisset
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
