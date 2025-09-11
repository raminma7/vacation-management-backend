<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            {{-- Info Box --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <p class="text-blue-700">
                    Extra hours allow a user to exceed the standard vacation limits. For example, if the monthly limit is 16 hours, a user with extra hours can use them to avoid being blocked when requesting additional vacation time.
                </p>
            </div>
            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Email</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Extra</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $user->id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $user->first_name }} {{ $user->last_name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $user->email }}</td>
                                <form action="{{ route('users.updateVacation', $user->id) }}" method="POST">
                                    @csrf
                                    <td class="px-4 py-2">
                                        <input min="0" type="number" name="vacation_extra" value="{{ $user->vacation_extra ?? 0 }}" class="w-20 border rounded px-2 py-1">
                                    </td>
                                    <td class="px-4 py-2">
                                        <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Update</button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
