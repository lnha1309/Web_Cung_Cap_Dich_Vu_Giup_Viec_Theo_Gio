# REST API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication

Hầu hết các endpoints đều yêu cầu authentication. Sau khi đăng nhập, bạn sẽ nhận được một API token. Sử dụng token này trong header của mọi request:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Response Format

### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "errors": {...}  // Validation errors (nếu có)
}
```

---

## Endpoints

### 1. Authentication

#### 1.1 Send OTP
Gửi OTP để đăng ký tài khoản mới.

**Endpoint:** `POST /api/auth/send-otp`

**Request Body:**
```json
{
  "phone": "0123456789",
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP đã được gửi đến email của bạn.",
  "debug_otp": "123456"  // Chỉ hiện trong debug mode
}
```

#### 1.2 Verify OTP
Xác thực OTP.

**Endpoint:** `POST /api/auth/verify-otp`

**Request Body:**
```json
{
  "phone": "0123456789",
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP hợp lệ."
}
```

#### 1.3 Check Username
Kiểm tra username có khả dụng không.

**Endpoint:** `POST /api/auth/check-username`

**Request Body:**
```json
{
  "username": "nguyenvana"
}
```

**Response:**
```json
{
  "success": true,
  "available": true,
  "message": "Tên đăng nhập khả dụng."
}
```

#### 1.4 Check Phone
Kiểm tra số điện thoại có khả dụng không.

**Endpoint:** `POST /api/auth/check-phone`

**Request Body:**
```json
{
  "phone": "0123456789"
}
```

**Response:**
```json
{
  "success": true,
  "available": true,
  "message": "Số điện thoại khả dụng."
}
```

#### 1.5 Register
Đăng ký tài khoản mới.

**Endpoint:** `POST /api/auth/register`

**Request Body:**
```json
{
  "username": "nguyenvana",
  "password": "password123",
  "full_name": "Nguyễn Văn A",
  "email": "nguyenvana@example.com",
  "phone": "0123456789",
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đăng ký thành công.",
  "data": {
    "user": {
      "id": 1,
      "username": "nguyenvana",
      "full_name": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "phone": "0123456789"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
}
```

#### 1.6 Login
Đăng nhập.

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "username": "nguyenvana",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đăng nhập thành công.",
  "data": {
    "user": {
      "id": 1,
      "username": "nguyenvana",
      "full_name": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "phone": "0123456789"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz"
  }
}
```

#### 1.7 Logout
Đăng xuất (xóa token hiện tại).

**Endpoint:** `POST /api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Đăng xuất thành công."
}
```

#### 1.8 Get Profile
Lấy thông tin profile của user hiện tại.

**Endpoint:** `GET /api/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "nguyenvana",
    "full_name": "Nguyễn Văn A",
    "email": "nguyenvana@example.com",
    "phone": "0123456789"
  }
}
```

#### 1.9 Update Profile
Cập nhật thông tin profile.

**Endpoint:** `PUT /api/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "full_name": "Nguyễn Văn B",
  "email": "nguyenvanb@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cập nhật thông tin thành công.",
  "data": {
    "id": 1,
    "username": "nguyenvana",
    "full_name": "Nguyễn Văn B",
    "email": "nguyenvanb@example.com",
    "phone": "0123456789"
  }
}
```

---

### 2. Services

#### 2.1 Get All Services
Lấy danh sách tất cả dịch vụ.

**Endpoint:** `GET /api/services`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "DV001",
      "name": "Giúp việc 2 giờ",
      "description": "Mô tả dịch vụ...",
      "price": 70000,
      "max_area": 50,
      "rooms": 2,
      "duration_hours": 2
    },
    {
      "id": "DV002",
      "name": "Giúp việc 3 giờ",
      "description": "Mô tả dịch vụ...",
      "price": 100000,
      "max_area": 80,
      "rooms": 3,
      "duration_hours": 3
    }
  ]
}
```

#### 2.2 Get Service by ID
Lấy chi tiết dịch vụ.

**Endpoint:** `GET /api/services/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "DV001",
    "name": "Giúp việc 2 giờ",
    "description": "Mô tả dịch vụ...",
    "price": 70000,
    "max_area": 50,
    "rooms": 2,
    "duration_hours": 2
  }
}
```

#### 2.3 Get Service by Duration
Lấy dịch vụ theo số giờ (2, 3, hoặc 4 giờ).

**Endpoint:** `GET /api/services/by-duration/{hours}`

**Example:** `GET /api/services/by-duration/2`

**Response:** Giống như endpoint 2.2

#### 2.4 Calculate Quote
Tính giá dịch vụ theo thời lượng.

**Endpoint:** `POST /api/services/quote`

**Request Body:**
```json
{
  "duration": 2
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "service_id": "DV001",
    "service_name": "Giúp việc 2 giờ",
    "price": 70000,
    "duration_hours": 2
  }
}
```

---

### 3. Addresses

#### 3.1 Get All Addresses
Lấy danh sách địa chỉ của user.

**Endpoint:** `GET /api/addresses`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "DC_001",
      "unit": "Căn 101",
      "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM",
      "district_id": "Q01",
      "district_name": "Quận Tân Phú"
    }
  ]
}
```

