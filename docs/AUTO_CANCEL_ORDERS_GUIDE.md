# Hướng Dẫn: Tự Động Hủy Đơn Hàng (Auto-Cancel Orders)

## 📋 Tổng Quan

Hệ thống tự động hủy các đơn đặt (DonDat) nếu sau **2 giờ trước thời gian bắt đầu** mà đơn vẫn ở trạng thái `assigned` hoặc `finding_staff`.

### Ví dụ Minh Họa

- **Thời gian bắt đầu đơn**: 10:00
- **Mốc kiểm tra**: 08:00 (T - 2h)
- **Kết quả**: Nếu tại 08:00 đơn vẫn là `assigned` hoặc `finding_staff` → Tự động chuyển sang `cancelled`

### Logic Áp Dụng

✅ **Đơn theo giờ (hour)**: Sử dụng `DonDat.NgayLam` + `DonDat.GioBatDau`

✅ **Đơn theo tháng (month)**: Sử dụng buổi đầu tiên trong `LichBuoiThang` (earliest scheduled session)

---

## 🚀 Hướng Dẫn Cài Đặt

### Bước 1: Chạy Migration

Mở terminal tại thư mục dự án và chạy:

```bash
php artisan migrate
```

Migration sẽ tạo các composite index để tối ưu hiệu suất:
- `DonDat`: index trên `(TrangThaiDon, NgayLam, GioBatDau)`
- `LichBuoiThang`: index trên `(ID_DD, NgayLam, GioBatDau, TrangThaiBuoi)`

**Kiểm tra index đã tạo:**

```sql
SHOW INDEXES FROM DonDat WHERE Key_name = 'idx_dondat_auto_cancel';
SHOW INDEXES FROM LichBuoiThang WHERE Key_name = 'idx_lichbuoi_auto_cancel';
```

---

### Bước 2: Import SQL Event

1. Mở **phpMyAdmin** hoặc **MySQL Client** (HeidiSQL, MySQL Workbench, etc.)

2. Chọn database của dự án (thường là `web_giup_viec` hoặc tương tự)

3. Vào tab **SQL** và import file:
   ```
   database/sql/auto_cancel_orders_setup.sql
   ```

4. Hoặc chạy từng lệnh trong file theo thứ tự:
   ```sql
   SET GLOBAL event_scheduler = ON;
   DROP EVENT IF EXISTS auto_cancel_dondat_2h;
   -- ... (copy toàn bộ nội dung file SQL)
   ```

---

### Bước 3: Kiểm Tra EVENT Đã Hoạt Động

Chạy các lệnh sau để kiểm tra:

```sql
-- Kiểm tra EVENT SCHEDULER đã bật chưa
SHOW VARIABLES LIKE 'event_scheduler';
-- Kết quả mong đợi: event_scheduler = ON ✅

-- Kiểm tra EVENT đã được tạo
SHOW EVENTS WHERE Name = 'auto_cancel_dondat_2h';
```

**Kết quả mong đợi:**

| Name | Status | Interval value | Interval field |
|------|--------|----------------|----------------|
| auto_cancel_dondat_2h | ENABLED | 5 | MINUTE |

---

## ⚙️ Kích Hoạt EVENT SCHEDULER Vĩnh Viễn

> ⚠️ **Lưu ý**: Một số hệ thống (XAMPP, Laragon) khi restart MySQL thì `event_scheduler` sẽ tự động TẮT. Để tránh điều này, cần cấu hình trong file config MySQL.

### Với XAMPP

1. Tìm file `my.ini`:
   ```
   C:\xampp\mysql\bin\my.ini
   ```

2. Mở bằng Notepad/VSCode với quyền Administrator

3. Tìm section `[mysqld]` và thêm dòng:
   ```ini
   [mysqld]
   event_scheduler=ON
   ```

4. Lưu file và **Restart MySQL** từ XAMPP Control Panel

### Với Laragon

