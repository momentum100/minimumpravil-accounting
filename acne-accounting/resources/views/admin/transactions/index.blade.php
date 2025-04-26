<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transactions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Optional: Add filters here later (by type, date range, user, etc.) --}}

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Type</th>
                                    <th scope="col" class="px-6 py-3">Amount</th>
                                    <th scope="col" class="px-6 py-3">Description</th>
                                    <th scope="col" class="px-6 py-3">Created By</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">{{ $transaction->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-4">{{ ucfirst(class_basename($transaction->operation_type)) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            {{ number_format($transaction->lines->sum('debit'), 2) }}
                                        </td>
                                        <td class="px-6 py-4">{{ Str::limit($transaction->description, 60) }}</td>
                                        <td class="px-6 py-4">{{ $transaction->operation?->creator?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.transactions.show', $transaction) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No transactions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 