@extends('layouts.app')

@section('title', 'Paiements en retard')
@section('header', 'Paiements en retard')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-xl font-bold text-gray-800">Échéances impayées en retard</h3>
    <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Retour aux commandes
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Commande</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Client</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Échéance</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Montant</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">En retard depuis</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($schedules as $schedule)
                    @php
                        $order = $schedule->order;
                    @endphp
                    <tr class="hover:bg-red-50 transition border-l-4 border-red-500">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ((auth()->user()->role ?? null) === 'super_admin')
                                <a href="{{ route('admin.users.show', $order->user) }}" class="text-blue-600 hover:underline">
                                    {{ $order->user->name }}
                                </a>
                            @else
                                <span class="text-gray-800">{{ $order->user->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-semibold text-gray-800">N° {{ $schedule->installment_number }}</span>
                            <div class="text-xs text-gray-500">{{ $schedule->status }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="text-red-600 font-bold">{{ number_format($schedule->amount, 2) }} XAF</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="text-red-600 font-semibold">
                                {{ $schedule->due_date->format('d/m/Y') }}
                                <br>
                                <span class="text-xs">({{ $schedule->due_date->diffInDays(now()) }} jours)</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                            <p>Aucun paiement en retard.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($schedules->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">
            {{ $schedules->links() }}
        </div>
    @endif
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-red-50 rounded-lg shadow p-6 border-l-4 border-red-500">
        <p class="text-gray-600 text-sm">Total échéances en retard</p>
        <p class="text-3xl font-bold text-red-600">{{ $schedules->total() }}</p>
    </div>

    <div class="bg-orange-50 rounded-lg shadow p-6 border-l-4 border-orange-500">
        <p class="text-gray-600 text-sm">Montant total en retard</p>
        <p class="text-3xl font-bold text-orange-600">
            {{ number_format(
                $schedules->sum('amount'),
                2
            ) }} XAF
        </p>
    </div>

    <div class="bg-yellow-50 rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <p class="text-gray-600 text-sm">Moyenne jours de retard</p>
        <p class="text-3xl font-bold text-yellow-600">
            @php
                $avgDays = 0;
                if ($schedules->count() > 0) {
                    $totalDays = 0;
                    foreach ($schedules as $schedule) {
                        $totalDays += $schedule->due_date->diffInDays(now());
                    }
                    $avgDays = round($totalDays / $schedules->count());
                }
            @endphp
            {{ $avgDays }} jours
        </p>
    </div>
</div>
@endsection