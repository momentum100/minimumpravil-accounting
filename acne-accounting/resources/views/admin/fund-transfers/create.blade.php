<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Fund Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                            x-ref="fromAccountSelect"
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
                                            x-ref="toAccountSelect"
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

    {{-- Recent Transfers Table Section --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Recent Fund Transfers') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">From Account</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">To Account</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description/Comment</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">View</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($recentTransfers as $transaction)
                                    @if ($transaction->operation) {{-- Check if operation (FundTransfer) exists --}}
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $transaction->operation->fromAccount->description ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $transaction->operation->toAccount->description ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">{{ number_format($transaction->operation->amount, 2) }} {{ $transaction->operation->currency }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $transaction->operation->comment ?: $transaction->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">View</a>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No recent fund transfers found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $recentTransfers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- End Recent Transfers Table Section --}}

    <script>
        function fundTransferForm() {
            return {
                fromUserId: '{{ old("from_user_id", $defaultFromUserId ?? '') }}',
                fromAccountId: '{{ old("from_account_id") }}',
                fromAccounts: [],
                loadingFromAccounts: false,
                toUserId: '{{ old("to_user_id") }}',
                toAccountId: '{{ old("to_account_id") }}',
                toAccounts: [],
                loadingToAccounts: false,

                init() {
                    console.log('Init - Initial fromUserId:', this.fromUserId);
                    console.log('Init - Initial toUserId:', this.toUserId);
                    // Fetch initial accounts if user IDs are pre-selected (e.g., validation failed)
                    if (this.fromUserId) {
                        console.log('Init - Calling fetchFromAccounts for user:', this.fromUserId);
                        this.fetchFromAccounts();
                    }
                    if (this.toUserId) {
                        console.log('Init - Calling fetchToAccounts for user:', this.toUserId);
                        this.fetchToAccounts();
                    }
                },

                fetchFromAccounts() {
                    console.log('fetchFromAccounts - Started');
                    if (!this.fromUserId) {
                        console.log('fetchFromAccounts - No fromUserId, resetting.');
                        this.fromAccounts = [];
                        this.fromAccountId = '';
                        return;
                    }
                    console.log('fetchFromAccounts - Fetching for user ID:', this.fromUserId);
                    this.loadingFromAccounts = true;
                    this.fromAccounts = []; // Clear previous options
                    this.fromAccountId = ''; // Reset selection

                    // Construct URL carefully
                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.fromUserId);
                    console.log('fetchFromAccounts - Fetch URL:', url);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            console.log('fetchFromAccounts - Received data:', data);
                            this.fromAccounts = data;
                            if (this.fromAccounts.length > 0) {
                                this.fromAccountId = this.fromAccounts[0].id;
                                console.log('fetchFromAccounts - Setting default fromAccountId model:', this.fromAccountId);
                                // Use $nextTick to ensure options are rendered before setting value
                                this.$nextTick(() => {
                                    if (this.$refs.fromAccountSelect) {
                                        this.$refs.fromAccountSelect.value = this.fromAccountId;
                                        console.log('fetchFromAccounts - $nextTick: Forcing select value to:', this.$refs.fromAccountSelect.value);
                                    } else {
                                        console.error('fetchFromAccounts - $nextTick: $refs.fromAccountSelect not found');
                                    }
                                });
                            } else {
                                console.log('fetchFromAccounts - No accounts received.');
                                this.fromAccountId = ''; // Reset if no accounts
                                this.$nextTick(() => { // Also reset select value if no accounts
                                    if (this.$refs.fromAccountSelect) this.$refs.fromAccountSelect.value = '';
                                });
                            }
                        })
                        .catch(error => console.error('Error fetching from accounts:', error))
                        .finally(() => {
                            this.loadingFromAccounts = false;
                            // Attempt to re-select old value if present after loading - REMOVED
                            // this.$nextTick(() => { this.fromAccountId = '{{ old("from_account_id") }}'; });
                        });
                },

                fetchToAccounts() {
                     console.log('fetchToAccounts - Started');
                     if (!this.toUserId) {
                        console.log('fetchToAccounts - No toUserId, resetting.');
                        this.toAccounts = [];
                        this.toAccountId = '';
                        return;
                    }
                    console.log('fetchToAccounts - Fetching for user ID:', this.toUserId);
                    this.loadingToAccounts = true;
                    this.toAccounts = []; // Clear previous options
                    this.toAccountId = ''; // Reset selection

                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.toUserId);
                    console.log('fetchToAccounts - Fetch URL:', url);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            console.log('fetchToAccounts - Received data:', data);
                            this.toAccounts = data;
                            if (this.toAccounts.length > 0) {
                                this.toAccountId = this.toAccounts[0].id;
                                console.log('fetchToAccounts - Setting default toAccountId model:', this.toAccountId);
                                // Use $nextTick to ensure options are rendered before setting value
                                this.$nextTick(() => {
                                    if (this.$refs.toAccountSelect) {
                                        this.$refs.toAccountSelect.value = this.toAccountId;
                                        console.log('fetchToAccounts - $nextTick: Forcing select value to:', this.$refs.toAccountSelect.value);
                                    } else {
                                        console.error('fetchToAccounts - $nextTick: $refs.toAccountSelect not found');
                                    }
                                });
                            } else {
                                console.log('fetchToAccounts - No accounts received.');
                                this.toAccountId = ''; // Reset if no accounts
                                this.$nextTick(() => { // Also reset select value if no accounts
                                    if (this.$refs.toAccountSelect) this.$refs.toAccountSelect.value = '';
                                });
                            }
                        })
                         .catch(error => console.error('Error fetching to accounts:', error))
                        .finally(() => {
                            this.loadingToAccounts = false;
                            // Attempt to re-select old value if present after loading - REMOVED
                            // this.$nextTick(() => { this.toAccountId = '{{ old("to_account_id") }}'; });
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