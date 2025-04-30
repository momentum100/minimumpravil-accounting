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
                                    <x-input-label for="from_user_id" value="From User" />
                                    <select id="from_user_id" name="from_user_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" x-model="fromUserId" @change="fetchFromAccounts(); checkCommission();" required size="10">
                                        <option value="">-- Select User --</option>
                                        @foreach ($fromUsers as $user)
                                            <option value="{{ $user->id }}" {{ old('from_user_id', $defaultFromUserId ?? '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} @if($user->role && $user->role !== 'System') - {{ ucfirst($user->role) }} (Terms: {{ number_format($user->terms * 100, 1) }}%) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('from_user_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="from_account_id" value="From Account (USD)" />
                                    <div class="relative">
                                        <select id="from_account_id" name="from_account_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm disabled:opacity-50" x-model="fromAccountId" required x-bind:disabled="loadingFromAccounts || fromAccounts.length === 0">
                                            <template x-if="loadingFromAccounts">
                                                <option value="">Loading accounts...</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length === 0 && fromUserId">
                                                <option value="">No USD accounts found for selected user.</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length === 0 && !fromUserId">
                                                <option value="">Select a user first</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length > 0">
                                                <option value="">-- Select Account --</option>
                                            </template>
                                            <template x-for="account in fromAccounts" :key="account.id">
                                                <option :value="account.id" x-text="account.description + ' (' + account.account_number + ') - Bal: $' + parseFloat(account.balance).toFixed(2)"></option>
                                            </template>
                                        </select>
                                        <div x-show="loadingFromAccounts" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                             <!-- Basic Spinner -->
                                             <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                              </svg>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('from_account_id')" class="mt-2" />
                                </div>
                            </div>

                            {{-- To User/Account --}}
                            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                 <h3 class="text-lg font-medium">To</h3>
                                <div>
                                    <x-input-label for="to_user_id" value="To User" />
                                    <select id="to_user_id" name="to_user_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" x-model="toUserId" @change="fetchToAccounts" required size="10">
                                        <option value="">-- Select User --</option>
                                        @foreach ($toUsers as $user)
                                            <option value="{{ $user->id }}" {{ old('to_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('to_user_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="to_account_id" value="Account (USD)" />
                                    <select id="to_account_id" name="to_account_id"
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
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" required step="0.01" min="0.01"
                                          x-model.number="amountValue" @input="updateCalculatedTotal" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        {{-- Add Commission Checkbox (Conditional) - Use Component --}}
                        <x-commission-checkbox
                            show="showCommissionCheckbox"          {{-- Alpine var for visibility --}}
                            rate-percent="commissionRatePercent"  {{-- Alpine var for rate --}}
                            checked="addCommissionChecked"        {{-- Alpine var for x-model --}}
                            @change="updateCalculatedTotal"       {{-- Pass through the @change handler --}}
                        />

                        {{-- Calculated Total Amount Display (Conditional) --}}
                        <div x-show="showCommissionCheckbox && amountValue > 0" class="mt-2 text-sm text-gray-700 dark:text-gray-300"> {{-- Hide if amount is 0 --}}
                            <strong>Calculated Total: $ <span x-text="calculatedTotalAmount"></span></strong>
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

    {{-- Recent Transfers Table Section - Use Component --}}
    <x-recent-transfers-table :transfers="$recentTransfers" />

    <script>
        function fundTransferForm() {
            // Pass the necessary PHP data to Alpine
            const fromUsersData = @json($fromUsersData->keyBy('id'));
            const initialAmount = parseFloat('{{ old("amount", 0) }}') || 0;
            // Set initialAddCommission to true by default, but allow old() to override if validation failed
            const initialAddCommission = {{ old('add_commission') === '0' ? 'false' : 'true' }};

            return {
                fromUserId: '{{ old("from_user_id", $defaultFromUserId ?? '') }}',
                fromAccountId: '{{ old("from_account_id") }}',
                fromAccounts: [],
                loadingFromAccounts: false,
                toUserId: '{{ old("to_user_id") }}',
                toAccountId: '{{ old("to_account_id") }}',
                toAccounts: [],
                loadingToAccounts: false,
                showCommissionCheckbox: false,
                commissionRatePercent: 0,
                allFromUsersData: fromUsersData,
                amountValue: initialAmount,
                addCommissionChecked: initialAddCommission,
                calculatedTotalAmount: initialAmount.toFixed(2),

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
                    // Initial check for commission on page load (if old('from_user_id') is set)
                    this.checkCommission();
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

                    // Also check if commission should be shown for the newly selected user
                    // this.checkCommission(); // Moved to the @change handler to ensure it runs after model updates

                    // Construct URL carefully
                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.fromUserId);
                    console.log('fetchFromAccounts - Fetch URL:', url);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            console.log('fetchFromAccounts - Received data:', data);
                            this.fromAccounts = data;
                            // If there was a previously selected account ID (e.g., from validation error)
                            // try to keep it selected if it exists in the new list
                            if (savedFromAccountId && this.fromAccounts.some(acc => acc.id == savedFromAccountId)) {
                                this.fromAccountId = savedFromAccountId;
                                console.log('fetchFromAccounts - Restored fromAccountId:', this.fromAccountId);
                            }
                        })
                        .catch(error => {
                            console.error('fetchFromAccounts - Error:', error);
                            // Optionally show an error message to the user
                        })
                        .finally(() => {
                            this.loadingFromAccounts = false;
                            console.log('fetchFromAccounts - Finished');
                            // Check commission *after* accounts are fetched and loading state is false
                            // this.checkCommission(); // Moved to @change handler
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
                            console.log('fetchToAccounts - Finished');
                        });
                },

                // New method to check if commission checkbox should be shown
                checkCommission() {
                    if (!this.fromUserId) {
                        this.showCommissionCheckbox = false;
                        return;
                    }
                    console.log('checkCommission - Checking user ID:', this.fromUserId);
                    const selectedUser = this.allFromUsersData[this.fromUserId];
                    console.log('checkCommission - Selected user data:', selectedUser);

                     // Define the role(s) that trigger the commission checkbox
                     const agencyRoles = ['agency']; // Use lowercase for comparison

                    if (selectedUser && selectedUser.role && agencyRoles.includes(selectedUser.role.toLowerCase())) {
                        console.log('checkCommission - Role is Agency, showing checkbox.');
                        this.showCommissionCheckbox = true;
                         // Calculate and store percentage for display
                        this.commissionRatePercent = selectedUser.terms ? (parseFloat(selectedUser.terms) * 100).toFixed(1) : 0;
                         console.log('checkCommission - Commission rate set to:', this.commissionRatePercent);
                    } else {
                        console.log('checkCommission - Role is not Agency or no user data, hiding checkbox.');
                        this.showCommissionCheckbox = false;
                        this.commissionRatePercent = 0;
                    }
                },

                updateCalculatedTotal() {
                    console.log('updateCalculatedTotal - Started');
                    let total = this.amountValue;

                    if (this.addCommissionChecked) {
                        console.log('Calculated Total with commission:', total);
                        total += (total * (this.commissionRatePercent / 100));
                        console.log('Calculated Total with commission:', total);
                    } else {
                        console.log('Calculated Total without commission:', total);
                    }

                    // Format as USD currency
                    this.calculatedTotalAmount = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                submitForm(event) {
                    console.log('Submitting form...');
                    event.target.submit(); // Use standard form submission
                }
            }
        }
    </script>
</x-app-layout> 