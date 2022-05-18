@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Details</title>
</head>

<div>
    @include('nav')
</div>

<body>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Domain Details
                </h2>
            </div>
        </header>

        <div class="pt-6 pb-12">
            <div class="max-w-7xl mx-auto">

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="bg-white border-b border-gray-200">

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <tbody>
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
                                            (domain)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="mt-10 text-xl">Certificates</div>

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    NAME
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
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                    (id)
                                </td>
                                <td class="px-6 py-4">
                                    (name)
                                </td>
                                <td class="px-6 py-4">
                                    (status)
                                </td>
                                <td class="px-6 py-4">
                                    (date & time)
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/certificate-details" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</body>

</html>
