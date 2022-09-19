<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="min-h-screen bg-gray-100">

                <div class="flex justify-end max-w-7xl mx-auto mt-6">
                    <a href="/create-certificate" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Create Certificate</a>
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
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3">
                                                    ID
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    LABEL
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    STATUS
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    LAST RENEWED
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    VALIDATE UNTIL
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                    ADDITIONAL DOMAINS
                                                </th>
                                                <th scope="col" class="px-6 py-3">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allCertificateInfos as $certInfo)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ $certInfo->id }}
                                                </th>
                                                <td class="px-6 py-4">
                                                    @if($certInfo->label)
                                                        {{ $certInfo->label }}
                                                    @else
                                                        {{ $certInfo->domain }}
                                                    @endif
                                                </td>
                                                @if($certInfo->status == 'success')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-success self-center mr-2"></div>
                                                    <div>Success</div>
                                                </td>
                                                @elseif($certInfo->status == 'pending')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-pending self-center mr-2"></div>
                                                    <div>Pending</div>
                                                </td>
                                                @elseif($certInfo->status == 'installed')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-install self-center mr-2"></div>
                                                    <div>Installed</div>
                                                </td>
                                                @elseif($certInfo->status == 'activated')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-activate self-center mr-2"></div>
                                                    <div>Activated</div>
                                                </td>
                                                @elseif($certInfo->status == 'error')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-error self-center mr-2"></div>
                                                    <div>Error</div>
                                                </td>
                                                @elseif($certInfo->status == 'deactivated')
                                                <td class="flex px-6 py-4">
                                                    <div class="status-error self-center mr-2"></div>
                                                    <div>Deactivated</div>
                                                </td>
                                                @endif
                                                <td class="px-6 py-4">
                                                    {{ $certInfo->last_renewed_at }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certInfo->certificate_validation_date }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ count($certInfo->domains) }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="{{ route('certificate-details', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                                    @if($certInfo->status == 'pending')
                                                    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" onclick="openEnvironmentPopup( '{{ $certInfo->domain }}' )">Install</a>
                                                    @endif
                                                    @if($certInfo->status == 'pending' || $certInfo->status == 'error')
                                                    <a href="#" class="font-medium text-red-600 dark:text-blue-500 hover:underline" onclick="openDeletePopup( '{{ $certInfo->id }}' )">Delete</a>
                                                    @endif
                                                    @if($certInfo->status == 'success' || $certInfo->status == 'deactivated' || $certInfo->status == 'activated' || $certInfo->status == 'installed')
                                                    <a href="{{ ($certInfo->status=='pending' || $certInfo->status=='deactivated' || $certInfo->status=='installed') ? 
                                                        route('certificate-activate', $certInfo->id) : 
                                                        route('certificate-deactivate', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline mr-4">
                                                        {{ ($certInfo->status=='pending' || $certInfo->status=='deactivated' || $certInfo->status=='installed') ? 'Activate' : 'Deactivate'}}
                                                    </a>
                                                    @endif
                                                    @if($certInfo->status != 'success' && $certInfo->status != 'activated' && $certInfo->status != 'pending' && $certInfo->status != 'error')
                                                    <a href="{{ route('certificate-remove', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Remove From Acquia</a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <!-- Delete certificate popup -->
                        <div class="relative z-10" id="delete-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

                            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

                            <div class="fixed z-10 inset-0 overflow-y-auto">
                                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                                    <form action="{{ route('certificate-delete') }}" method="post">
                                        @csrf
                                        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg leading-6 font-medium text-red-600" id="modal-title">Delete certificate</h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500">Are you sure you want to permanently delete this certificate?</p>
                                                            <input type="hidden" id="certificate" name="certificate" value="" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                                                <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeDeletePopup()">Cancel</button>
                                                <button type="submit" onclick="this.classList.toggle('button--loading')" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"><span class="loader">Delete</span></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Add to environment popup -->
                        <div class="relative z-10" id="add-to-environment-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

                            <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>
                            <div class="fixed z-10 inset-0 overflow-y-auto">
                                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                                    <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                                        <form action="{{ url('certificate-install') }}" method="post">
                                            @csrf
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add to Environment</h3>
                                                    </div>
                                                </div>
                                                <div class="py-8 sm:px-10">
                                                    <table width="100%">
                                                        <tr>
                                                            <td>Label</td>
                                                            <td>
                                                                <input type="text" name="cert_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                                                @if ($errors->has('cert_name'))
                                                                <p class="block mt-2 text-sm text-red-600 dark:text-red-500">{{ $errors->first('cert_name') }}</p>
                                                                @endif
                                                                <input type="hidden" id="domain" name="domain" value="">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>environment</td>
                                                            <td class="pt-4">
                                                                <select name="environment" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                                                    <option value="6290-df6a4756-6dcc-4190-90b9-d7b3e870a6c2">Live</option>
                                                                    <option value="6294-df6a4756-6dcc-4190-90b9-d7b3e870a6c2">Dev</option>
                                                                    <option value="6292-df6a4756-6dcc-4190-90b9-d7b3e870a6c2">Test</option>
                                                                </select>
                                                                @if ($errors->has('environment'))
                                                                <p class="block mt-2 text-sm text-red-600 dark:text-red-500">{{ $errors->first('environment') }}</p>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                                                <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeEnvironmentPopup()">Cancel</button>
                                                <button type="submit" onclick="this.classList.toggle('button--loading')" class="w-50  inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm"><span class='loader'>Run Action</span></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // Add to environment
                function openEnvironmentPopup($domain) {
                    var $hiddenName = document.getElementById("domain");
                    $hiddenName.value = $domain;
                    document.getElementById("add-to-environment-modal").style.display = "block";
                }

                function closeEnvironmentPopup() {
                    document.getElementById("add-to-environment-modal").style.display = "none";
                }

                // Delete
                function openDeletePopup($certificate) {
                    var $hiddenCertificate = document.getElementById("certificate");
                    $hiddenCertificate.value = $certificate;
                    document.getElementById("delete-modal").style.display = "block";
                }

                function closeDeletePopup() {
                    document.getElementById("delete-modal").style.display = "none";
                }
            </script>
        </div>
    </div>
</x-app-layout>