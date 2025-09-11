<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vacation Requests') }}
        </h2>
    </x-slot>

    {{-- Hide elements with x-cloak until Alpine initializes --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">User</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Note</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Period</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Extra Hours Used</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($requests as $request)
                            <tr class="hover:bg-gray-50" x-data="{ open: false }">
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $request->id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-800">{{ $request->user->first_name }} {{ $request->user->last_name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-800">{{ $request->note }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    @if($request->start_date && $request->end_date)
                                        {{ \Carbon\Carbon::parse($request->start_date)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('Y-m-d') }}
                                    @elseif($request->date && $request->start_time && $request->end_time)
                                        {{ \Carbon\Carbon::parse($request->date)->format('Y-m-d') }} ({{ \Carbon\Carbon::parse($request->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('H:i') }})
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-800">{{ $request->extra_hours_used }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-sm relative">
                                    <button @click="open = true" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                        Manage
                                    </button>

                                    <div x-show="open" x-cloak hidden
                                         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                        <div @click.away="open = false" class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                                            <h3 class="text-lg font-semibold mb-4">Manage Request #{{ $request->id }}</h3>

                                            <form method="POST" action="{{ route('requests.request.update', $request->id) }}">
                                                @csrf
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                    <select name="status" class="w-full border rounded px-2 py-1">
                                                        <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="approved" {{ $request->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                                        <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    </select>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                                                    <textarea name="admin_note" class="w-full border rounded px-2 py-1" rows="3">{{ $request->admin_note }}</textarea>
                                                </div>

                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="open = false" class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                                                    <button type="submit" class="px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if($requests->isEmpty())
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-sm text-gray-500 text-center">No vacation requests yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
