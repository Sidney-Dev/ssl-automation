@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environments</title>
</head>

<div>
    @include('nav')
</div>

<body>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Environments
                </h2>
            </div>
        </header>

        @if (Session::has('error'))
            <p class="block mt-2 text-sm text-red-600 text-center dark:text-red-500">{{ Session::get('error') }}</p>
        @endif

        <div class="pt-6 pb-12">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="bg-white border-b border-gray-200">

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            @isset($environmentDetails)
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
                                            NAME
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            DOMAINS
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            ACTIVE DOMAINS
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            DEFAULT DOMAIN
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            TOTAL DOMAIN
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($environmentDetails as $details)
                                    <tr class="bg-white border-b hover:bg-gray-50" style="vertical-align:baseline">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $details->id }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $details->label }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $details->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @foreach($details->domains as $domain)
                                            <p>{{ $domain }}</p>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $details->active_domain }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $details->default_domain }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ count($details->domains) }}
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
</body>

</html>
