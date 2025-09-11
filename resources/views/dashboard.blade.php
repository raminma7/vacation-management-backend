<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
                <div class="text-gray-500">Total Requests</div>
                <div class="text-2xl font-bold">{{ $totalRequests }}</div>
            </div>

            <div class="bg-yellow-100 shadow rounded-lg p-6 flex flex-col items-center">
                <div class="text-gray-700">Pending</div>
                <div class="text-2xl font-bold">{{ $pendingRequests }}</div>
            </div>

            <div class="bg-green-100 shadow rounded-lg p-6 flex flex-col items-center">
                <div class="text-gray-700">Approved</div>
                <div class="text-2xl font-bold">{{ $approvedRequests }}</div>
            </div>

            <div class="bg-red-100 shadow rounded-lg p-6 flex flex-col items-center">
                <div class="text-gray-700">Rejected</div>
                <div class="text-2xl font-bold">{{ $rejectedRequests }}</div>
            </div>

        </div>
    </div>
</x-app-layout>
