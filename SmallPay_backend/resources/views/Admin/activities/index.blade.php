{{-- resources/views/admin/activities/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Activités')
@section('header', 'Activités récentes')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Historique des activités</h3>
        <p class="mt-1 text-sm text-gray-500">Suivez toutes les activités récentes sur la plateforme</p>
    </div>
    
    <div class="divide-y divide-gray-200">
        @forelse($activities as $activity)
        <div class="p-6 flex items-start gap-4 hover:bg-gray-50">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full flex items-center justify-center
                    @if($activity->type === 'order') bg-blue-100 text-blue-600
                    @elseif($activity->type === 'payment') bg-green-100 text-green-600
                    @elseif($activity->type === 'login') bg-purple-100 text-purple-600
                    @else bg-gray-100 text-gray-600
                    @endif">
                    @if($activity->type === 'order')
                        <i class="fas fa-shopping-cart"></i>
                    @elseif($activity->type === 'payment')
                        <i class="fas fa-credit-card"></i>
                    @elseif($activity->type === 'login')
                        <i class="fas fa-sign-in-alt"></i>
                    @else
                        <i class="fas fa-history"></i>
                    @endif
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $activity->description }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <div class="p-6 text-center text-gray-500">
            Aucune activité récente
        </div>
        @endforelse
    </div>
    
    @if($activities->count() > 0)
    <div class="p-6 border-t border-gray-200 text-center">
        <p class="text-sm text-gray-500">{{ $activities->count() }} activités affichées</p>
    </div>
    @endif
</div>
@endsection
