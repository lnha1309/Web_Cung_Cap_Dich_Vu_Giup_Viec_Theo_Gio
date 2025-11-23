# Hướng Dẫn Sử Dụng API cho Flutter App

## Tổng Quan

REST API đã được tạo sẵn để kết nối Flutter app với database MySQL của hệ thống. API sử dụng Laravel Sanctum để xác thực token-based authentication.

## Cài Đặt & Cấu Hình

### 1. Đã Hoàn Thành

✅ Laravel Sanctum đã được cài đặt
✅ 5 API Controllers đã được tạo
✅ API routes đã được cấu hình
✅ CORS đã được thiết lập
✅ Models đã được cập nhật với relationships

### 2. Cấu Hình Database

Đảm bảo file `.env` của bạn đã được cấu hình đúng MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Chạy Migration (Nếu Chưa)

```bash
php artisan migrate
```

### 4. Khởi Động Server

```bash
php artisan serve
```

API sẽ chạy tại: `http://127.0.0.1:8000`

## Cấu Trúc API

### Controllers Đã Tạo

1. **ApiAuthController** - Xác thực & quản lý user
   - Đăng ký, đăng nhập, đăng xuất
   - OTP verification
   - Profile management

2. **ApiServiceController** - Quản lý dịch vụ
   - Lấy danh sách dịch vụ
   - Chi tiết dịch vụ
   - Tính giá theo thời lượng

3. **ApiAddressController** - Quản lý địa chỉ
   - CRUD operations cho địa chỉ khách hàng
   - Tự động nhận diện quận/huyện

4. **ApiBookingController** - Quản lý đơn đặt
   - Tạo & xem đơn đặt
   - Tìm nhân viên khả dụng
   - Hủy đơn đặt

5. **ApiVoucherController** - Quản lý vouchers
   - Danh sách vouchers khả dụng
   - Áp dụng voucher
   - Lịch sử sử dụng

### Routes

Tất cả API routes được định nghĩa trong `routes/api.php`:

- **Public routes**: `/api/auth/login`, `/api/auth/register`, `/api/services`
- **Protected routes**: Yêu cầu `Authorization: Bearer {token}` header

## Testing API

### Sử dụng cURL

**Login:**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'
```

**Get Services:**
```bash
curl http://127.0.0.1:8000/api/services
```

**Get Addresses (với token):**
```bash
curl http://127.0.0.1:8000/api/addresses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Postman/Thunder Client

Import các endpoints từ file `API_DOCUMENTATION.md` để test.

## Integration với Flutter

### 1. Cài Đặt Dependencies

```yaml
# pubspec.yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
```

### 2. Tạo API Service

Xem ví dụ chi tiết trong file `API_DOCUMENTATION.md` phần "Flutter Integration Example".

### 3. Base URL

Trong Flutter app, cấu hình base URL:

```dart
// Development (Local)
static const String baseUrl = 'http://YOUR_LOCAL_IP:8000/api';

// Production
static const String baseUrl = 'https://your-domain.com/api';
```

**Lưu ý:** Khi test trên thiết bị thật/emulator, thay `127.0.0.1` bằng IP máy tính của bạn (ví dụ: `192.168.1.100`).

## Tài Liệu Đầy Đủ

Xem file **`API_DOCUMENTATION.md`** để có:

- ✅ Danh sách đầy đủ tất cả endpoints
- ✅ Request/Response examples chi tiết
- ✅ Flutter integration code examples
- ✅ Error handling
- ✅ Authentication flow

## Các File Quan Trọng

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── ApiAuthController.php
│           ├── ApiServiceController.php
│           ├── ApiAddressController.php
│           ├── ApiBookingController.php
│           └── ApiVoucherController.php
├── Models/
│   ├── User.php (đã cập nhật với HasApiTokens)
│   ├── ChiTietKhuyenMai.php (đã thêm relationships)
│   └── ... (các models khác)
config/
├── cors.php (cấu hình CORS)
└── sanctum.php (cấu hình Sanctum)
routes/
└── api.php (định nghĩa routes)
API_DOCUMENTATION.md (tài liệu API đầy đủ)
```

## Troubleshooting

### CORS Issues

Nếu gặp lỗi CORS, đảm bảo:
1. File `config/cors.php` đã tồn tại
2. Middleware CORS được kích hoạt trong `bootstrap/app.php`

### Token Expiration

Token mặc định không hết hạn. Để set thời gian hết hạn, thêm vào `config/sanctum.php`:

```php
'expiration' => 60 * 24, // 24 hours
```

### Database Connection

Nếu API trả về lỗi database, kiểm tra:
1. MySQL server đang chạy
2. Thông tin `.env` chính xác
3. Database và tables đã được tạo (chạy migrations)

## Next Steps

1. ✅ Review `API_DOCUMENTATION.md`
2. ✅ Test các endpoints bằng Postman
3. ✅ Integrate vào Flutter app
4. ⬜ Deploy lên server production
5. ⬜ Cấu hình HTTPS cho production

## Hỗ Trợ

- **API Documentation**: `API_DOCUMENTATION.md`
- **Laravel Sanctum Docs**: https://laravel.com/docs/sanctum
- **Flutter HTTP Package**: https://pub.dev/packages/http
