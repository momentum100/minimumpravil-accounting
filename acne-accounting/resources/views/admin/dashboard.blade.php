<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Админ панель
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    Добро пожаловать в админ зону!
                    <p class="mt-4">Управляйте ресурсами приложения здесь.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.teams.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Управление командами</a>
                        <a href="{{ route('admin.users.index') }}" class="ml-4 text-blue-600 dark:text-blue-400 hover:underline">Управление пользователями</a>
                        <a href="{{ route('admin.transactions.index') }}" class="ml-4 text-blue-600 dark:text-blue-400 hover:underline">Просмотр транзакций</a>
                        <a href="{{ route('admin.bulk-expenses.create') }}" class="ml-4 text-blue-600 dark:text-blue-400 hover:underline">Массовый ввод расходов</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 