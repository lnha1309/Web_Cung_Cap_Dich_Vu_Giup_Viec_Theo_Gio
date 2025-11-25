-- =====================================================
-- AUTO CANCEL ORDER EVENT SCHEDULER SETUP
-- =====================================================
-- File: auto_cancel_orders_setup.sql
-- Mục đích: Tự động hủy đơn đặt (DonDat) nếu sau 2 giờ trước
--          thời gian bắt đầu mà đơn vẫn ở trạng thái 
--          'assigned' hoặc 'finding_staff'
-- =====================================================

-- Bước 1: Kích hoạt EVENT SCHEDULER
SET GLOBAL event_scheduler = ON;

-- Kiểm tra xem EVENT SCHEDULER đã bật chưa
SHOW VARIABLES LIKE 'event_scheduler';
-- Kết quả mong đợi: event_scheduler = ON ✅

-- =====================================================
-- Bước 2: Tạo EVENT tự động hủy đơn
-- =====================================================

-- Xóa event cũ nếu đã tồn tại (để có thể chạy lại script)
DROP EVENT IF EXISTS auto_cancel_dondat_2h;

DELIMITER $$

CREATE EVENT auto_cancel_dondat_2h
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    -- -----------------------------------------------------
    -- Phần 1: Hủy đơn theo giờ (LoaiDon = 'hour')
    -- -----------------------------------------------------
    UPDATE DonDat
    SET TrangThaiDon = 'cancelled'
    WHERE LoaiDon = 'hour'
      AND TrangThaiDon IN ('assigned', 'finding_staff')
      AND NgayLam IS NOT NULL 
      AND GioBatDau IS NOT NULL
      -- Đã đến mốc 2h trước giờ bắt đầu
      AND NOW() >= DATE_SUB(CONCAT(NgayLam, ' ', GioBatDau), INTERVAL 2 HOUR)
      -- Nhưng chưa qua giờ bắt đầu
      AND NOW() < CONCAT(NgayLam, ' ', GioBatDau);

    -- -----------------------------------------------------
    -- Phần 2: Hủy đơn theo tháng (LoaiDon = 'month')
    -- -----------------------------------------------------
    -- Tìm các đơn tháng có buổi đầu tiên (earliest scheduled session)
    -- mà đã đến mốc 2h trước giờ bắt đầu buổi đầu
    UPDATE DonDat dd
    INNER JOIN (
        SELECT 
            lbt.ID_DD,
            MIN(CONCAT(lbt.NgayLam, ' ', lbt.GioBatDau)) AS FirstSessionTime
        FROM LichBuoiThang lbt
        WHERE lbt.TrangThaiBuoi = 'scheduled'
        GROUP BY lbt.ID_DD
    ) first_session ON dd.ID_DD = first_session.ID_DD
    SET dd.TrangThaiDon = 'cancelled'
    WHERE dd.LoaiDon = 'month'
      AND dd.TrangThaiDon IN ('assigned', 'finding_staff')
      -- Đã đến mốc 2h trước giờ bắt đầu buổi đầu
      AND NOW() >= DATE_SUB(first_session.FirstSessionTime, INTERVAL 2 HOUR)
      -- Nhưng chưa qua giờ bắt đầu buổi đầu
      AND NOW() < first_session.FirstSessionTime;

END$$

DELIMITER ;

-- =====================================================
-- Bước 3: Kiểm tra EVENT đã được tạo
-- =====================================================
SHOW EVENTS WHERE Name = 'auto_cancel_dondat_2h';

-- Kết quả mong đợi:
-- Name: auto_cancel_dondat_2h
-- Status: ENABLED
-- Interval value: 5
-- Interval field: MINUTE

-- =====================================================
-- CÁC LỆNH QUẢN LÝ EVENT (Dùng khi cần)
-- =====================================================

-- Tạm thời TẮT event (không chạy nữa):
-- ALTER EVENT auto_cancel_dondat_2h DISABLE;

-- BẬT lại event:
-- ALTER EVENT auto_cancel_dondat_2h ENABLE;

-- XÓA hẳn event:
-- DROP EVENT IF EXISTS auto_cancel_dondat_2h;

-- Xem tất cả events trong database:
-- SHOW EVENTS;

-- =====================================================
-- GHI CHÚ QUAN TRỌNG
-- =====================================================
-- ⚠️ EVENT SCHEDULER có thể tự động TẮT khi restart MySQL
-- 👉 Để kích hoạt vĩnh viễn, xem hướng dẫn trong file 
--    AUTO_CANCEL_ORDERS_GUIDE.md
-- =====================================================
