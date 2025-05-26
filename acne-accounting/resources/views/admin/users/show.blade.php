<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Информация о пользователе: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Информация о пользователе</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Имя:</strong> {{ $user->name }}</p>
                            <p><strong>Роль:</strong> 
                                @if($user->role === 'buyer')
                                    Баер
                                @elseif($user->role === 'agency')
                                    Агентство
                                @else
                                    {{ ucfirst($user->role) }}
                                @endif
                            </p>
                            <p><strong>Email:</strong> {{ $user->email ?: 'Н/Д' }}</p>
                            <p><strong>Telegram ID:</strong> {{ $user->telegram_id ?: 'Н/Д' }}</p>
                            <p><strong>Статус:</strong>
                                <span @class([
                                    'px-2 py-0.5 text-xs font-semibold leading-tight rounded-full',
                                    'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' => $user->active,
                                    'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' => ! $user->active,
                                ])>
                                    {{ $user->active ? 'Активен' : 'Неактивен' }}
                                </span>
                             </p>
                            <p><strong>Виртуальный пользователь:</strong> {{ $user->is_virtual ? 'Да' : 'Нет' }}</p>
                            <p class="mt-2 text-xs text-gray-500">Создан: {{ $user->created_at->format('Y-m-d H:i') }}</p>
                            <p class="text-xs text-gray-500">Обновлен: {{ $user->updated_at->format('Y-m-d H:i') }}</p>
                        </div>

                        <div>
                            @if($user->role === 'buyer')
                                <p><strong>Команда:</strong> {{ $user->team?->name ?: 'Н/Д' }}</p>
                                <p><strong>Sub2 теги:</strong></p>
                                <pre class="mt-1 p-2 bg-gray-100 dark:bg-gray-700 rounded text-sm overflow-x-auto">{{ json_encode($user->sub2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Н/Д' }}</pre>
                            @elseif($user->role === 'agency')
                                <p><strong>Контактная информация:</strong></p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $user->contact_info ?: 'Н/Д' }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Редактировать
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Назад к списку
                        </a>
                         @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Удалить
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Accounts List --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Связанные счета</h3>
                    @if($user->accounts->isNotEmpty())
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">ID</th>
                                        <th scope="col" class="px-6 py-3">Тип</th>
                                        <th scope="col" class="px-6 py-3">Описание</th>
                                        <th scope="col" class="px-6 py-3">Валюта</th>
                                        {{-- Add Balance later if needed --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-right">Balance</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->accounts as $account)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4">{{ $account->id }}</td>
                                            <td class="px-6 py-4">{{ $account->account_type }}</td>
                                            <td class="px-6 py-4">{{ $account->description }}</td>
                                            <td class="px-6 py-4">{{ $account->currency }}</td>
                                            {{-- <td class="px-6 py-4 text-right"> Format balance </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                         <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">У этого пользователя нет связанных счетов.</p>
                    @endif
                </div>
            </div>

            {{-- TODO: Potentially add related data like transactions etc. --}}

        </div>
    </div>
</x-app-layout> 