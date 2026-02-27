@extends('layouts.app')

@section('title', 'Notifications')
@section('header', 'Notifications')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h3 class="text-xl font-bold text-gray-800">Toutes les notifications</h3>
    <form method="POST" action="{{ route('admin.notifications.readAll') }}">
        @csrf
        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">
            Tout marquer comme lu
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Message</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($notifications as $n)
                    <tr class="hover:bg-gray-50 transition {{ $n->is_read ? '' : 'bg-blue-50' }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $n->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $n->message }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $n->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-right">
                            @if (!$n->is_read)
                                <form method="POST" action="{{ route('admin.notifications.read', $n) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-800">Marquer comme lu</button>
                                </form>
                            @else
                                <span class="text-gray-400">Lu</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucune notification</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($notifications->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
