<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daily Expenses') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="dailyExpensesManager()" x-init="fetchExpenses()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Add Expense Form --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Add New Expense</h3>
                    <form @submit.prevent="addExpense" class="space-y-4 relative" x-ref="expenseForm">
                        @csrf {{-- Although not directly used by fetch, good practice to include --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="date" value="Date" />
                                {{-- Name still date for label/id, model is operation_date --}}
                                <x-text-input id="date" class="block mt-1 w-full" type="text" name="operation_date" x-ref="dateInput" x-model="newExpense.operation_date" required />
                                <p x-show="errors.operation_date" x-text="errors.operation_date ? errors.operation_date[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                            </div>
                            <div>
                                <x-input-label for="buyer_id" value="Buyer" />
                                <select id="buyer_id" name="buyer_id" x-model="newExpense.buyer_id" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">Select Buyer...</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.buyer_id" x-text="errors.buyer_id ? errors.buyer_id[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                            </div>
                             <div>
                                <x-input-label for="category" value="Category" />
                                <x-text-input id="category" class="block mt-1 w-full" type="text" name="category" x-model="newExpense.category" required />
                                <p x-show="errors.category" x-text="errors.category ? errors.category[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                             <div>
                                <x-input-label for="quantity" value="Quantity" />
                                <x-text-input id="quantity" class="block mt-1 w-full" type="number" step="0.01" name="quantity" x-model="newExpense.quantity" required />
                                <p x-show="errors.quantity" x-text="errors.quantity ? errors.quantity[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                            </div>
                             <div>
                                <x-input-label for="tariff" value="Tariff" />
                                <x-text-input id="tariff" class="block mt-1 w-full" type="number" step="0.01" name="tariff" x-model="newExpense.tariff" required />
                                <p x-show="errors.tariff" x-text="errors.tariff ? errors.tariff[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                            </div>
                            <div>
                                {{-- Total is calculated backend --}}
                                <x-input-label value="Total (Calculated)" />
                                <p class="mt-1 block w-full h-10 flex items-center px-3 bg-gray-100 dark:bg-gray-700 rounded-md shadow-sm text-gray-500 dark:text-gray-400" x-text="calculateTotal()"></p>
                            </div>
                        </div>
                        <div>
                            <x-input-label for="comment" value="Comment" />
                            <textarea id="comment" name="comment" rows="2" x-model="newExpense.comment"
                                      class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            ></textarea>
                            <p x-show="errors.comment" x-text="errors.comment ? errors.comment[0] : ''" class="text-sm text-red-600 dark:text-red-400 mt-1"></p>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button x-bind:disabled="isLoading">
                                <span x-show="!isLoading">{{ __('Add Expense') }}</span>
                                <span x-show="isLoading">Adding...</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Expenses Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                     <h3 class="text-lg font-medium mb-4">Existing Expenses</h3>

                     {{-- Loading Indicator --}}
                     <div x-show="isLoading && expenses.length === 0" class="text-center py-4">
                        Loading expenses...
                     </div>

                     {{-- Table --}}
                     <div class="relative overflow-x-auto shadow-md sm:rounded-lg" x-show="!isLoading || expenses.length > 0">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Buyer</th>
                                    <th scope="col" class="px-6 py-3">Category</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">Tariff</th>
                                    <th scope="col" class="px-6 py-3">Total</th>
                                    <th scope="col" class="px-6 py-3">Comment</th>
                                    <th scope="col" class="px-6 py-3">Created By</th>
                                    {{-- <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="expense in expenses" :key="expense.id">
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4" x-text="expense.operation_date"></td>
                                        <td class="px-6 py-4" x-text="expense.buyer?.name || '-'"></td>
                                        <td class="px-6 py-4" x-text="expense.category"></td>
                                        <td class="px-6 py-4 text-right" x-text="parseFloat(expense.quantity).toFixed(2)"></td>
                                        <td class="px-6 py-4 text-right" x-text="parseFloat(expense.tariff).toFixed(2)"></td>
                                        <td class="px-6 py-4 text-right" x-text="parseFloat(expense.total).toFixed(2)"></td>
                                        <td class="px-6 py-4" x-text="expense.comment ? expense.comment.substring(0, 50) + (expense.comment.length > 50 ? '...' : '') : '-'"></td>
                                        <td class="px-6 py-4" x-text="expense.creator?.name || '-'"></td>
                                        {{-- <td class="px-6 py-4 text-right"> --}}
                                            {{-- Edit/Delete buttons later --}}
                                        {{-- </td> --}}
                                    </tr>
                                </template>
                                <tr x-show="!isLoading && expenses.length === 0">
                                     <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No expenses found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4" x-show="pagination.total > pagination.per_page">
                        <nav class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                Showing
                                <span class="font-medium" x-text="pagination.from"></span>
                                to
                                <span class="font-medium" x-text="pagination.to"></span>
                                of
                                <span class="font-medium" x-text="pagination.total"></span>
                                results
                            </p>
                            <div class="flex justify-end">
                                <button @click="fetchExpenses(pagination.current_page - 1)" :disabled="!pagination.prev_page_url || isLoading" class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white mr-2">
                                    Previous
                                </button>
                                <button @click="fetchExpenses(pagination.current_page + 1)" :disabled="!pagination.next_page_url || isLoading" class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    Next
                                </button>
                            </div>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function dailyExpensesManager() {
            // Helper to get today's date in YYYY-MM-DD format
            const getTodayDate = () => {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            return {
                expenses: [],
                isLoading: false,
                pagination: { total: 0, per_page: 15, current_page: 1, from: 0, to: 0, next_page_url: null, prev_page_url: null },
                newExpense: {
                    operation_date: getTodayDate(),
                    buyer_id: '',
                    category: '',
                    quantity: '',
                    tariff: '',
                    comment: ''
                },
                errors: {},
                datePickerInstance: null, // To hold the flatpickr instance

                // Add init method
                init() {
                    this.fetchExpenses();
                    
                    // --- Check flatpickr status immediately ---
                    console.log('[Init Start] typeof window.flatpickr:', typeof window.flatpickr);
                    // ----------------------------------------

                    Alpine.effect(() => {
                        const initializePicker = () => {
                            if (this.$refs.dateInput && typeof window.flatpickr === 'function' && this.$refs.expenseForm) {
                                if (!this.datePickerInstance) {
                                    console.log('Initializing flatpickr (default settings)...'); // Updated log
                                    this.datePickerInstance = window.flatpickr(this.$refs.dateInput, {
                                        dateFormat: "Y-m-d",
                                        defaultDate: this.newExpense.operation_date,
                                    });
                                }
                                return true;
                            }
                            return false;
                        };

                        if (!initializePicker()) {
                            // If it failed immediately, try again after a short delay
                            console.warn('flatpickr not ready, trying again shortly...'); // Debug log
                            setTimeout(() => {
                                if (!initializePicker()) {
                                     console.error('flatpickr still not ready after delay.'); // Final attempt failed
                                }
                            }, 100); // 100ms delay
                        }
                    });
                },

                fetchExpenses(page = 1) {
                    this.isLoading = true;
                    fetch(`{{ route('admin.daily-expenses.index') }}?page=${page}`,
                        {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.expenses = data.data;
                            this.pagination = {
                                total: data.total,
                                per_page: data.per_page,
                                current_page: data.current_page,
                                from: data.from,
                                to: data.to,
                                next_page_url: data.next_page_url,
                                prev_page_url: data.prev_page_url
                            };
                        })
                        .catch(error => console.error('Error fetching expenses:', error))
                        .finally(() => this.isLoading = false);
                },

                addExpense() {
                    this.isLoading = true;
                    this.errors = {}; // Clear previous errors

                    fetch(`{{ route('admin.daily-expenses.store') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Get CSRF token
                        },
                        body: JSON.stringify(this.newExpense)
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 422) {
                                return response.json().then(data => {
                                    this.errors = data.errors;
                                    throw new Error('Validation failed');
                                });
                            } else {
                                throw new Error('Network response was not ok: ' + response.statusText);
                            }
                        }
                        return response.json(); // Expecting the newly created expense
                    })
                    .then(data => {
                        // Success - reset form and refresh table (fetch first page)
                        this.resetForm();
                        this.fetchExpenses(1); // Go back to first page to see the new entry usually
                        // Or: Add to start if on page 1: this.expenses.unshift(data);
                    })
                    .catch(error => {
                        if (error.message !== 'Validation failed') {
                             console.error('Error adding expense:', error);
                             // Optionally show a generic error message to the user
                        }
                    })
                    .finally(() => this.isLoading = false);
                },

                resetForm() {
                    this.newExpense = {
                        operation_date: getTodayDate(),
                        buyer_id: '',
                        category: '',
                        quantity: '',
                        tariff: '',
                        comment: ''
                    };
                    this.errors = {};
                    // Also update the date picker instance if it exists
                    if (this.datePickerInstance) {
                         this.datePickerInstance.setDate(this.newExpense.operation_date, false); // Update picker without triggering onChange
                    }
                },

                calculateTotal() {
                    const quantity = parseFloat(this.newExpense.quantity) || 0;
                    const tariff = parseFloat(this.newExpense.tariff) || 0;
                    return (quantity * tariff).toFixed(2);
                }
            }
        }
    </script>
    @push('head')
        <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Add CSRF token for fetch --}}
    @endpush
</x-app-layout> 