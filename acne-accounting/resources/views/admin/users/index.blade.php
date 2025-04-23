<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Role Filter Tabs -->
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                            <li class="me-2">
                                <a href="{{ route('admin.users.index') }}" @class([
                                    'inline-block p-4 border-b-2 rounded-t-lg',
                                    'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500 active' => !$roleFilter,
                                    'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' => $roleFilter
                                ])>All Users</a>
                            </li>
                            @foreach($validRoles as $role)
                                <li class="me-2">
                                    <a href="{{ route('admin.users.index', ['role' => $role]) }}" @class([
                                        'inline-block p-4 border-b-2 rounded-t-lg',
                                        'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500 active' => $roleFilter === $role,
                                        'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' => $roleFilter !== $role
                                    ])>{{ ucfirst($role) }}s</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>


                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Add New User') }}
                        </a>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Role</th>
                                    <th scope="col" class="px-6 py-3">Contact</th>
                                    <th scope="col" class="px-6 py-3">Team</th>
                                    <th scope="col" class="px-6 py-3">Accounts</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $user->name }}
                                        </th>
                                        <td class="px-6 py-4">{{ ucfirst($user->role) }}</td>
                                        <td class="px-6 py-4">{{ $user->email ?: ('TG: ' . $user->telegram_id ?: 'N/A') }}</td>
                                        <td class="px-6 py-4">{{ $user->team?->name ?: 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $user->accounts->count() }}</td>
                                        <td class="px-6 py-4">
                                            <span @class([
                                                'px-2 py-1 text-xs font-semibold leading-tight rounded-full',
                                                'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' => $user->active,
                                                'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' => ! $user->active,
                                            ])>
                                                {{ $user->active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                            <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-green-600 dark:text-green-500 hover:underline">Edit</a>
                                            @if(auth()->id() !== $user->id) {{-- Don't show delete button for self --}}
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No users found{{ $roleFilter ? ' with role ' . ucfirst($roleFilter) : '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> 