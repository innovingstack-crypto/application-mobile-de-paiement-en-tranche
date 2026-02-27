@extends('layouts.app')

@section('title', 'Users')
@section('header', 'Gestion des utilisateurs')

@section('content')
<div class="mb-6">
   
    
    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8" role="tablist">
            <button
                type="button"
                role="tab"
                aria-selected="true"
                class="tab-button pb-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium"
                onclick="switchTab('users')"
            >
                <i class="fas fa-users mr-2"></i> Users ({{ $users->total() }})
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300"
                onclick="switchTab('admins')"
            >
                <i class="fas fa-user-tie mr-2"></i> Admins
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                class="tab-button pb-4 px-1 border-b-2 border-transparent text-gray-500 font-medium hover:text-gray-700 hover:border-gray-300"
                onclick="switchTab('super-admins')"
            >
                <i class="fas fa-crown mr-2"></i> Super Admins
            </button>
        </nav>
    </div>

    <!-- Search and Add User -->
    <div class="mb-6 flex justify-between items-center">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Rechercher par nom, email, telephone, ID..."
                class="flex-1 max-w-sm px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-search mr-2"></i> Rechercher
            </button>
            @if (request('q'))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Reset</a>
            @endif
        </form>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i> Ajouter
        </a>
    </div>

    <!-- Users Table -->
    <div id="users-tab" class="tab-content bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $regularUsers = $users->filter(fn($u) => $u->role === 'user');
                    @endphp
                    @forelse ($regularUsers as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <div class="flex items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full mr-3">
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if ($user->status === 'active') bg-green-100 text-green-800
                                    @elseif ($user->status === 'blocked') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        
                            <td class="px-6 py-4 text-sm">
                                @include('Admin.users.partials.actions', ['user' => $user])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admins Table -->
    <div id="admins-tab" class="tab-content hidden bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Managed Users</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $admins = $users->filter(fn($u) => $u->role === 'admin');
                    @endphp
                    @forelse ($admins as $admin)
                        <tr class="hover:bg-gray-50 transition bg-purple-50">
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <div class="flex items-center">
                                    <span class="inline-block bg-purple-200 rounded-full p-1 mr-3">
                                        <i class="fas fa-user-tie text-purple-600"></i>
                                    </span>
                                    {{ $admin->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $admin->email ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $admin->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if ($admin->status === 'active') bg-green-100 text-green-800
                                    @elseif ($admin->status === 'blocked') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ ucfirst($admin->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                -
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @include('Admin.users.partials.actions', ['user' => $admin])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No admins found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Super Admins Table -->
    <div id="super-admins-tab" class="tab-content hidden bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Permissions</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $superAdmins = $users->filter(fn($u) => $u->role === 'super_admin');
                    @endphp
                    @forelse ($superAdmins as $superAdmin)
                        <tr class="hover:bg-gray-50 transition bg-amber-50">
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <div class="flex items-center">
                                    <span class="inline-block bg-yellow-300 rounded-full p-1 mr-3">
                                        <i class="fas fa-crown text-yellow-700"></i>
                                    </span>
                                    {{ $superAdmin->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $superAdmin->email ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $superAdmin->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if ($superAdmin->status === 'active') bg-green-100 text-green-800
                                    @elseif ($superAdmin->status === 'blocked') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    {{ ucfirst($superAdmin->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Full Access</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @include('Admin.users.partials.actions', ['user' => $superAdmin])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No super admins found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active styling from all buttons
    document.querySelectorAll('[role="tab"]').forEach(button => {
        button.setAttribute('aria-selected', 'false');
        button.classList.remove('border-blue-600', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show the selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active styling to the clicked button
    event.target.closest('[role="tab"]').setAttribute('aria-selected', 'true');
    event.target.closest('[role="tab"]').classList.add('border-blue-600', 'text-blue-600');
    event.target.closest('[role="tab"]').classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
}
</script>
@endsection