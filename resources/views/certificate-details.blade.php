@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Details</title>
</head>

<div>
    @include('nav')
</div>

<body>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Certificate Details
                </h2>
            </div>
        </header>

        <div class="pt-6 pb-12">
            <div class="max-w-7xl mx-auto relative">

                <div class="flex justify-end">

                    <button id="editDropdown" data-dropdown-toggle="editMenu" class="font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center" type="button" onclick="toggleEditMenu()">
                        Edit
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                </div>

                <!-- Dropdown menu -->
                <div id="editMenu" class="absolute right-0 z-10 hidden bg-white divide-y divide-gray-100 rounded shadow w-44 mt-1">
                    <ul class="py-1 text-sm text-gray-700" aria-labelledby="dropdownDefault">
                        <li>
                            <a href="#" class="block px-4 py-2 text-red-600 hover:bg-gray-100" onclick="openDeletePopup()">Delete certificate</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100" onclick="openRenewPopup()">Renew certificate</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100" onclick="openEnvironmentPopup()">Add to environment</a>
                        </li>
                    </ul>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-4">
                    <div class="bg-white border-b border-gray-200">

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <tbody>
                                    @isset($certificateInfos)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4" width="30%">
                                            ID
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->id }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            name
                                        </td>
                                        <td class="px-6 py-4">
                                            (name)
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            domain
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->domain }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            status
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->created == 1 ? 'Success' : 'Failed'  }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            last_renewed_at
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->last_renewed_at }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            fullchain_path
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->fullchain_path }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            chain_path
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->chain_path }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            cert_path
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $certificateInfos->cert_path }}
                                        </td>
                                    </tr>
                                    <tr class="bg-white hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            privkey_path
                                        </td>
                                        <td class="px-6 py-4">
                                        {{ $certificateInfos->privkey_path }}
                                        </td>
                                    </tr>
                                    @endisset
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="mt-10 text-xl">Domains</div>

                <div class="flex justify-end max-w-7xl mx-auto mt-2">
                    <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none" onclick="openNewDomainPopup()">Add New Domain</button>
                </div>

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    DOMAIN
                                </th>
                                <th scope="col" class="px-6 py-3">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                    (id)
                                </td>
                                <td class="px-6 py-4">
                                    (domain)
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="/domain-details" class="font-medium text-blue-600 hover:underline">View</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <!-- Delete certificate popup -->
    <div class="relative z-10" id="delete-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-red-600" id="modal-title">Delete certificate</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this certificate?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                        <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeDeletePopup()">Cancel</button>
                        <button type="button" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew certificate popup -->
    <div class="relative z-10" id="renew-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Renew certificate</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to renew this certificate?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                        <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeRenewPopup()">Cancel</button>
                        <button type="button" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add to environment popup -->
    <div class="relative z-10" id="add-to-environment-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add to Evironment</h3>
                            </div>
                        </div>
                        <div class="py-8 sm:px-10">
                            <table width="100%">
                                <tr>
                                    <td>name</td>
                                    <td><input type="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></td>
                                </tr>
                                <tr>
                                    <td>environment</td>
                                    <td class="pt-4"><input type="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                        <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeEnvironmentPopup()">Cancel</button>
                        <button type="button" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm">Run Action</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add new domain popup -->
    <div class="relative z-10" id="add-new-domain-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add New Domain</h3>
                            </div>
                        </div>
                        <div class="py-8 sm:px-10">
                            <table width="100%">
                                <tr>
                                    <td>name</td>
                                    <td><input type="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></td>
                                </tr>
                                <tr>
                                    <td>domain</td>
                                    <td class="pt-4"><input type="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                        <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeNewDomainPopup()">Cancel</button>
                        <button type="button" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm">Add Domain</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function toggleEditMenu() {
            document.getElementById("editMenu").classList.toggle("hidden");
        }

        // Delete
        function openDeletePopup() {
            document.getElementById("delete-modal").style.display = "block";
        }

        function closeDeletePopup() {
            document.getElementById("delete-modal").style.display = "none";
        }

        // Renew
        function openRenewPopup() {
            document.getElementById("renew-modal").style.display = "block";
        }

        function closeRenewPopup() {
            document.getElementById("renew-modal").style.display = "none";
        }

        // Add to environment
        function openEnvironmentPopup() {
            document.getElementById("add-to-environment-modal").style.display = "block";
        }

        function closeEnvironmentPopup() {
            document.getElementById("add-to-environment-modal").style.display = "none";
        }

        // Add new domain
        function openNewDomainPopup() {
            document.getElementById("add-new-domain-modal").style.display = "block";
        }

        function closeNewDomainPopup() {
            document.getElementById("add-new-domain-modal").style.display = "none";
        }
    </script>

</body>

</html>
