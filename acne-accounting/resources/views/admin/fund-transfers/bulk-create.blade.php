<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Создать массовые переводы средств
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="bulkFundTransferForm()">

                    <x-auth-session-status class="mb-4" :status="session('status')" />
                     {{-- General Error Display --}}
                    <div x-show="generalError" class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded" x-text="generalError"></div>


                    <form @submit.prevent="handleFormSubmit">
                        @csrf {{-- Include CSRF token --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- From User/Account (Mostly reused from single transfer) --}}
                            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                <h3 class="text-lg font-medium">От</h3>
                                <div>
                                    <x-input-label for="from_user_id" value="От пользователя" />
                                    <select id="from_user_id" name="from_user_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" x-model="fromUserId" @change="fetchFromAccounts(); checkCommission();" required size="10">
                                        <option value="">-- Выберите пользователя --</option>
                                        @foreach ($fromUsers as $user)
                                            @if($user->role === 'separator')
                                                <option disabled>{{ $user->name }}</option>
                                            @else
                                                <option value="{{ $user->id }}" {{ $defaultFromUserId == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} @if($user->role && $user->role !== 'System') - {{ ucfirst($user->role) }} (Условия: {{ number_format($user->terms * 100, 1) }}%) @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    {{-- Consider adding x-input-error here if needed --}}
                                </div>
                                <div>
                                    <x-input-label for="from_account_id" value="От счета (USD)" />
                                    <div class="relative">
                                        <select id="from_account_id" name="from_account_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm disabled:opacity-50" x-model="fromAccountId" required x-bind:disabled="loadingFromAccounts || fromAccounts.length === 0">
                                            <template x-if="loadingFromAccounts">
                                                <option value="">Загрузка счетов...</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length === 0 && fromUserId">
                                                <option value="">USD счета не найдены для выбранного пользователя.</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length === 0 && !fromUserId">
                                                <option value="">Сначала выберите пользователя</option>
                                            </template>
                                            <template x-if="!loadingFromAccounts && fromAccounts.length > 0">
                                                <option value="">-- Выберите счет --</option>
                                            </template>
                                            <template x-for="account in fromAccounts" :key="account.id">
                                                <option :value="account.id" x-text="account.description + ' (' + account.account_number + ') - Баланс: $' + parseFloat(account.balance).toFixed(2)"></option>
                                            </template>
                                        </select>
                                        <div x-show="loadingFromAccounts" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                             <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                              </svg>
                                        </div>
                                    </div>
                                     {{-- Consider adding x-input-error here if needed --}}
                                </div>
                            </div>

                            {{-- Bulk Input Section --}}
                            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                <h3 class="text-lg font-medium">Кому (Массовый ввод)</h3>
                                <div>
                                    <x-input-label for="bulk_input" value="Получатели и суммы" />
                                    <textarea id="bulk_input" name="bulk_input"
                                              class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                              x-model="bulkInput"
                                              rows="15"
                                              placeholder="Вводите каждого получателя на новой строке:
username1 100.50 [Необязательное описание]
username2 75 [Другое описание]
petya 200"
                                              @input="parseBulkInput"></textarea>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Формат: `имя_пользователя сумма [необязательное описание]` на строку.</p>
                                     {{-- Error display for parsing issues? --}}
                                </div>

                                {{-- Description (Applies to all if no line description) --}}
                                <div class="mt-4">
                                    <x-input-label for="description" value="Описание по умолчанию (Необязательно)" />
                                    <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" x-model="defaultDescription" />
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Это описание будет использоваться, если у строки нет собственного описания.</p>
                                </div>

                                {{-- Add Commission Checkbox (Conditional) - Use Component --}}
                                <x-commission-checkbox
                                    show="showCommissionCheckbox"          {{-- Alpine var for visibility --}}
                                    rate-percent="commissionRatePercent"  {{-- Alpine var for rate --}}
                                    checked="applyCommission"             {{-- Alpine var for x-model (use new var) --}}
                                    {{-- No @change needed here as calculation happens on submit --}}
                                />

                            </div>
                        </div>

                        {{-- Parsed Transfers Table --}}
                        <div class="mt-8" x-show="parsedTransfers.length > 0">
                             <h3 class="text-lg font-medium mb-4">Переводы к обработке (<span x-text="parsedTransfers.length"></span>)</h3>
                             <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Имя получателя</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Сумма (USD)</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Описание</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(transfer, index) in parsedTransfers" :key="transfer.uniqueId">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="index + 1"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="transfer.username"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100" x-text="transfer.amount.toFixed(2)"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="transfer.description || defaultDescription"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <span x-text="transfer.status" :class="{
                                                        'text-gray-500 dark:text-gray-400': transfer.status === 'В ожидании',
                                                        'text-yellow-600 dark:text-yellow-400': transfer.status === 'Обрабатывается',
                                                        'text-green-600 dark:text-green-400': transfer.status === 'Успешно',
                                                        'text-red-600 dark:text-red-400': transfer.status.startsWith('Ошибка')
                                                    }"></span>
                                                     <template x-if="transfer.status.startsWith('Ошибка')">
                                                        <p class="text-xs text-red-500" x-text="transfer.message"></p>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.dashboard') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                Отмена
                            </a>
                            <x-primary-button
                                type="submit"
                                x-bind:disabled="isProcessing || isCheckingUsers || loadingFromAccounts || parsedTransfers.length === 0 || !fromAccountId || hasErrors">
                                <span x-show="isProcessing">Обрабатывается...</span>
                                <span x-show="isCheckingUsers && !isProcessing">Проверка пользователей...</span>
                                <span x-show="!isProcessing && !isCheckingUsers">Обработать массовые переводы</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bulkFundTransferForm() {
            console.log('[bulkFundTransferForm] Defining function...');
            const fromUsersData = @json($fromUsersData->keyBy('id'));

            return {
                fromUserId: '{{ $defaultFromUserId ?? '' }}',
                fromAccountId: '',
                fromAccounts: [],
                loadingFromAccounts: false,
                allFromUsersData: fromUsersData,
                commissionRatePercent: 0,
                showCommissionCheckbox: false,
                applyCommission: true,
                bulkInput: '',
                parsedTransfers: [],
                defaultDescription: '',
                isProcessing: false,
                isCheckingUsers: false,
                hasErrors: false,
                generalError: '',
                debounceTimeout: null,

                init() {
                    console.log('Bulk Init - Initial fromUserId:', this.fromUserId);
                    if (this.fromUserId) {
                        console.log('Bulk Init - Calling fetchFromAccounts for user:', this.fromUserId);
                        this.fetchFromAccounts();
                    }
                    this.checkCommission();

                    // Watch relevant state for button disabled status
                    this.$watch('isProcessing', (value) => this.logButtonState('isProcessing', value));
                    this.$watch('isCheckingUsers', (value) => this.logButtonState('isCheckingUsers', value));
                    this.$watch('loadingFromAccounts', (value) => this.logButtonState('loadingFromAccounts', value));
                    this.$watch('fromAccountId', (value) => this.logButtonState('fromAccountId', value));
                    this.$watch('hasErrors', (value) => this.logButtonState('hasErrors', value));
                    this.$watch('parsedTransfers', (value) => this.logButtonState('parsedTransfers', value.length));
                    this.logButtonState('init', null);
                },

                fetchFromAccounts() {
                    console.log('fetchFromAccounts - Started');
                    this.generalError = '';
                    if (!this.fromUserId) {
                        console.log('fetchFromAccounts - No fromUserId, resetting.');
                        this.fromAccounts = [];
                        this.fromAccountId = '';
                        return;
                    }
                    console.log('fetchFromAccounts - Fetching for user ID:', this.fromUserId);
                    this.loadingFromAccounts = true;
                    this.fromAccounts = [];
                    this.fromAccountId = '';

                    const url = `{{ route('admin.users.accounts', ':userId') }}`.replace(':userId', this.fromUserId);
                    console.log('fetchFromAccounts - Fetch URL:', url);

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok loading accounts.');
                            }
                            return response.json();
                         })
                        .then(data => {
                            console.log('fetchFromAccounts - Received data:', data);
                            this.fromAccounts = data.map(acc => ({
                                ...acc,
                                balance: acc.balance !== undefined ? parseFloat(acc.balance) : 0
                             }));
                             if (this.fromAccounts.length > 0) {
                                 this.fromAccountId = this.fromAccounts[0].id;
                                 console.log('fetchFromAccounts - Automatically selected first account ID:', this.fromAccountId);
                             } else {
                                 this.fromAccountId = '';
                             }
                        })
                        .catch(error => {
                            console.error('fetchFromAccounts - Error:', error);
                            this.generalError = 'Failed to load accounts for the selected user. Please try again.';
                        })
                        .finally(() => {
                            this.loadingFromAccounts = false;
                            console.log('fetchFromAccounts - Finished');
                        });
                },

                checkCommission() {
                    if (!this.fromUserId) {
                        this.showCommissionCheckbox = false; return;
                    }
                    const selectedUser = this.allFromUsersData[this.fromUserId];
                    const agencyRoles = ['agency'];
                    if (selectedUser && selectedUser.role && agencyRoles.includes(selectedUser.role.toLowerCase())) {
                         this.showCommissionCheckbox = true;
                        this.commissionRatePercent = selectedUser.terms ? (parseFloat(selectedUser.terms) * 100).toFixed(1) : 0;
                        console.log('checkCommission - Agency found, rate:', this.commissionRatePercent);
                    } else {
                        this.showCommissionCheckbox = false;
                        this.commissionRatePercent = 0;
                         console.log('checkCommission - Not an agency or no user data.');
                    }
                },

                parseBulkInput() {
                    const lines = this.bulkInput.trim().split('\n');
                    const transfers = [];
                    const uniqueIdBase = Date.now();

                    lines.forEach((line, index) => {
                        if (!line.trim()) return;
                        const match = line.trim().match(/^(\S+)\s+([\d\.]+)\s*(.*)$/);
                        const uniqueId = `${uniqueIdBase}-${index}`;

                        if (match) {
                            const username = match[1];
                            const amount = parseFloat(match[2]);
                            const description = match[3] ? match[3].trim() : null;

                            if (!isNaN(amount) && amount > 0) {
                                transfers.push({
                                    uniqueId: uniqueId,
                                    username: username,
                                    amount: amount,
                                    description: description,
                                    status: 'В ожидании',
                                    message: ''
                                });
                            } else {
                                transfers.push({ uniqueId: `${uniqueId}-invalidAmount`, username: username, amount: NaN, description: description, status: 'Ошибка (Parse)', message: 'Invalid amount format.' });
                            }
                        } else {
                            transfers.push({ uniqueId: `${uniqueId}-invalidLine`, username: line.trim().split(' ')[0] || '?', amount: NaN, description: '', status: 'Ошибка (Parse)', message: 'Invalid line format. Use: username amount [description]' });
                        }
                    });
                    this.parsedTransfers = transfers;
                     console.log('Parsed Transfers (Input Event):', this.parsedTransfers);
                    this.generalError = '';
                    this.hasErrors = this.parsedTransfers.some(t => t.status.startsWith('Ошибка'));

                    clearTimeout(this.debounceTimeout);
                    this.debounceTimeout = setTimeout(() => {
                        console.log('Debounce triggered: Calling checkUserExistence');
                        this.checkUserExistence();
                    }, 500);
                },

                async checkUserExistence() {
                    const validParsedTransfers = this.parsedTransfers.filter(t => !t.status.startsWith('Ошибка'));
                    const usernamesToCheck = [ ...new Set(validParsedTransfers.map(t => t.username)) ];

                    if (usernamesToCheck.length === 0) {
                        console.log('No valid usernames to check.');
                        return;
                    }

                    this.isCheckingUsers = true;
                    this.hasErrors = true;
                    console.log('Checking existence for usernames:', usernamesToCheck);

                    try {
                         const response = await fetch('{{ route("admin.users.check") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ usernames: usernamesToCheck })
                        });

                        if (!response.ok) {
                            throw new Error(`Server error ${response.status} checking users.`);
                        }

                        const result = await response.json();
                        const existenceMap = result.exists;
                        console.log('User existence map received:', existenceMap);

                        this.parsedTransfers.forEach(transfer => {
                            if (transfer.status === 'В ожидании' && existenceMap.hasOwnProperty(transfer.username)) {
                                if (!existenceMap[transfer.username]) {
                                    transfer.status = 'Ошибка (User NF)';
                                    transfer.message = 'Recipient username not found.';
                                    console.warn(`User not found: ${transfer.username}`);
                                }
                            }
                        });

                    } catch (error) {
                        console.error('Error checking user existence:', error);
                        this.generalError = 'Failed to verify recipient usernames. Check network or try again.';
                        this.parsedTransfers.forEach(transfer => {
                            if (transfer.status === 'В ожидании') {
                                transfer.status = 'Ошибка (Check Fail)';
                                transfer.message = 'Could not verify user existence.';
                            }
                        });
                    } finally {
                        this.isCheckingUsers = false;
                        this.hasErrors = this.parsedTransfers.some(t => t.status.startsWith('Ошибка'));
                        console.log('User check finished. Has errors:', this.hasErrors);
                    }
                },

                logButtonState(trigger, value) {
                    const isDisabled = this.isProcessing || this.isCheckingUsers || this.loadingFromAccounts || this.parsedTransfers.length === 0 || !this.fromAccountId || this.hasErrors;
                    console.log(`[Button State Log - Trigger: ${trigger}]`, {
                        isDisabled: isDisabled,
                        isProcessing: this.isProcessing,
                        isCheckingUsers: this.isCheckingUsers,
                        loadingFromAccounts: this.loadingFromAccounts,
                        parsedTransfersLength: this.parsedTransfers.length,
                        fromAccountId: this.fromAccountId,
                        hasErrors: this.hasErrors
                    });
                },

                handleFormSubmit() {
                    console.log('[handleFormSubmit] Form submitted! Attempting to call processBulkTransfers...');
                    try {
                        this.processBulkTransfers();
                    } catch (error) {
                        console.error('[handleFormSubmit] Error calling processBulkTransfers:', error);
                        this.generalError = 'A critical error occurred trying to start processing. Check console.';
                    }
                },

                async processBulkTransfers() {
                    console.log('[processBulkTransfers] Attempting to start...');
                    console.log('[processBulkTransfers] State: fromAccountId=', this.fromAccountId, 'hasErrors=', this.hasErrors, 'isProcessing=', this.isProcessing, 'isCheckingUsers=', this.isCheckingUsers, 'parsedTransfers Count=', this.parsedTransfers.length);

                    if (this.hasErrors) {
                        this.generalError = 'Please fix the errors highlighted in the table before processing.';
                        console.error('[processBulkTransfers] EXIT: Errors found (hasErrors=true).', this.parsedTransfers.filter(t => t.status.startsWith('Ошибка')));
                        return;
                    }
                     if (!this.fromAccountId) {
                         this.generalError = 'Please select a From Account.';
                         console.error('[processBulkTransfers] EXIT: No From Account selected.');
                         return;
                     }
                     let validTransfers = this.parsedTransfers.filter(t => !t.status.startsWith('Ошибка'));
                     if (validTransfers.length === 0) {
                         this.generalError = 'No valid transfers to process.';
                         console.error('[processBulkTransfers] EXIT: No valid transfers in the list after filtering.');
                         return;
                     }
                     if (this.isProcessing) {
                         console.warn('[processBulkTransfers] EXIT: Already processing (isProcessing=true).');
                         this.generalError = 'Processing already in progress.';
                         return;
                     }
                     if (this.isCheckingUsers) {
                         console.warn('[processBulkTransfers] EXIT: Still checking users (isCheckingUsers=true).');
                         this.generalError = 'Still checking users... Please wait.';
                         return;
                     }
                     console.log('[processBulkTransfers] Prerequisites met. Starting processing...');
                     this.isProcessing = true;
                     this.generalError = '';
                     validTransfers.forEach(t => this.updateTransferStatus(t.uniqueId, 'В ожидании'));
                     console.log(`Processing ${validTransfers.length} transfers.`);
                     for (const transfer of validTransfers) {
                         console.log(`[Loop Start] Processing transfer ID: ${transfer.uniqueId}, User: ${transfer.username}, Amount: ${transfer.amount}`);
                         this.updateTransferStatus(transfer.uniqueId, 'Обрабатывается');
                         console.log(`[Loop] Calculating amountToSend for ${transfer.uniqueId}...`);
                         let amountToSend = transfer.amount;
                         let commissionApplied = false;
                         if (this.applyCommission && this.showCommissionCheckbox && this.commissionRatePercent > 0) {
                             const commission = amountToSend * (this.commissionRatePercent / 100);
                             amountToSend += commission;
                             commissionApplied = true;
                             console.log(`[Loop] Applying ${this.commissionRatePercent}% commission. Sending: ${amountToSend.toFixed(2)}`);
                         } else {
                              console.log(`[Loop] No commission applied. Sending: ${amountToSend.toFixed(2)}`);
                         }
                         console.log(`[Loop] Checking CSRF token for ${transfer.uniqueId}...`);
                         const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                         if (!csrfToken) {
                              console.error(`[Loop EXIT] CSRF token not found for ${transfer.uniqueId}.`);
                              this.updateTransferStatus(transfer.uniqueId, 'Ошибка (Setup)', 'CSRF token missing.');
                              this.generalError = 'Configuration error: CSRF token missing.';
                              this.isProcessing = false;
                              return;
                          }
                          console.log(`[Loop] CSRF token found for ${transfer.uniqueId}.`);
                          const payload = {
                              from_account_id: this.fromAccountId,
                              to_username: transfer.username,
                              amount: parseFloat(amountToSend.toFixed(2)),
                              description: transfer.description || this.defaultDescription,
                              unique_id: transfer.uniqueId
                          };
                          console.log(`[Loop] Preparing fetch for ${transfer.uniqueId} with payload:`, payload);
                          try {
                               console.log(`[Loop] >>> Attempting fetch for ${transfer.uniqueId}...`);
                               const response = await fetch('{{ route("admin.fund-transfers.bulk.store") }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': csrfToken,
                                     'Accept': 'application/json',
                                 },
                                 body: JSON.stringify(payload)
                             });
                               console.log(`[Loop] <<< Fetch completed for ${transfer.uniqueId}. Status: ${response.status}`);
                              let result = {};
                              try {
                                 result = await response.json();
                                 console.log(`Parsed response for ${transfer.uniqueId}:`, result);
                              } catch (jsonError) {
                                 console.error(`Failed to parse JSON response for ${transfer.uniqueId}:`, jsonError);
                                 throw new Error(`Invalid JSON response from server (Status: ${response.status})`);
                              }
                             const resultUniqueId = result.unique_id || transfer.uniqueId;
                             if (!response.ok || !result.success) {
                                 const errorMsg = result.message || `API Error (${response.status})`;
                                 console.error(`Error processing transfer ${resultUniqueId}:`, errorMsg, result);
                                 this.updateTransferStatus(resultUniqueId, `Ошибка (API)`, errorMsg);
                              } else {
                                 console.log(`Transfer ${resultUniqueId} success:`, result);
                                 this.updateTransferStatus(resultUniqueId, 'Успешно', result.message || 'Completed');
                             }
                         } catch (error) {
                             console.error(`Network/fetch error processing transfer ${transfer.uniqueId}:`, error);
                              this.updateTransferStatus(transfer.uniqueId, 'Ошибка (Network)', error.message || 'Could not communicate with the server.');
                         }
                         await new Promise(resolve => setTimeout(resolve, 150));
                     }
                     console.log('[processBulkTransfers] Bulk processing loop finished.');
                     this.isProcessing = false;
                     this.fetchFromAccounts();
                      console.log('[processBulkTransfers] Finished. isProcessing:', this.isProcessing);
                },
                updateTransferStatus(uniqueId, status, message = '') {
                    const index = this.parsedTransfers.findIndex(t => t.uniqueId === uniqueId);
                    if (index !== -1) {
                        this.parsedTransfers[index].status = status;
                        this.parsedTransfers[index].message = message;
                        if(status.startsWith('Ошибка') && !this.hasErrors) {
                            this.hasErrors = true;
                        }
                    }
                }
            }
        }
    </script>
    
     {{-- Add CSRF token meta tag if not already in main layout --}}
     @push('head')
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush

    {{-- Recent Transfers Table Section - Use Component --}}
    <x-recent-transfers-table :transfers="$recentTransfers" />

</x-app-layout> 