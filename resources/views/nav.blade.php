<nav class="bg-white border-b" id="nav">
    <div class="container flex items-center max-w-7xl mx-auto py-4">
        <div class="self-center text-xl font-semibold whitespace-nowrap">SSL Automation</div>
        <div class="w-full flex justify-between ml-6">
            <ul class="flex mt-4 md:flex-row md:space-x-8 md:mt-0 md:text-sm md:font-medium">
                <li>
                    <a href="/certificates" class="block py-2 pr-4 pl-3 text-gray-700 bg-blue-700 rounded md:bg-transparent md:p-0" aria-current="page">Certificates</a>
                </li>
                <li>
                    <a href="/environments" class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">Environments</a>
                </li>
                <li>
                    <a href="/users" class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">Users</a>
                </li>
            </ul>
            @if (Route::has('login'))
            <ul class="flex mt-4 md:flex-row md:space-x-8 md:mt-0 md:text-sm md:font-medium">
                @auth
                <li>{{ Auth::user()->name }}</li>
                <li>
                <form action=" {{ url('/logout') }}" style="display:inline" method="post">
                @csrf
                    <a href="#" onclick="this.parentNode.submit()" class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">Log out</a>
                </form>
                </li>
                @endauth
            </ul>
            @endif
        </div>
    </div>
</nav>