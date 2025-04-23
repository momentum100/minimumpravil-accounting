<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Welcome to the Admin Area!") }}
                    <p class="mt-4">Manage application resources here.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.teams.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Manage Teams</a>
                        <a href="{{ route('admin.users.index') }}" class="ml-4 text-blue-600 dark:text-blue-400 hover:underline">Manage Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 