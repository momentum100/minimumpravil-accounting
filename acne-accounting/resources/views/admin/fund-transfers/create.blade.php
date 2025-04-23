<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Fund Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.fund-transfers.store') }}"
                          x-data="fundTransferForm()"
                          @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- From User/Account --}}
                            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                <h3 class="text-lg font-medium">From</h3>
                                <div>
                                    <x-input-label for="from_user_id" value="User" />
                                    <select id="from_user_id" name="from_user_id"
                                            x-model="fromUserId"
                                            @change="fetchFromAccounts"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select User...</option>
                                        @foreach($fromUsers as $user)
                                            <option value="{{ $user->id }}" {{ old('from_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} {{ $user->is_virtual ? '(System)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('from_user_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="from_account_id" value="Account (USD)" />
                                    <select id="from_account_id" name="from_account_id"
                                            x-model="fromAccountId"
                                            :disabled="!fromUserId || loadingFromAccounts"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm disabled:opacity-50">
                                        <option value="" x-show="!fromUserId || loadingFromAccounts">{{ __('Select User First...') }}</option>
                                        <template x-if="fromUserId && !loadingFromAccounts">
                                             <template x-for="account in fromAccounts" :key="account.id">
                                                <option :value="account.id" x-text="`${account.description} (${account.account_type})`"></option>
                                            </template>
                                        </template>
                                         <option value="" x-show="fromUserId && !loadingFromAccounts && fromAccounts.length === 0">{{ __('No USD accounts found') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('from_account_id')" class="mt-2" />
                                </div>
                            </div>

                            {{-- To User/Account --}}
                            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                 <h3 class="text-lg font-medium">To</h3>
                                <div>
                                    <x-input-label for="to_user_id" value="User" />
                                    <select id="to_user_id" name="to_user_id"
                                            x-model="toUserId"
                                            @change="fetchToAccounts"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select User...</option>
                                         @foreach($toUsers as $user)
                                            <option value="{{ $user->id }}" {{ old('to_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('to_user_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="to_account_id" value="Account (USD)" />
                                    <select id="to_account_id" name="to_account_id"
                                            x-model="toAccountId"
                                            :disabled="!toUserId || loadingToAccounts"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm disabled:opacity-50">
                                        <option value="" x-show="!toUserId || loadingToAccounts">{{ __('Select User First...') }}</option>
                                        <template x-if="toUserId && !loadingToAccounts">
                                            <template x-for="account in toAccounts" :key="account.id">
                                                <option :value="account.id" x-text="`${account.description} (${account.account_type})`"></option>
                                            </template>
                                        </template>
                                        <option value="" x-show="toUserId && !loadingToAccounts && toAccounts.length === 0">{{ __('No USD accounts found') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('to_account_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                         {{-- Amount --}}
                        <div class="mt-6">
                            <x-input-label for="amount" value="Amount (USD)" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount')" required step="0.01" min="0.01" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div class="mt-4">
                            <x-input-label for="description" value="Description" />
                            <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description')" required />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.dashboard') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button x-bind:disabled="loadingFromAccounts || loadingToAccounts">
                                <span x-show="loadingFromAccounts || loadingToAccounts">Loading...</span>
                                <span x-show="!loadingFromAccounts && !loadingToAccounts">{{ __('Create Transfer') }}</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fundTransferForm() {
            return {
                fromUserId: '{{ old("from_user_id") }}',
                fromAccountId: '{{ old("from_account_id") }}',
                fromAccounts: [],
                loadingFromAccounts: false,
                toUserId: '{{ old("to_user_id") }}',
                toAccountId: '{{ old("to_account_id") }}',
                toAccounts: [],
                loadingToAccounts: false,

                init() {
                    // Fetch initial accounts if user IDs are pre-selected (e.g., validation failed)
                    if (this.fromUserId) {
                        this.fetchFromAccounts();
                    }
                    if (this.toUserId) {
                        this.fetchToAccounts();
                    }
                },

                fetchFromAccounts() {
                    if (!this.fromUserId) {
                        this.fromAccounts = [];
                        this.fromAccountId = '';
                        return;
                    }
                    this.loadingFromAccounts = true;
                    this.fromAccounts = []; // Clear previous options
                    this.fromAccountId = ''; // Reset selection

                    // Construct URL carefully
                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.fromUserId);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            this.fromAccounts = data;
                            // If accounts were found, select the first one by default
                            if (this.fromAccounts.length > 0) {
                                this.fromAccountId = this.fromAccounts[0].id;
                            }
                        })
                        .catch(error => console.error('Error fetching from accounts:', error))
                        .finally(() => {
                            this.loadingFromAccounts = false;
                            // Attempt to re-select old value if present after loading
                            this.$nextTick(() => { this.fromAccountId = '{{ old("from_account_id") }}'; });
                        });
                },

                fetchToAccounts() {
                     if (!this.toUserId) {
                        this.toAccounts = [];
                        this.toAccountId = '';
                        return;
                    }
                    this.loadingToAccounts = true;
                    this.toAccounts = []; // Clear previous options
                    this.toAccountId = ''; // Reset selection

                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.toUserId);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            this.toAccounts = data;
                            // If accounts were found, select the first one by default
                            if (this.toAccounts.length > 0) {
                                this.toAccountId = this.toAccounts[0].id;
                            }
                        })
                         .catch(error => console.error('Error fetching to accounts:', error))
                        .finally(() => {
                            this.loadingToAccounts = false;
                            // Attempt to re-select old value if present after loading
                            this.$nextTick(() => { this.toAccountId = '{{ old("to_account_id") }}'; });
                        });
                },

                // Handle form submission via Alpine if needed, or let default form action work
                 submitForm(event) {
                    event.target.submit(); // Use standard form submission
                }
            }
        }
    </script>
</x-app-layout> 