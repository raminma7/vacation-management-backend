<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    {{-- Hide Alpine sections until initialization --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Vacation Limits --}}
            <div x-data="{ open: false }" x-cloak class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <button @click="open = !open" 
                        class="w-full flex justify-between items-center text-left">
                    <h3 class="text-lg font-bold text-gray-700">Vacation Limits</h3>
                    <svg :class="{ 'rotate-180': open }" class="h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-transition class="mt-4">
                    {{-- Info Box --}}
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-blue-700">
                            Here you can set the default values of vacation limits as a general rule. Exceptions for each user can be made in the <strong>Users</strong> tab.
                        </p>
                    </div>
                    <form action="{{ route('settings.updateLimits') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="yearly_limit" class="block text-sm font-medium text-gray-700">Yearly Limit (hours)</label>
                            <input min="0" type="number" name="yearly_limit" id="yearly_limit"
                                   value="{{ old('yearly_limit', $limits->yearly_limit ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('yearly_limit')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="monthly_limit" class="block text-sm font-medium text-gray-700">Monthly Limit (hours)</label>
                            <input min="0" type="number" name="monthly_limit" id="monthly_limit"
                                   value="{{ old('monthly_limit', $limits->monthly_limit ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('monthly_limit')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                                Save Limits
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Company Holidays --}}
            <div x-data="{ open: false }" x-cloak class="bg-white shadow sm:rounded-lg p-6">
                <button @click="open = !open" 
                        class="w-full flex justify-between items-center text-left">
                    <h3 class="text-lg font-bold text-gray-700">Company Holidays</h3>
                    <svg :class="{ 'rotate-180': open }" class="h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-transition class="mt-4">
                    {{-- Add new holiday --}}
                    <form action="{{ route('settings.setHoliday') }}" method="POST" class="mb-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Holiday Name</label>
                                <input type="text" name="name" id="name"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       required>
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" id="date"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       required>
                            </div>
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required>
                                    <option value="company">Company</option>
                                    <option value="national">National</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                                Add Holiday
                            </button>
                        </div>
                    </form>

                    {{-- List existing holidays --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($holidays ?? [] as $holiday)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $holiday->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ \Carbon\Carbon::parse($holiday->date)->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($holiday->type) }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <form action="{{ route('settings.deleteHoliday', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if(empty($holidays) || count($holidays) === 0)
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-sm text-gray-500 text-center">No holidays added yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
