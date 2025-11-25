// Notification System JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const notificationBell = document.getElementById('notificationBellToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const notificationApiBase = '/web-api/notifications';

    if (!notificationBell) return; // Exit if not logged in

    // Load unread count on page load
    loadUnreadCount();

    // Refresh count every 30 seconds
    setInterval(loadUnreadCount, 30000);

    // Toggle notification dropdown
    notificationBell.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = notificationDropdown.classList.contains('show');

        // Close account menu if open
        const accountDropdown = document.getElementById('accountMenuDropdown');
        if (accountDropdown) {
            accountDropdown.classList.remove('show');
        }

        if (!isOpen) {
            notificationDropdown.classList.add('show');
            loadNotifications();
        } else {
            notificationDropdown.classList.remove('show');
        }
    });

    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            markAllRead();
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!notificationBell.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });

    // Load unread count
    function loadUnreadCount() {
        fetch(`${notificationApiBase}/unread-count`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
                    notificationBadge.style.display = 'inline-block';
                } else {
                    notificationBadge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading unread count:', error);
            });
    }

    // Load notifications
    function loadNotifications() {
        notificationList.innerHTML = '<div class="notification-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</div>';

        fetch(`${notificationApiBase}?per_page=10`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    displayNotifications(data.data);
                } else {
                    notificationList.innerHTML = '<div class="notification-empty">Không có thông báo mới</div>';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = '<div class="notification-error">Lỗi tải thông báo</div>';
            });
    }

    // Display notifications
    function displayNotifications(notifications) {
        notificationList.innerHTML = '';

        notifications.forEach(notification => {
            const item = document.createElement('div');
            item.className = 'notification-item' + (notification.DaDoc ? '' : ' unread');
            item.dataset.id = notification.ID_TB;

            const time = formatTime(notification.ThoiGian);
            const icon = getNotificationIcon(notification.LoaiThongBao);
            const bookingId = notification.DuLieuLienQuan
                ? (notification.DuLieuLienQuan.ID_DD || notification.DuLieuLienQuan.booking_id)
                : null;

            item.innerHTML = `
                <div class="notification-icon ${notification.LoaiThongBao}">
                    <i class="${icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notification.TieuDe)}</div>
                    <div class="notification-message">${escapeHtml(notification.NoiDung)}</div>
                    <div class="notification-time">${time}</div>
                </div>
            `;

            item.addEventListener('click', function () {
                if (bookingId) {
                    markAsRead(notification.ID_TB, `/my-bookings/${bookingId}`);
                } else {
                    markAsRead(notification.ID_TB);
                }
            });

            notificationList.appendChild(item);
        });
    }

    // Mark notification as read
    function markAsRead(notificationId, redirectUrl = null) {
        fetch(`${notificationApiBase}/${notificationId}/mark-read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`[data-id="${notificationId}"]`);
                    if (item) {
                        item.classList.remove('unread');
                    }
                    loadUnreadCount();
                }
            })
            .catch(error => {
                console.error('Error marking as read:', error);
            })
            .finally(() => {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
    }

    // Mark all as read
    function markAllRead() {
        fetch(`${notificationApiBase}/mark-all-read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    loadUnreadCount();
                }
            })
            .catch(error => {
                console.error('Error marking all as read:', error);
            });
    }

    // Helper: Get notification icon
    function getNotificationIcon(type) {
        const icons = {
            'order_created': 'fa-solid fa-cart-plus',
            'order_cancelled': 'fa-solid fa-times-circle',
            'order_status_change': 'fa-solid fa-sync',
            'refund_completed': 'fa-solid fa-money-bill-wave',
            'other': 'fa-solid fa-bell'
        };
        return icons[type] || icons.other;
    }

    // Helper: Format time
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Vừa xong';
        if (minutes < 60) return `${minutes} phút trước`;
        if (hours < 24) return `${hours} giờ trước`;
        if (days < 7) return `${days} ngày trước`;

        return date.toLocaleDateString('vi-VN');
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
