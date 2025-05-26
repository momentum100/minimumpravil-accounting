<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Редактировать пользователя: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="{ role: '{{ old('role', $user->role) }}' }"> {{-- Alpine state for role --}}
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" value="Имя / Название агентства" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" value="Роль" />
                            <select id="role" name="role" x-model="role" @class([
                                'block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm',
                                'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' => $user->id === auth()->id() // Visually disable role change for self
                            ])
                            {{ $user->id === auth()->id() ? 'disabled' : '' }} {{-- Prevent self role change --}} >
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption }}" {{ old('role', $user->role) == $roleOption ? 'selected' : '' }}>
                                        @if($roleOption === 'buyer')
                                            Баер
                                        @elseif($roleOption === 'agency')
                                            Агентство
                                        @else
                                            {{ ucfirst($roleOption) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role" value="{{ $user->role }}" /> {{-- Submit current role if disabled --}}
                                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">Изменение собственной роли не разрешено.</p>
                            @endif
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Email (Optional, primarily for web login roles) -->
                        <div class="mt-4">
                            <x-input-label for="email" value="Email (для входа в веб)" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                         <!-- Password (Optional, leave blank to keep current) -->
                        <div class="mt-4">
                            <x-input-label for="password" value="Новый пароль (оставьте пустым, чтобы сохранить текущий)" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="Подтвердите новый пароль" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Telegram ID (Optional) -->
                        <div class="mt-4">
                            <x-input-label for="telegram_id" value="Telegram ID" />
                            <x-text-input id="telegram_id" class="block mt-1 w-full" type="text" name="telegram_id" :value="old('telegram_id', $user->telegram_id)" />
                            <x-input-error :messages="$errors->get('telegram_id')" class="mt-2" />
                        </div>

                        <!-- Team (Only for Buyers) -->
                        <div class="mt-4" x-show="role === 'buyer'" x-transition>
                            <x-input-label for="team_id" value="Команда" />
                            <select id="team_id" name="team_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Выберите команду</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id', $user->team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
                        </div>

                        {{-- Sub2 Tags Textarea (Only for Buyers) --}}
                        <div class="mt-4" x-show="role === 'buyer'" x-transition.opacity>
                            <x-input-label for="sub2" value="Sub2 теги (один на строку)" />
                            <textarea id="sub2" name="sub2" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('sub2', $user->sub2 && is_array($user->sub2) ? implode("\n", $user->sub2) : '') }}</textarea> {{-- Convert array to lines --}}
                            <x-input-error :messages="$errors->get('sub2')" class="mt-2" />
                            <x-input-error :messages="$errors->get('sub2.*')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Введите теги, разделенные новыми строками. Они будут сохранены как массив JSON.</p>
                        </div>

                        <!-- Contact Info (Only for Agencies) -->
                        <div class="mt-4" x-show="role === 'agency'" x-transition>
                            <x-input-label for="contact_info" value="Контактная информация" />
                            <textarea id="contact_info" name="contact_info" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('contact_info', $user->contact_info) }}</textarea>
                            <x-input-error :messages="$errors->get('contact_info')" class="mt-2" />
                        </div>

                        <!-- Active Status -->
                        <div class="block mt-4">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', $user->active) ? 'checked' : '' }}
                                {{ $user->id === auth()->id() ? 'disabled' : '' }} {{-- Prevent self deactivation --}}
                                >
                                <span @class([
                                    'ms-2 text-sm text-gray-600 dark:text-gray-400',
                                    'text-gray-400 dark:text-gray-500' => $user->id === auth()->id()
                                ])>Активен</span>
                            </label>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="active" value="1" /> {{-- Submit active=1 if disabled --}}
                                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">Деактивация себя не разрешена.</p>
                            @endif
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                Отмена
                            </a>

                            <x-primary-button>
                                Обновить пользователя
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 