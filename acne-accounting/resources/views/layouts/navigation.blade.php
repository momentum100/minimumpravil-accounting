<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Панель управления') }}
                    </x-nav-link>

                    {{-- Admin Links --}}
                    @if(in_array(Auth::user()->role, ['owner', 'finance']))
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Админ панель') }}
                         </x-nav-link>
                         <x-nav-link :href="route('admin.fund-transfers.create')" :active="request()->routeIs('admin.fund-transfers.create')">
                            {{ __('Создать перевод средств') }}
                         </x-nav-link>
                         {{-- Add Bulk Transfer Link Here --}}
                         <x-nav-link :href="route('admin.fund-transfers.bulk.create')" :active="request()->routeIs('admin.fund-transfers.bulk.create')">
                            {{ __('Массовые переводы средств') }}
                         </x-nav-link>
                         {{-- <x-nav-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                            {{ __('View Transactions') }}
                         </x-nav-link> --}}
                         <x-nav-link :href="route('admin.daily-expenses.index')" :active="request()->routeIs('admin.daily-expenses.*')">
                            {{ __('Ежедневные расходы') }}
                         </x-nav-link>
                         <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Управление пользователями') }}
                         </x-nav-link>
                         <x-nav-link :href="route('admin.teams.index')" :active="request()->routeIs('admin.teams.*')">
                            {{ __('Управление командами') }}
                         </x-nav-link>
                         {{-- Add Buyer Statement Link --}}
                         <x-nav-link :href="route('admin.buyer-statements.index')" :active="request()->routeIs('admin.buyer-statements.*')">
                             {{ __('Отчеты баеров') }}
                         </x-nav-link>
                         {{-- Add Admin Agency Transfers Link --}}
                         <x-nav-link :href="route('admin.agency-transfers.index')" :active="request()->routeIs('admin.agency-transfers.index')">
                            {{ __('Расходы агенств (Админ)') }}
                         </x-nav-link>
                         {{-- The old generic 'Admin Area' link is removed in favor of specific links --}}
                         {{--
                         <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard') || request()->routeIs('admin.teams.*') || request()->routeIs('admin.users.*')" class="relative group">
                            {{ __('Admin Area') }}
                             <span class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-400"></span>
                         </x-nav-link>
                         --}}
                    @endif

                    {{-- Buyer Links --}}
                    @if(Auth::user()->role === 'buyer')
                        <x-nav-link :href="route('buyer.dashboard')" :active="request()->routeIs('buyer.dashboard')">
                            {{ __('Мой отчет') }}
                        </x-nav-link>
                        {{-- Add Agency Transfer Link --}}
                        <x-nav-link :href="route('buyer.agency-transfers.index')" :active="request()->routeIs('buyer.agency-transfers.index')">
                            {{ __('Расходы агентств') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Профиль') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Выйти') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Панель управления') }}
            </x-responsive-nav-link>
        </div>

        {{-- Responsive Admin Links --}}
        @if(in_array(Auth::user()->role, ['owner', 'finance']))
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ __('Админ зона') }}</div>
                </div>
                <div class="mt-3 space-y-1">
                     <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Админ панель') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.fund-transfers.create')" :active="request()->routeIs('admin.fund-transfers.create')">
                        {{ __('Создать перевод средств') }}
                    </x-responsive-nav-link>
                    {{-- Add Bulk Transfer Link Here (Responsive) --}}
                    <x-responsive-nav-link :href="route('admin.fund-transfers.bulk.create')" :active="request()->routeIs('admin.fund-transfers.bulk.create')">
                        {{ __('Массовые переводы средств') }}
                    </x-responsive-nav-link>
                    {{-- <x-responsive-nav-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                        {{ __('View Transactions') }}
                    </x-responsive-nav-link> --}}
                    <x-responsive-nav-link :href="route('admin.daily-expenses.index')" :active="request()->routeIs('admin.daily-expenses.*')">
                        {{ __('Ежедневные расходы') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        {{ __('Управление пользователями') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.teams.index')" :active="request()->routeIs('admin.teams.*')">
                        {{ __('Управление командами') }}
                    </x-responsive-nav-link>
                    {{-- Add Responsive Buyer Statement Link --}}
                    <x-responsive-nav-link :href="route('admin.buyer-statements.index')" :active="request()->routeIs('admin.buyer-statements.*')">
                        {{ __('Отчеты баеров') }}
                    </x-responsive-nav-link>
                    {{-- Add Responsive Admin Agency Transfers Link --}}
                    <x-responsive-nav-link :href="route('admin.agency-transfers.index')" :active="request()->routeIs('admin.agency-transfers.index')">
                        {{ __('Переводы агентств (Админ)') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endif

        {{-- Responsive Buyer Links --}}
        @if(Auth::user()->role === 'buyer')
            <div class="pt-4 pb-1 border-t border-gray-200">
                 <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }} (Баер)</div>
                </div>
                <div class="mt-3 space-y-1">
                     <x-responsive-nav-link :href="route('buyer.dashboard')" :active="request()->routeIs('buyer.dashboard')">
                        {{ __('Мой отчет') }}
                    </x-responsive-nav-link>
                     {{-- Add Responsive Agency Transfer Link --}}
                     <x-responsive-nav-link :href="route('buyer.agency-transfers.index')" :active="request()->routeIs('buyer.agency-transfers.index')">
                        {{ __('Переводы агентств') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endif

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Профиль') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Выйти') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
