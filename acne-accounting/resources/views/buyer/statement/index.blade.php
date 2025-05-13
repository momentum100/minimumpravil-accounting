<x-app-layout> {{-- Or your buyer-specific layout if different --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- Conditional Title --}}
            @if(isset($isAdminView) && $isAdminView && $viewMode === 'agency')
                {{ __('Admin View: Agency Charges for Buyer') }}
            @elseif($viewMode === 'agency')
                {{ __('My Agency Charges / Expenses') }}
            @else
                {{ __('My Statement') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Admin Buyer Selector Form (Only for admin view of agency transfers) --}}
                    @if(isset($isAdminView) && $isAdminView && $viewMode === 'agency')
                        <form method="GET" action="{{ route('admin.agency-transfers.index') }}" class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label for="buyer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Buyer</label>
                                    <select name="buyer_id" id="buyer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:focus:ring-offset-gray-800" onchange="this.form.submit()">
                                        <option value="">-- Select a Buyer --</option>
                                        @foreach($buyers as $buyerOption)
                                            <option value="{{ $buyerOption->id }}" {{ (isset($selectedBuyer) && $selectedBuyer->id == $buyerOption->id) ? 'selected' : '' }}>
                                                {{ $buyerOption->name }} ({{ $buyerOption->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Date inputs for admin --}}
                                <div>
                                    <label for="date_from_admin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                                    <input type="date" name="date_from" id="date_from_admin" value="{{ $dateFrom }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                                </div>
                                <div>
                                    <label for="date_to_admin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                                    <input type="date" name="date_to" id="date_to_admin" value="{{ $dateTo }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                                </div>
                            </div>
                            <div class="mt-4 flex items-center space-x-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus:ring-offset-gray-800">Apply Filters</button>
                                <a href="{{ route('admin.agency-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring focus:ring-gray-200 active:bg-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Clear All</a>
                            </div>
                        </form>
                    @endif

                    {{-- Original Filter Form (Non-Admin or general statement) --}}
                    {{-- Hide this form if it's the admin view for agency transfers as the one above handles it --}}
                    @if(!(isset($isAdminView) && $isAdminView && $viewMode === 'agency'))
                        <form method="GET" 
                            action="{{-- Determine action based on context --}}
                                @if(isset($isAdminView) && $isAdminView) {{-- Should not happen if above condition is met, but as fallback --}}
                                    {{ route('admin.buyer-statements.index', (isset($selectedBuyer) ? ['buyer_id' => $selectedBuyer->id] : [])) }}
                                @elseif($viewMode === 'agency')
                                    {{ route('buyer.agency-transfers.index') }}
                                @else
                                    {{ route('buyer.dashboard') }} {{-- Assuming buyer.dashboard is the general statement route --}}
                                @endif
                            " 
                            class="mb-6 space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                            {{-- Buyer ID hidden input for admin general statement view --}}
                            @if(isset($isAdminView) && $isAdminView && $viewMode !== 'agency' && isset($selectedBuyer))
                                <input type="hidden" name="buyer_id" value="{{ $selectedBuyer->id }}">
                            @endif

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
                                <a href="{{-- Determine clear link based on context --}}
                                    @if(isset($isAdminView) && $isAdminView && $viewMode !== 'agency')
                                        {{ route('admin.buyer-statements.index', (isset($selectedBuyer) ? ['buyer_id' => $selectedBuyer->id] : [])) }}
                                    @elseif(isset($isAdminView) && $isAdminView && $viewMode === 'agency')
                                        {{ route('admin.agency-transfers.index') }} {{-- Link to admin agency transfers without a buyer selected --}}
                                    @elseif($viewMode === 'agency')
                                        {{ route('buyer.agency-transfers.index') }}
                                    @else
                                        {{ route('buyer.dashboard') }}
                                    @endif
                                " class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring focus:ring-gray-200 active:bg-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Clear / Current Month</a>
                                
                                {{-- The period input is no longer present in the original form --}}
                                {{-- <input type="hidden" name="period" id="period"> --}}
                            </div>
                        </form>
                    @endif

                    <script>
                        function setDates(days) {
                            const endDate = new Date();
                            const startDate = new Date();
                            startDate.setDate(endDate.getDate() - days);

                            const formatDate = (date) => {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            };

                            // Try to set for admin date fields first if they exist
                            const adminDateFrom = document.getElementById('date_from_admin');
                            const adminDateTo = document.getElementById('date_to_admin');
                            if (adminDateFrom && adminDateTo) {
                                adminDateFrom.value = formatDate(startDate);
                                adminDateTo.value = formatDate(endDate);
                            } else {
                                // Fallback to original date fields
                                document.getElementById('date_from').value = formatDate(startDate);
                                document.getElementById('date_to').value = formatDate(endDate);
                            }
                            
                            // Clear any period input if it exists (it was removed from original form)
                            // const periodInput = document.getElementById('period');
                            // if (periodInput) periodInput.value = '';
                        }
                        // Removed quick date links from original form, they can be added to admin form if needed.
                    </script>

                    {{-- Display selected buyer for admin --}}
                    @if(isset($isAdminView) && $isAdminView && $viewMode === 'agency' && isset($selectedBuyer))
                        <div class="mb-4 p-3 bg-indigo-100 dark:bg-indigo-900 rounded-md">
                            <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">
                                Showing agency charges for: <span class="font-bold">{{ $selectedBuyer->name }}</span>
                            </p>
                        </div>
                    @elseif(isset($isAdminView) && $isAdminView && $viewMode === 'agency' && !isset($selectedBuyer) && request()->has('buyer_id'))
                         <div class="mb-4 p-3 bg-yellow-100 dark:bg-yellow-900 rounded-md">
                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                Please select a buyer to view their agency charges. If a buyer was selected but not found, they may not exist.
                            </p>
                        </div>
                    @endif

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
                                           @if(isset($isAdminView) && $isAdminView && $viewMode === 'agency' && !isset($selectedBuyer))
                                               Please select a buyer from the dropdown above to see their agency charges.
                                           @elseif($viewMode === 'agency')
                                               No charges via agencies found for the selected buyer and period.
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