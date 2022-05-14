@extends('localdev')

@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Environments
                </h2>
            </div>
        </header>

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
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            1
                                        </th>
                                        <td class="px-6 py-4">
                                            (label)
                                        </td>
                                        <td class="px-6 py-4">
                                            (name)
                                        </td>
                                        <td class="px-6 py-4">
                                            (domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (active domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (default domain)
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            2
                                        </th>
                                        <td class="px-6 py-4">
                                            (label)
                                        </td>
                                        <td class="px-6 py-4">
                                            (name)
                                        </td>
                                        <td class="px-6 py-4">
                                            (domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (active domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (default domain)
                                        </td>
                                    </tr>
                                    <tr class="bg-white hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            3
                                        </th>
                                        <td class="px-6 py-4">
                                            (label)
                                        </td>
                                        <td class="px-6 py-4">
                                            (name)
                                        </td>
                                        <td class="px-6 py-4">
                                            (domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (active domains)
                                        </td>
                                        <td class="px-6 py-4">
                                            (default domain)
                                        </td>
                                    </tr>
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