<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Создать нового пользователя
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="{ role: '{{ old('role', 'buyer') }}' }"> {{-- Alpine state for role --}}
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" value="Имя / Название агентства" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" value="Роль" />
                            <select id="role" name="role" x-model="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption }}" {{ old('role', 'buyer') == $roleOption ? 'selected' : '' }}>
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
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Email (Only for Web Login Roles like Owner, Finance, Buyer) -->
                        <div class="mt-4" x-show="['owner', 'finance', 'buyer'].includes(role)" x-transition>
                            <x-input-label for="email" value="Email (для входа в веб)" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Обязательно для ролей Owner, Finance или Баер для входа в веб.</p>
                        </div>

                        <!-- Password (Only for Web Login Roles like Owner, Finance, Buyer) -->
                        <div class="mt-4" x-show="['owner', 'finance', 'buyer'].includes(role)" x-transition>
                            <x-input-label for="password" value="Пароль (для входа в веб)" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="mt-4" x-show="['owner', 'finance', 'buyer'].includes(role)" x-transition>
                            <x-input-label for="password_confirmation" value="Подтвердите пароль" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Telegram ID (Optional for all roles) -->
                        <div class="mt-4">
                            <x-input-label for="telegram_id" value="Telegram ID (Необязательно)" />
                            <x-text-input id="telegram_id" class="block mt-1 w-full" type="text" name="telegram_id" :value="old('telegram_id')" />
                            <x-input-error :messages="$errors->get('telegram_id')" class="mt-2" />
                        </div>

                        <!-- Team (Only for Buyers) -->
                        <div class="mt-4" x-show="role === 'buyer'" x-transition>
                            <x-input-label for="team_id" value="Команда" />
                            <select id="team_id" name="team_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Выберите команду</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
                        </div>

                        {{-- Sub2 Tags Textarea (Only for Buyers) --}}
                        <div class="mt-4" x-show="role === 'buyer'" x-transition>
                            <x-input-label for="sub2" value="Sub2 теги (один на строку)" />
                            <textarea id="sub2" name="sub2" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('sub2') }}</textarea>
                            <x-input-error :messages="$errors->get('sub2')" class="mt-2" />
                            <x-input-error :messages="$errors->get('sub2.*')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Введите теги, разделенные новыми строками. Они будут сохранены как массив JSON.</p>
                        </div>

                        <!-- Terms (Only for Agencies) -->
                        <div class="mt-4" x-show="role === 'agency'" x-transition>
                            <x-input-label for="terms" value="Условия агентства (например, 0.01 для 1%)" />
                            <x-text-input id="terms" class="block mt-1 w-full" type="number" step="0.001" min="0" name="terms" :value="old('terms')" />
                            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Введите условия как десятичное число (например, 0.05 для 5%).</p>
                        </div>

                        <!-- Contact Info (Only for Agencies) -->
                        <div class="mt-4" x-show="role === 'agency'" x-transition>
                            <x-input-label for="contact_info" value="Контактная информация" />
                            <textarea id="contact_info" name="contact_info" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('contact_info') }}</textarea>
                            <x-input-error :messages="$errors->get('contact_info')" class="mt-2" />
                        </div>

                        <!-- Active Status (Visible for all) -->
                        <div class="block mt-4">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', 1) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Активен</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>

                        {{-- Note: is_virtual is handled automatically based on role in the backend --}}

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                Отмена
                            </a>

                            <x-primary-button>
                                Создать пользователя
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 