<x-app-layout> {{-- Or your buyer-specific layout if different --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- Conditional Title --}}
            @if($viewMode === 'agency')
                {{ __('Agency Charges / Expenses') }}
            @else
                {{ __('My Statement') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Filter Form - Conditional Action/Clear --}}
                    <form method="GET" action="{{ route($viewMode === 'agency' ? 'buyer.agency-transfers.index' : 'buyer.dashboard') }}" class="mb-6 space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                        {{-- Date inputs are the same --}}
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-offset-gray-800">Filter</button>
                            {{-- Conditional Clear Link --}}
                            <a href="{{ route($viewMode === 'agency' ? 'buyer.agency-transfers.index' : 'buyer.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring focus:ring-gray-200 active:bg-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Clear / Current Month</a>
                            {{-- Text links only needed in general view? Or keep for both? Let's keep for both for now --}}
                            <a href="javascript:void(0);" onclick="setDates(30)" class="text-sm text-blue-600 dark:text-blue-400 hover:underline focus:outline-none focus:underline ml-2">Last 30 Days</a>
                            <a href="javascript:void(0);" onclick="setDates(60)" class="text-sm text-blue-600 dark:text-blue-400 hover:underline focus:outline-none focus:underline ml-2">Last 60 Days</a>
                        </div>
                    </form>

                    {{-- Script should still contain setDates function (without submit) --}}
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

                            // REMOVED: Submit the form
                            // document.querySelector('form').submit(); 
                        }
                    </script>

                    {{-- Total Amount - Conditional Label --}}
                    @if($dateFrom && $dateTo)
                        <div class="mb-4 p-4 rounded-lg 
                            @if($viewMode === 'agency') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @else bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @endif"> 
                            <span class="font-semibold">
                                @if($viewMode === 'agency')
                                    Total Charged by Agencies ({{ $dateFrom }} to {{ $dateTo }}):
                                @else
                                    Total Expenses for Period ({{ $dateFrom }} to {{ $dateTo }}):
                                @endif
                            </span> {{ number_format($totalAmount, 2) }}
                        </div>
                    @endif

                    {{-- Transaction Table - Conditional Headers & Rows --}}
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                             <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    @if($viewMode === 'agency')
                                        <th scope="col" class="px-6 py-3">Charged By Agency</th>
                                        <th scope="col" class="px-6 py-3">Amount Charged</th>
                                        <th scope="col" class="px-6 py-3">Description / Comment</th>
                                    @else
                                        <th scope="col" class="px-6 py-3">Type</th>
                                        <th scope="col" class="px-6 py-3">Amount</th>
                                        <th scope="col" class="px-6 py-3">Description</th>
                                        <th scope="col" class="px-6 py-3"><span class="sr-only">Details</span></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        {{-- Date is common --}}
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>

                                        @if($viewMode === 'agency')
                                            {{-- Agency Transfer Row Logic --}}
                                            @php
                                                $fundTransfer = $transaction->operation;
                                                $creditLine = $transaction->lines->where('credit', '>', 0)->first(); 
                                                $chargedAmount = $creditLine ? $creditLine->credit : 0;
                                                $fromAgencyName = $fundTransfer?->fromAccount?->user?->name ?? 'Unknown Agency';
                                            @endphp
                                            <td class="px-6 py-4">{{ $fromAgencyName }}</td>
                                            <td class="px-6 py-4 text-right">{{ number_format($chargedAmount, 2) }}</td>
                                            <td class="px-6 py-4">{{ Str::limit($fundTransfer->comment ?? $transaction->description, 80) }}</td>
                                        @else
                                            {{-- General Statement Row Logic --}}
                                            <td class="px-6 py-4">{{ ucfirst(class_basename($transaction->operation_type)) }}</td>
                                            <td class="px-6 py-4 text-right">
                                                @php
                                                    $displayAmount = 0;
                                                    if ($buyerAccountId) {
                                                        $buyerLine = $transaction->lines->firstWhere('account_id', $buyerAccountId);
                                                        if ($buyerLine) { // Should show credit for expense
                                                            $displayAmount = $buyerLine->credit;
                                                        }
                                                    }
                                                @endphp
                                                {{ number_format($displayAmount, 2) }}
                                            </td>
                                            <td class="px-6 py-4">{{ Str::limit($transaction->description, 80) }}</td>
                                            <td class="px-6 py-4 text-right">
                                                {{-- <a href="#" ...>Details</a> --}}
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        {{-- Conditional Empty Message --}}
                                        <td colspan="{{ $viewMode === 'agency' ? 4 : 5 }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                           @if($viewMode === 'agency')
                                               No charges via agencies found for the selected period.
                                           @else
                                               No expense transactions found for the selected period.
                                           @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 