#### 3.2 Create Address
Tạo địa chỉ mới.

**Endpoint:** `POST /api/addresses`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "unit": "Căn 101",
  "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tạo địa chỉ thành công.",
  "data": {
    "id": "DC_002",
    "unit": "Căn 101",
    "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM",
    "district_id": "Q01",
    "district_name": "Quận Tân Phú"
  }
}
```

#### 3.3 Get Address Detail
Lấy chi tiết địa chỉ.

**Endpoint:** `GET /api/addresses/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:** Giống như response của endpoint 3.2

#### 3.4 Update Address
Cập nhật địa chỉ.

**Endpoint:** `PUT /api/addresses/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "unit": "Căn 102",
  "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM"
}
```

**Response:** Giống như response của endpoint 3.2

#### 3.5 Delete Address
Xóa địa chỉ (soft delete).

**Endpoint:** `DELETE /api/addresses/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Xóa địa chỉ thành công."
}
```

---

### 4. Bookings

#### 4.1 Get All Bookings
Lấy danh sách đơn đặt của user.

**Endpoint:** `GET /api/bookings`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "DD_hour_001",
      "order_type": "hour",
      "service": {
        "id": "DV001",
        "name": "Giúp việc 2 giờ"
      },
      "address": {
        "unit": "Căn 101",
        "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM"
      },
      "note": "Ghi chú...",
      "work_date": "2025-11-25",
      "start_time": "08:00",
      "duration_hours": 2,
      "status": "assigned",
      "total_amount": 70000,
      "discounted_amount": 63000,
      "staff_id": "NV001",
      "created_at": "2025-11-22 20:00:00"
    }
  ]
}
```

#### 4.2 Get Booking Detail
Lấy chi tiết đơn đặt.

**Endpoint:** `GET /api/bookings/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "DD_hour_001",
    "order_type": "hour",
    "service": {
      "id": "DV001",
      "name": "Giúp việc 2 giờ",
      "price": 70000
    },
    "address": {
      "id": "DC_001",
      "unit": "Căn 101",
      "full_address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM"
    },
    "note": "Ghi chú...",
    "work_date": "2025-11-25",
    "start_time": "08:00",
    "duration_hours": 2,
    "status": "assigned",
    "total_amount": 70000,
    "discounted_amount": 63000,
    "staff_id": "NV001",
    "created_at": "2025-11-22 20:00:00",
    "vouchers": [
      {
        "voucher_code": "KHACHHANGMOI",
        "discount_amount": 7000
      }
    ]
  }
}
```

#### 4.3 Create Booking
Tạo đơn đặt mới.

**Endpoint:** `POST /api/bookings`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "order_type": "hour",
  "service_id": "DV001",
  "address_id": "DC_001",
  "work_date": "2025-11-25",
  "start_time": "08:00",
  "duration_hours": 2,
  "total_amount": 70000,
  "discounted_amount": 63000,
  "staff_id": "NV001",
  "vouchers": [
    {
      "code": "KHACHHANGMOI",
      "discount_amount": 7000
    }
  ],
  "note": "Ghi chú...",
  "payment_method": "cash"
}
```

**Hoặc nếu địa chỉ chưa được lưu:**
```json
{
  "order_type": "hour",
  "service_id": "DV001",
  "address_text": "140 Lê Trọng Tấn, Tân Phú, TP.HCM",
  "address_unit": "Căn 101",
  "work_date": "2025-11-25",
  "start_time": "08:00",
  "duration_hours": 2,
  "total_amount": 70000,
  "discounted_amount": 63000,
  "vouchers": [
    {
      "code": "KHACHHANGMOI",
      "discount_amount": 7000
    }
  ],
  "payment_method": "cash"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Tạo đơn đặt thành công.",
  "data": {
    "booking_id": "DD_hour_002",
    "status": "finding_staff"
  }
}
```

#### 4.4 Find Available Staff
Tìm nhân viên khả dụng.

**Endpoint:** `POST /api/bookings/find-staff`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "work_date": "2025-11-25",
  "start_time": "08:00",
  "duration_hours": 2,
  "address": "140 Lê Trọng Tấn, Tân Phú, TP.HCM"
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "NV001",
      "name": "Nguyễn Thị B",
      "avatar": "avatar.jpg",
      "phone": "0987654321",
      "rating_percent": 85,
      "proximity_percent": 100,
      "score": 95.5,
      "jobs_completed": 120
    }
  ]
}
```

#### 4.5 Calculate Quote
Tính giá cho đơn đặt.

**Endpoint:** `POST /api/bookings/quote`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "duration": 2
}
```

