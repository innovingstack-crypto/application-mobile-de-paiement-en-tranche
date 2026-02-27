<div class="flex space-x-2">
    <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    @if ($user->status === 'active')
        <form method="POST" action="{{ route('admin.users.block', $user) }}" style="display: inline;">
            @csrf
            <button type="submit" class="text-red-600 hover:text-red-800" title="Block" onclick="return confirm('Block this user?')">
                <i class="fas fa-ban"></i>
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="display: inline;">
            @csrf
            <button type="submit" class="text-green-600 hover:text-green-800" title="Unblock">
                <i class="fas fa-check"></i>
            </button>
        </form>
    @endif
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Delete this user?')">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
