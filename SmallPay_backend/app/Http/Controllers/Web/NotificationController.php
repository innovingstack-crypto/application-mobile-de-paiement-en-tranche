<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::byUser(Auth::id())
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('Admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Accès non autorisé.');
        }

        $notification->markAsRead();

        if ($notification->related_order_id) {
            return redirect()->route('admin.orders.show', $notification->related_order_id);
        }

        return redirect()->back()->with('success', 'Notification marquée comme lue');
    }

    public function markAllAsRead(Request $request)
    {
        Notification::byUser(Auth::id())->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    }
}
