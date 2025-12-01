<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all notifications for the logged-in customer
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $customer = $user->khachHang;

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin khách hàng.');
        }

        $filter = $request->get('filter', 'all'); // 'all' or 'unread'
        $filterUnread = ($filter === 'unread');

        $notifications = $this->notificationService->getNotifications(
            $customer->ID_KH,
            20, // per page
            $filterUnread
        );

        return view('notifications.index', compact('notifications', 'filter'));
    }
}
