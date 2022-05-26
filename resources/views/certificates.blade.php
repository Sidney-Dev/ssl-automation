@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates</title>
</head>

<div>
    @include('nav')
</div>

<body>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Certificates
                </h2>
            </div>
        </header>

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
                                            DOMAIN
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            STATUS
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            LAST RENEWED
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
                                        {{ $certInfo->domain }}
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
                                        @elseif($certInfo->status == 'error')
                                        <td class="flex px-6 py-4">
                                            <div class="status-error self-center mr-2"></div>
                                            <div>Error</div>
                                        </td>
                                        @endif
                                        <td class="px-6 py-4">
                                        {{ $certInfo->last_renewed_at }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('certificate-details', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                            @if($certInfo->status == 'pending'  || $certInfo->status == 'success')
                                            <a href="{{ ($certInfo->slug == null || $certInfo->slug != null) && $certInfo->status=='pending' ? route('certificate-activate', $certInfo->id) : route('certificate-deactivate', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline mr-4">{{ ($certInfo->slug == null || $certInfo->slug != null) && $certInfo->status=='pending' ? 'Activate' : 'Deactivate'}}</a>
                                            @endif
                                            @if($certInfo->status != 'success')
                                            <a href="{{ route('certificate-delete', $certInfo->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Remove</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