**Response:** Giống như endpoint 2.4

#### 4.6 Cancel Booking
Hủy đơn đặt.

**Endpoint:** `PUT /api/bookings/{id}/cancel`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Hủy đơn đặt thành công."
}
```

---

### 5. Vouchers

#### 5.1 Get Available Vouchers
Lấy danh sách vouchers khả dụng.

**Endpoint:** `GET /api/vouchers`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "code": "KHACHHANGMOI",
      "name": "Giảm giá khách hàng mới",
      "description": "Giảm 10% cho khách hàng mới",
      "discount_percent": 10,
      "max_discount": 50000,
      "start_date": "2025-01-01",
      "end_date": "2025-12-31",
      "already_used": false,
      "can_use": true
    }
  ]
}
```

#### 5.2 Apply Voucher
Áp dụng voucher và tính giảm giá.

**Endpoint:** `POST /api/vouchers/apply`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "code": "KHACHHANGMOI",
  "amount": 70000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "code": "KHACHHANGMOI",
    "name": "Giảm giá khách hàng mới",
    "discount_percent": 10,
    "discount_amount": 7000,
    "final_amount": 63000,
    "original_amount": 70000
  }
}
```

#### 5.3 Get Voucher History
Lấy lịch sử sử dụng voucher.

**Endpoint:** `GET /api/vouchers/history`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "booking_id": "DD_hour_001",
      "voucher_code": "KHACHHANGMOI",
      "voucher_name": "Giảm giá khách hàng mới",
      "discount_amount": 7000,
      "used_at": "2025-11-22 20:00:00"
    }
  ]
}
```

---

## Flutter Integration Example

### Install Dependencies

```yaml
# pubspec.yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
```

### API Service Class

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'http://your-domain.com/api';
  
  // Get auth token from storage
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }
  
  // Save auth token to storage
  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }
  
  // Clear auth token
  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }
  
  // Login
  Future<Map<String, dynamic>> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'username': username,
        'password': password,
      }),
    );
    
    final data = json.decode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      await saveToken(data['data']['token']);
      return data;
    } else {
      throw Exception(data['error'] ?? 'Login failed');
    }
  }
  
  // Get services
  Future<List<dynamic>> getServices() async {
    final response = await http.get(
      Uri.parse('$baseUrl/services'),
    );
    
    final data = json.decode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      return data['data'];
    } else {
      throw Exception(data['error'] ?? 'Failed to load services');
    }
  }
  
  // Get addresses (authenticated)
  Future<List<dynamic>> getAddresses() async {
    final token = await getToken();
    
    final response = await http.get(
      Uri.parse('$baseUrl/addresses'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );
    
    final data = json.decode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      return data['data'];
    } else {
      throw Exception(data['error'] ?? 'Failed to load addresses');
    }
  }
  
  // Create booking (authenticated)
  Future<Map<String, dynamic>> createBooking(Map<String, dynamic> bookingData) async {
    final token = await getToken();
    
    final response = await http.post(
      Uri.parse('$baseUrl/bookings'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: json.encode(bookingData),
    );
    
    final data = json.decode(response.body);
    
    if (response.statusCode == 201 && data['success']) {
      return data;
    } else {
      throw Exception(data['error'] ?? 'Failed to create booking');
    }
  }
  
  // Logout
  Future<void> logout() async {
    final token = await getToken();
    
    await http.post(
      Uri.parse('$baseUrl/auth/logout'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );
    
    await clearToken();
  }
}
```

### Usage Example

```dart
// Login
final apiService = ApiService();
try {
  final result = await apiService.login('username', 'password');
  print('Logged in: ${result['data']['user']['full_name']}');
} catch (e) {
  print('Login error: $e');
}

// Get services
try {
  final services = await apiService.getServices();
  print('Found ${services.length} services');
} catch (e) {
  print('Error: $e');
}

// Create booking
try {
  final booking = await apiService.createBooking({
    'order_type': 'hour',
    'service_id': 'DV001',
    'address_id': 'DC_001',
    'work_date': '2025-11-25',
    'start_time': '08:00',
    'duration_hours': 2,
    'total_amount': 70000,
    'payment_method': 'cash',
  });
  print('Booking created: ${booking['data']['booking_id']}');
} catch (e) {
  print('Error: $e');
}
```

---

## Notes

- Tất cả số tiền (amount, price) đều là số nguyên (VNĐ)
- Thời gian (time) theo format `HH:MM` (24h)
- Ngày (date) theo format `YYYY-MM-DD`
- Token sẽ hết hạn sau một thời gian, cần handle refresh hoặc login lại
- CORS đã được cấu hình để cho phép requests từ Flutter app
