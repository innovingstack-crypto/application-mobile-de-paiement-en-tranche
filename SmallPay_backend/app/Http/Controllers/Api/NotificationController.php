<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PaymentSchedule;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Lister les notifications
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->get('per_page', 50);
            $offset = ($request->get('page', 1) - 1) * $limit;
            
            $query = Notification::byUser(auth('api')->id());
            
            if ($request->has('is_read')) {
                if ($request->is_read) {
                    $query->read();
                } else {
                    $query->unread();
                }
            }
            
            $total = $query->count();
            $notifications = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $notifications,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $request->get('page', 1),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     * PUT /api/notifications/{id}/read
     */
    public function markAsRead($id)
    {
        try {
            $notification = $this->notificationService->markAsRead($id);
            
            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Récupérer l'échéancier d'une commande
     * GET /api/schedules/{orderId}
     */
    public function getSchedule($orderId)
    {
        try {
            $schedules = PaymentSchedule::where('order_id', $orderId)
                ->orderBy('installment_number')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $schedules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        try {
            $count = $this->notificationService->getUnreadCount(auth('api')->id());
            
            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}