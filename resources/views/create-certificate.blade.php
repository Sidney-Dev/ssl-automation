@extends('localdev')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Certificate</title>
</head>

<div>
    @include('nav')
</div>

<body>

    <!-- Successful toast -->
    <div class="absolute right-0 mr-6 mt-2">
        <div id="toast-success" class="hidden flex items-center w-full max-w-xs p-6 mb-4 text-gray-500 bg-white rounded-md shadow" role="alert" style="background-color:#f0fdf4">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 text-sm font-normal mr-6">Certificate created successfully</div>
            <button type="button" class="ml-auto items-center -mx-1.5 -my-1.5 text-gray-400 rounded-lg p-1.5 inline-flex h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close" onclick="toggleSuccessToast()">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="min-h-screen bg-gray-100">

        <header v-if="$slots.header" class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Certificate
                </h2>
            </div>
        </header>
        @if ($errors->has('domain'))
            <p class="block mt-2 text-sm text-red-600 text-center dark:text-red-500">{{ $errors->first('domain') }}</p>
        @endif
        <form method="post" action="{{ url('/create-certificate') }}">
            @csrf
            <div class="py-6">
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="bg-white border-b border-gray-200 py-8 sm:px-20 items-center">
                            <div class="flex items-center">
                                <label>domain</label>
                                <input type="text" name="domain" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-3/4 p-2.5 mx-auto">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end max-w-7xl mx-auto">
                <a href="/certificates" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2">Cancel</a>
                <input type="submit" name="create" value="Create Certificate" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2">
            </div>
        </form>

    </div>

    <script>
        function toggleSuccessToast() {
            document.getElementById("toast-success").classList.toggle("hidden");
        }
    </script>

</body>

</html>