1. Tìm file `my.ini`:
   ```
   C:\laragon\bin\mysql\mysql-8.x\my.ini
   ```
   (Thay `mysql-8.x` bằng version MySQL bạn đang dùng: `mysql-8.0.30`, `mysql-5.7.33`, etc.)

2. Mở file và thêm vào section `[mysqld]`:
   ```ini
   [mysqld]
   event_scheduler=ON
   ```

3. Save và **Restart MySQL** từ Laragon menu

### Với Server Production (Ubuntu/Linux)

1. Tìm file config MySQL:
   ```bash
   sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
   ```

2. Thêm vào section `[mysqld]`:
   ```ini
   [mysqld]
   event_scheduler=ON
   ```

3. Restart MySQL:
   ```bash
   sudo systemctl restart mysql
   ```

---

## 🧪 Hướng Dẫn Test

### Test Đơn Theo Giờ (Hour)

1. **Tạo đơn test**:
   ```sql
   INSERT INTO DonDat (
       ID_DD, LoaiDon, ID_DV, ID_KH, NgayLam, GioBatDau, 
       ThoiLuongGio, TrangThaiDon, TongTien
   ) VALUES (
       'TEST_HOUR_001', 'hour', 'DV001', 'KH001', 
       CURDATE(), ADDTIME(CURTIME(), '01:50:00'),  -- Bắt đầu sau 1h50m
       3, 'assigned', 300000
   );
   ```

2. **Đợi 5-10 phút** (EVENT chạy mỗi 5 phút)

3. **Kiểm tra kết quả**:
   ```sql
   SELECT ID_DD, TrangThaiDon, NgayLam, GioBatDau 
   FROM DonDat 
   WHERE ID_DD = 'TEST_HOUR_001';
   ```
   
   👉 Trạng thái vẫn là `assigned` (vì chưa đến mốc T-2h)

4. **Sửa thời gian để test thực tế**:
   ```sql
   -- Set thời gian bắt đầu = NOW() + 1h 30m (đã qua mốc T-2h)
   UPDATE DonDat 
   SET NgayLam = CURDATE(), 
       GioBatDau = ADDTIME(CURTIME(), '01:30:00')
   WHERE ID_DD = 'TEST_HOUR_001';
   ```

5. **Đợi thêm 5 phút** và kiểm tra lại → Trạng thái sẽ tự động chuyển sang `cancelled` ✅

### Test Đơn Theo Tháng (Month)

1. **Tạo đơn tháng test**:
   ```sql
   INSERT INTO DonDat (
       ID_DD, LoaiDon, ID_DV, ID_KH, ID_Goi,
       NgayBatDauGoi, NgayKetThucGoi,
       TrangThaiDon, TongTien
   ) VALUES (
       'TEST_MONTH_001', 'month', 'DV001', 'KH001', 'GOI001',
       CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY),
       'finding_staff', 2000000
   );
   ```

2. **Tạo buổi đầu tiên trong LichBuoiThang**:
   ```sql
   INSERT INTO LichBuoiThang (
       ID_Buoi, ID_DD, NgayLam, GioBatDau, TrangThaiBuoi
   ) VALUES (
       'BUOI_001', 'TEST_MONTH_001', 
       CURDATE(), ADDTIME(CURTIME(), '01:30:00'),  -- Bắt đầu sau 1h30m
       'scheduled'
   );
   ```

3. **Đợi 5-10 phút** và kiểm tra:
   ```sql
   SELECT dd.ID_DD, dd.TrangThaiDon, lbt.NgayLam, lbt.GioBatDau
   FROM DonDat dd
   LEFT JOIN LichBuoiThang lbt ON dd.ID_DD = lbt.ID_DD
   WHERE dd.ID_DD = 'TEST_MONTH_001';
   ```
   
   👉 Đơn sẽ tự động chuyển sang `cancelled` ✅

---

## 🛠️ Quản Lý EVENT

### Tắt Tạm Thời

```sql
ALTER EVENT auto_cancel_dondat_2h DISABLE;
```

### Bật Lại

```sql
ALTER EVENT auto_cancel_dondat_2h ENABLE;
```

