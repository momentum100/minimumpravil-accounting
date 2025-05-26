<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Информация о команде: {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-2">Информация о команде</h3>
                    <p><strong>Название:</strong> {{ $team->name }}</p>
                    <p><strong>Описание:</strong></p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $team->description ?: 'Н/Д' }}</p>
                    <p class="mt-2 text-xs text-gray-500">Создана: {{ $team->created_at->format('Y-m-d H:i') }}</p>
                    <p class="text-xs text-gray-500">Обновлена: {{ $team->updated_at->format('Y-m-d H:i') }}</p>

                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('admin.teams.edit', $team) }}" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Редактировать
                        </a>
                        <a href="{{ route('admin.teams.index') }}" class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Назад к списку
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Баеры в этой команде</h3>

                     <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Имя баера</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <th scope="col" class="px-6 py-3">Telegram ID</th>
                                    <th scope="col" class="px-6 py-3">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($team->buyers as $buyer)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $buyer->name }}</td>
                                        <td class="px-6 py-4">{{ $buyer->email ?: 'Н/Д' }}</td>
                                        <td class="px-6 py-4">{{ $buyer->telegram_id ?: 'Н/Д' }}</td>
                                        <td class="px-6 py-4">
                                            <span @class([
                                                'px-2 py-1 text-xs font-semibold leading-tight rounded-full',
                                                'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' => $buyer->active,
                                                'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' => ! $buyer->active,
                                            ])>
                                                {{ $buyer->active ? 'Активен' : 'Неактивен' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Баеры в этой команде не найдены.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 