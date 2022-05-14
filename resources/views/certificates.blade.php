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

        <nav class="bg-white border-b">
            <div class="container flex justify-between items-center max-w-7xl mx-auto py-4">
                <div class="self-center text-xl font-semibold whitespace-nowrap">SSL Automation</div>
                <div class="w-full md:block md:w-auto ml-6">
                    <ul class="flex mt-4 md:flex-row md:space-x-8 md:mt-0 md:text-sm md:font-medium">
                        <li>
                            <a href="/certificates" class="block py-2 pr-4 pl-3 text-blue-600 rounded md:bg-transparent md:text-blue-700 md:p-0" aria-current="page">Certificates</a>
                        </li>
                        <li>
                            <a href="/environments" class="block py-2 pr-4 pl-3 text-gray-700 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">Environments</a>
                        </li>
                        <li>
                            <a href="/users" class="block py-2 pr-4 pl-3 text-gray-700 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">Users</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

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
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            1
                                        </th>
                                        <td class="px-6 py-4">
                                            domain
                                        </td>
                                        <td class="px-6 py-4">
                                            Success
                                        </td>
                                        <td class="px-6 py-4">
                                            date & time
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="/certificate-details" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                        </td>
                                    </tr>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            2
                                        </th>
                                        <td class="px-6 py-4">
                                            domain
                                        </td>
                                        <td class="px-6 py-4">
                                            Success
                                        </td>
                                        <td class="px-6 py-4">
                                            date & time
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="/certificate-details" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                        </td>
                                    </tr>
                                    <tr class="bg-white hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            3
                                        </th>
                                        <td class="px-6 py-4">
                                            domain
                                        </td>
                                        <td class="px-6 py-4">
                                            Success
                                        </td>
                                        <td class="px-6 py-4">
                                            date & time
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
        </div>

    </div>
</body>

</html>