### Xóa Hẳn

```sql
DROP EVENT IF EXISTS auto_cancel_dondat_2h;
```

### Xem Tất Cả Events

```sql
SHOW EVENTS;
```

### Xem Chi Tiết EVENT

```sql
SHOW CREATE EVENT auto_cancel_dondat_2h\G
```

---

## 🐛 Troubleshooting

### ❌ Lỗi: "Event scheduler is switched off"

**Nguyên nhân**: EVENT SCHEDULER chưa được kích hoạt.

**Giải pháp**:
```sql
SET GLOBAL event_scheduler = ON;
```

### ❌ Lỗi: "Access denied; you need SUPER privilege"

**Nguyên nhân**: User MySQL không có quyền bật EVENT SCHEDULER.

**Giải pháp**:
- Đăng nhập bằng user `root` hoặc user có quyền SUPER
- Hoặc grant quyền:
  ```sql
  GRANT SUPER ON *.* TO 'your_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### ❌ EVENT không chạy

**Kiểm tra**:
1. Xác nhận `event_scheduler` = ON:
   ```sql
   SHOW VARIABLES LIKE 'event_scheduler';
   ```

2. Xác nhận EVENT status = ENABLED:
   ```sql
   SHOW EVENTS WHERE Name = 'auto_cancel_dondat_2h';
   ```

3. Kiểm tra log MySQL (nếu có lỗi):
   - XAMPP: `C:\xampp\mysql\data\mysql_error.log`
   - Laragon: `C:\laragon\data\mysql\mysql_error.log`

### ❌ Đơn không bị hủy dù đã qua mốc 2h

**Kiểm tra**:
1. Đảm bảo `NgayLam` và `GioBatDau` không NULL
2. Đảm bảo `TrangThaiDon` đúng là 'assigned' hoặc 'finding_staff'
3. Chạy query thủ công để test logic:
   ```sql
   SELECT 
       ID_DD,
       NgayLam,
       GioBatDau,
       CONCAT(NgayLam, ' ', GioBatDau) AS StartTime,
       DATE_SUB(CONCAT(NgayLam, ' ', GioBatDau), INTERVAL 2 HOUR) AS CancelCheckTime,
       NOW() AS CurrentTime,
       CASE 
           WHEN NOW() >= DATE_SUB(CONCAT(NgayLam, ' ', GioBatDau), INTERVAL 2 HOUR) 
           AND NOW() < CONCAT(NgayLam, ' ', GioBatDau)
           THEN 'SHOULD BE CANCELLED'
           ELSE 'NOT YET'
       END AS Status
   FROM DonDat
   WHERE LoaiDon = 'hour'
     AND TrangThaiDon IN ('assigned', 'finding_staff');
   ```

---

## 📌 Ghi Chú Quan Trọng

> ⚠️ **Lưu ý về Production**: 
> - Luôn backup database trước khi chạy migration hoặc import SQL event
> - Test kỹ trên môi trường development trước khi deploy lên production
> - Monitor log MySQL trong vài ngày đầu để đảm bảo EVENT chạy đúng

> 💡 **Best Practice**:
> - Event chạy mỗi 5 phút là hợp lý để cân bằng giữa độ chính xác và hiệu suất
> - Nếu muốn tăng tần suất, có thể sửa thành `EVERY 1 MINUTE`, nhưng sẽ tốn tài nguyên hơn
> - Nếu muốn giảm tần suất, có thể sửa thành `EVERY 10 MINUTE`

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề, kiểm tra lại:
1. ✅ Migration đã chạy thành công
2. ✅ EVENT SCHEDULER đã bật
3. ✅ EVENT đã được tạo và status = ENABLED
4. ✅ Dữ liệu test có đúng điều kiện (`TrangThaiDon`, `NgayLam`, `GioBatDau`)

---

**File liên quan:**
- Migration: `database/migrations/2025_11_25_add_index_dondat_for_auto_cancel.php`
- SQL Event: `database/sql/auto_cancel_orders_setup.sql`
