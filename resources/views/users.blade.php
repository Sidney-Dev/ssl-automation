<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="min-h-screen bg-gray-100">
                <div class="flex justify-end max-w-7xl mx-auto mt-6">
                    <a href="{{ route('registration') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Create User</a>
                </div>

                @if(Session::has('success'))
                <div class="alert alert-success text-green-400 text-center alert-dismissible fade show" role="alert">
                    <strong>{{ Session::get('success') }}</strong>
                </div>
                @endif

                @if (Session::has('error'))
                <p class="block mt-2 text-sm text-red-600 text-center dark:text-red-500">{{ Session::get('error') }}</p>
                @endif

                <div class="pt-6 pb-12">
                    <div class="max-w-7xl mx-auto">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="bg-white border-b border-gray-200">

                                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                    @isset($users)
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3" width="30%">
                                                    NAME
                                                </th>
                                                <th scope="col" class="px-6 py-3" width="40%">
                                                    EMAIL
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    CREATED DATE
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $user)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ $user->name }}
                                                </th>
                                                <td class="px-6 py-4">
                                                    {{ $user->email }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $user->created_at }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="{{ route('view-user',$user->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                                    <a href="{{ route('edit-user',$user->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                                    <button class="font-medium text-blue-600 dark:text-blue-500 hover:underline"  onclick="openDeletePopup('{{$user->id}}')">Delete</button>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    @endisset
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                </div>

                <!-- Reset link popup -->
                <div class="relative z-10" id="reset-link-popup" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

                    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

                    <div class="fixed z-10 inset-0 overflow-y-auto">
                        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reset link</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">Are you sure you want to reset the link?</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                                    <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeResetPopup()">Cancel</button>
                                    <button type="button" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete user popup -->
                <div class="relative z-10" id="delete-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

                    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

                    <div class="fixed z-10 inset-0 overflow-y-auto">
                        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                            <form action="{{ route('delete-user') }}" method="post">
                                @csrf
                                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-red-600" id="modal-title">Delete user</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">Are you sure you want to delete this user?</p>
                                                    <input type="hidden" id="user" name="user" value="" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                                        <button type="button"  class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeDeletePopup()">Cancel</button>
                                        <button type="submit" onclick="this.classList.toggle('button--loading')" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"><span class="loader">Delete</span></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function openResetPopup() {
                document.getElementById("reset-link-popup").style.display = "block";
            }

            function closeResetPopup() {
                document.getElementById("reset-link-popup").style.display = "none";
            }

            // Delete
            function openDeletePopup($user) {
                var $hiddenUser = document.getElementById("user");
                $hiddenUser.value = $user;
                document.getElementById("delete-modal").style.display = "block";
            }

            function closeDeletePopup() {
                document.getElementById("delete-modal").style.display = "none";
            }
            </script>
        </div>
    </div>
</x-app-layout>