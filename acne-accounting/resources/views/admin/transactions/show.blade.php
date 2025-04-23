<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transaction Details') }} #{{ $transaction->id }}
            </h2>
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                &larr; {{ __('Back to Transactions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Transaction Summary</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->id }}</dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->transaction_date->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            {{-- Extract type from the operation model name --}}
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ class_basename($transaction->operation_type) }}</dd>
                        </div>
                         <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ $transaction->status }}</dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By (Operation)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->operation?->creator?->name ?? 'N/A' }}</dd>
                        </div>
                         <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Accounting Period</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->accounting_period }}</dd>
                        </div>
                        <div class="col-span-full">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->description ?? '-' }}</dd>
                        </div>
                        {{-- Optional: Add details from the specific operation if needed --}}
                        {{-- @if ($transaction->operation_type === App\Models\FundTransfer::class) ... @endif --}}
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Transaction Lines (Double Entry)</h3>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Account ID</th>
                                    <th scope="col" class="px-6 py-3">Account Description</th>
                                    <th scope="col" class="px-6 py-3">Debit</th>
                                    <th scope="col" class="px-6 py-3">Credit</th>
                                    <th scope="col" class="px-6 py-3">Line Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaction->lines as $line)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">{{ $line->account_id }}</td>
                                        <td class="px-6 py-4">{{ $line->account?->description ?? 'Account not found' }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($line->debit, 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($line->credit, 2) }}</td>
                                        <td class="px-6 py-4">{{ $line->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No transaction lines found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 