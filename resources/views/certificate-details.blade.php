<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="min-h-screen bg-gray-100">
                <div class="pt-6 pb-12">
                    <div class="max-w-7xl mx-auto relative">
                        @if ($errors->has('domain'))
                        <p class="block mt-2 text-sm text-red-600 dark:text-red-500">{{ $errors->first('domain') }}</p>
                        @endif
                        @if(Session::has('success'))
                        <div class="alert alert-success text-green-400 text-center alert-dismissible fade show" role="alert">
                            <strong>{{ Session::get('success') }}</strong>
                        </div>
                        @endif
                        @if (Session::has('error'))
                        <p class="block mt-2 text-sm text-red-600 text-center dark:text-red-500">{{ Session::get('error') }}</p>
                        @endif
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-4">
                            <div class="bg-white border-b border-gray-200">

                                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <tbody>
                                            @isset($certificate)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4" width="30%">
                                                    ID
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->id }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    label
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->label }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    domain
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($certificate->status == 'activated')
                                                    <a href="https://{{ $certificate->domain }}" target="_blank">{{ $certificate->domain }}</a>
                                                    @else
                                                    {{ $certificate->domain }}
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    status in acquia
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->status  }}
                                                </td>
                                            </tr>

                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    status
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->created == 1 ? 'Success' : 'Failed'  }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    last_renewed_at
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->last_renewed_at }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    certificate_validation_date
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->certificate_validation_date }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    fullchain
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->fullchain_path }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    chain
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->chain_path }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    cert
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->cert_path }}
                                                </td>
                                            </tr>
                                            <tr class="bg-white hover:bg-gray-50">
                                                <td class="px-6 py-4">
                                                    private_key
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $certificate->privkey_path }}
                                                </td>
                                            </tr>
                                            @endisset
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <div class="mt-10 text-xl">
                            <!-- Domain count -->
                            @if($certificate->domains)
                            <div class="ml-5 mb-4 text-sm text-gray-500">Domains: {{ count($certificate->domains) }}</div>
                            @else
                            <div>Domains: 0</div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end max-w-7xl mx-auto mt-2">
                            <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none" onclick="openNewDomainPopup('{{ $certificate->domains }}')">Add New Domain</button>
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
                                    {{-- list all domains --}}
                                    @if($certificate->domains)
                                    @foreach($certificate->domains as $domain)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                            {{ $domain->id }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($certificate->status == 'activated')
                                                <a href="https://{{ $domain->name }}" target="_blank">{{ $domain->name }}</a>
                                            @else
                                            {{ $domain->name }}
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <a href="#" onclick="openDeletePopup('{{ $domain->name }}')" class="font-medium text-blue-600 hover:underline">Remove</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
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
                        <form action="{{ route('store-domains', $certificate->id) }}" method="post">
                            @csrf
                            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-red-600" id="modal-title">Delete certificate</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">Are you sure you want to delete this domain?</p>
                                                <input type="hidden" id="subdomain" name="subdomain" value="" />
                                                <input type="hidden" name="action" value="deleted" />
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

            <!-- Add domains popup -->
            <div class="relative z-10" id="add-new-domain-modal" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display:none;">

                <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"></div>

                <div class="fixed z-10 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add domains</h3>
                                    </div>
                                </div>
                                <div class="py-8 sm:px-10">
                                    <table width="100%">

                                        <tr>
                                            <td class="pt-4">
                                                <form action="{{ route('store-domains', $certificate->id) }}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{ $certificate->domain }}">
                                                    <div class="form-group">
                                                        <label for="">Enter up to <span id="count-domain"></span> domains. New line separated.</label>
                                                        <textarea class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" name="domains" id="subdomains"></textarea>
                                                    </div>
                                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-end">
                                                        <button type="button" class="mt-3 w-50 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeNewDomainPopup()">Cancel</button>
                                                        <button type="submit" onclick="this.classList.toggle('button--loading')" class="w-50 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:ml-3 sm:w-auto sm:text-sm"><span class="loader">Add Domain</span></button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
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
                function openDeletePopup($subdomain) {
                    var $hiddenSubdomain = document.getElementById("subdomain");
                    $hiddenSubdomain.value = $subdomain;
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

                // Add new domain
                var cnt = 0;
                var total_domain = 70;
                var count_domain = document.getElementById('count-domain');

                function openNewDomainPopup($domains) {

                    if (cnt == 0) {
                        if ($domains != "") {
                            var $subdomains = "";
                            var $objDomains = JSON.parse($domains);

                            for (var i = 0; i < $objDomains.length; i++) {
                                for (var $key in $objDomains[i]) {
                                    if ($key == "name") $subdomains += $objDomains[i][$key] + "\r\n"
                                }
                            }
                            total_domain -= $objDomains.length;
                            count_domain.innerHTML = total_domain;

                            $subdomains = $subdomains.replace(/^\s+|\s+$/g, '');
                        }

                        const textarea = document.getElementById('subdomains');

                        textarea.value += $subdomains;
                        cnt += 1;
                    }
                    document.getElementById("add-new-domain-modal").style.display = "block";
                }

                function closeNewDomainPopup() {
                    document.getElementById("add-new-domain-modal").style.display = "none";
                }

                const textarea = document.getElementById('subdomains')


                textarea.addEventListener('input', () => {
                    text = textarea.value;
                    lines = text.split("\n");
                    count = lines.length;

                    count_domain.innerHTML = 70 - count;
                })
            </script>

        </div>
    </div>
</x-app-layout>