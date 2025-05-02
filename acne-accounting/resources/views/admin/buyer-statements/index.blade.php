<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buyer Statements') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('admin.buyer-statements.index') }}" class="mb-6 space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                        <div>
                            <label for="buyer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buyer</label>
                            <select name="buyer_id" id="buyer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                                <option value="">-- Select Buyer --</option>
                                @foreach ($buyers as $buyer)
                                    <option value="{{ $buyer->id }}" {{ $selectedBuyerId == $buyer->id ? 'selected' : '' }}>
                                        {{ $buyer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                        </div>

                        {{-- Hidden input for period shortcuts --}}
                        <input type="hidden" name="period" id="period" value="{{ $period }}">

                        <div class="flex items-end space-x-4">
                            {{-- Filter Button First --}}
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-offset-gray-800">Filter</button>
                            
                            {{-- Clear Button Second --}}
                            <a href="{{ route('admin.buyer-statements.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring focus:ring-gray-200 active:bg-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Clear</a>
                            
                            {{-- Text Links Last --}}
                            <a href="javascript:void(0);" onclick="setDates(30)" class="text-sm text-blue-600 dark:text-blue-400 hover:underline focus:outline-none focus:underline">Last 30 Days</a>
                            <a href="javascript:void(0);" onclick="setDates(60)" class="text-sm text-blue-600 dark:text-blue-400 hover:underline focus:outline-none focus:underline">Last 60 Days</a>
                        </div>
                    </form>

                    <script>
                        function setDates(days) {
                            const endDate = new Date();
                            const startDate = new Date();
                            startDate.setDate(endDate.getDate() - days);

                            // Format dates as YYYY-MM-DD
                            const formatDate = (date) => {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            };

                            document.getElementById('date_from').value = formatDate(startDate);
                            document.getElementById('date_to').value = formatDate(endDate);
                            
                            // Clear the period input (no longer primary driver)
                            document.getElementById('period').value = ''; 
                        }
                        
                        // No longer need the listeners that cleared the period input
                    </script>

                    {{-- Total Amount --}}
                    @if($selectedBuyerId && ($dateFrom || $dateTo || $period))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 rounded-lg text-green-800 dark:text-green-200">
                            <span class="font-semibold">Total Expenses for Selected Period:</span> {{ number_format($totalAmount, 2) }}
                        </div>
                    @endif

                    {{-- Transaction Table --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Type</th>
                                    <th scope="col" class="px-6 py-3">Amount</th>
                                    <th scope="col" class="px-6 py-3">Description</th>
                                    {{-- <th scope="col" class="px-6 py-3">Created By</th> --}}
                                    {{-- <th scope="col" class="px-6 py-3">Parties Involved</th> --}}
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Get the buyer's account ID outside the loop if a buyer is selected
                                    $buyerAccountId = null;
                                    if ($selectedBuyerId) {
                                        $buyerAccount = \App\Models\Account::where('user_id', $selectedBuyerId)->first();
                                        $buyerAccountId = $buyerAccount?->id;
                                    }
                                @endphp
                                @forelse ($transactions as $transaction)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">{{ $transaction->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-4">{{ ucfirst(class_basename($transaction->operation_type)) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            @php
                                                $displayAmount = 0;
                                                // Find the credit line associated with the buyer's account for this transaction
                                                if ($buyerAccountId) {
                                                    // Corrected relationship name: lines
                                                    $buyerLine = $transaction->lines->firstWhere('account_id', $buyerAccountId); 
                                                    if ($buyerLine) {
                                                        // Assuming expense = credit to buyer account
                                                        $displayAmount = $buyerLine->credit;
                                                    } else {
                                                         // Fallback or log if no matching line found?
                                                    }
                                                }
                                                // Fallback if no buyer selected or line not found (though unlikely if total works)
                                            @endphp
                                            {{ number_format($displayAmount, 2) }}
                                        </td>
                                        <td class="px-6 py-4">{{ Str::limit($transaction->description, 60) }}</td>
                                        {{-- Hiding columns less relevant for buyer statement --}}
                                        {{-- <td class="px-6 py-4">{{ $transaction->operation?->creator?->name ?? 'N/A' }}</td> --}}
                                        {{-- <td class="px-6 py-4">...parties...</td> --}}
                                        <td class="px-6 py-4 text-right">
                                            {{-- Link to general transaction detail if needed --}}
                                            <a href="{{ route('admin.transactions.show', $transaction) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            @if($selectedBuyerId)
                                                No transactions found for the selected buyer and period.
                                            @else
                                                Please select a buyer to view their statement.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                         {{-- Append query parameters to pagination links --}}
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 