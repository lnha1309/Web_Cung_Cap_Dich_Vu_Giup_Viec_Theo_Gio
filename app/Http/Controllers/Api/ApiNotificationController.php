<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiNotificationController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications (paginated)
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $customer = $user->khachHang;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách hàng'
            ], 404);
        }

        $perPage = $request->get('per_page', 20);
        $filterUnread = $request->get('filter') === 'unread';

        $notifications = $this->notificationService->getNotifications(
            $customer->ID_KH,
            $perPage,
            $filterUnread
        );

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $customer = $user->khachHang;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách hàng'
            ], 404);
        }

        $count = $this->notificationService->getUnreadCount($customer->ID_KH);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     * POST /api/notifications/{id}/mark-read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $customer = $user->khachHang;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách hàng'
            ], 404);
        }

        // Verify notification belongs to this customer
        $notification = ThongBao::where('ID_TB', $id)
            ->where('ID_KH', $customer->ID_KH)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông báo'
            ], 404);
        }

        $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đã đọc'
        ]);
    }

    /**
     * Mark all notifications as read
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $customer = $user->khachHang;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách hàng'
            ], 404);
        }

        $count = $this->notificationService->markAllAsRead($customer->ID_KH);

        return response()->json([
            'success' => true,
            'message' => "Đã đánh dấu {$count} thông báo là đã đọc",
            'count' => $count
        ]);
    }
}
