@extends('layouts.base')

@section('title', 'Chi tiết đơn hàng')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    /* CSS Tùy chỉnh cho đẹp hơn */
    .detail-card { border: none; border-radius: 16px; overflow: hidden; }
    .detail-header { background: linear-gradient(to right, #004D2E, #004D2E); color: white; padding: 20px; }
    .info-label { color: #6c757d; font-size: 0.9rem; margin-bottom: 5px; }
    .info-value { font-weight: 600; color: #212529; font-size: 1.05rem; }
    .price-tag { font-size: 1.5rem; color: #dc3545; font-weight: bold; }
    .btn-back { background: rgba(255, 255, 255, 0.2); color: white; border: none; transition: 0.3s; }
    .btn-back:hover { background: rgba(255, 255, 255, 0.4); color: white; }
    .rating-stars .star-btn { text-decoration: none; color: inherit; }
    .rating-stars .star-btn i { transition: color 0.2s; }
</style>

<div class="container py-5" style="background-color: #f4f6f9; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="card shadow detail-card">
                <div class="detail-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">CHI TIẾT ĐƠN HÀNG</h5>
                        <small class="opacity-75">Mã đơn: #{{ $booking->ID_DD }}</small>
                    </div>
                    <a href="{{ route('bookings.history') }}" class="btn btn-sm btn-back rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
                
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        @if($booking->TrangThaiDon == 'finding_staff')
                            <div class="alert alert-info d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-search me-2"></i>Trạng thái: <strong>Đang tìm nhân viên</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'assigned')
                            <div class="alert alert-primary d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-person-check me-2"></i>Trạng thái: <strong>Đã có nhân viên nhận</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'confirmed')
                            <div class="alert alert-success d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-check2-circle me-2"></i>Trạng thái: <strong>Đơn đã được xác nhận bởi nhân viên</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'rejected')
                            <div class="alert alert-danger d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-exclamation-octagon me-2"></i>Trạng thái: <strong>Đơn bị từ chối - Đang tìm nhân viên khác</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'done')
                            <div class="alert alert-success d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i>Trạng thái: <strong>Hoàn thành</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'completed')
                            <div class="alert alert-success d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-clipboard-check me-2"></i>Trạng thái: <strong>Đã hoàn thành - chờ đánh giá</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'cancelled')
                            <div class="alert alert-danger d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-x-circle-fill me-2"></i>Trạng thái: <strong>Đã hủy</strong>
                            </div>
                        @else
                            <div class="alert alert-secondary d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                Trạng thái: <strong>{{ $booking->TrangThaiDon }}</strong>
                            </div>
                        @endif
                    </div>

                    @if($booking->TrangThaiDon === 'finding_staff' && $booking->FindingStaffPromptSentAt)
                        <div class="alert alert-warning border-0 shadow-sm mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Chưa tìm thấy nhân viên phù hợp</h6>
                                    <p class="mb-2 text-muted small">Bạn có thể đổi giờ, hủy đơn hoặc tiếp tục chờ thêm để chúng tôi sắp xếp nhân viên phù hợp.</p>
                                    @if($booking->FindingStaffResponse === 'wait')
                                        <span class="badge bg-success">Đã ghi nhận: Tiếp tục chờ</span>
                                    @elseif($booking->FindingStaffResponse === 'reschedule')
                                        <span class="badge bg-primary">Đã ghi nhận yêu cầu đổi giờ bắt đầu</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Cần bạn chọn hành động</span>
                                    @endif
                                </div>
                                <div class="text-end small text-muted">
                                    @php
                                        $promptAt = $booking->FindingStaffPromptSentAt ? \Carbon\Carbon::parse($booking->FindingStaffPromptSentAt) : null;
                                    @endphp
                                    @if($promptAt)
                                        Gửi lúc {{ $promptAt->format('H:i d/m') }}
                                    @endif
                                </div>
                            </div>
                            @if($errors->has('new_date') || $errors->has('new_time'))
                                <div class="alert alert-danger mt-2 mb-0 py-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    {{ $errors->first('new_date') ?? $errors->first('new_time') }}
                                </div>
                            @endif

                            @if(($booking->RescheduleCount ?? 0) >= 1)
                                {{-- Already rescheduled, show info message --}}
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Bạn đã thay đổi thời gian đơn hàng. Mỗi đơn chỉ được thay đổi 1 lần duy nhất.
                                </div>
                            @else
                                {{-- Show reschedule form and options --}}
                                <form id="rescheduleForm" class="mt-3" method="POST" action="{{ route('bookings.findingStaffAction', $booking->ID_DD) }}">
    @csrf
    <input type="hidden" name="action" value="reschedule">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-muted">Ngay bat dau moi</label>
            <input type="date" name="new_date" class="form-control"
                value="{{ $booking->NgayLam ? \Carbon\Carbon::parse($booking->NgayLam)->format('Y-m-d') : \Carbon\Carbon::now()->addDay()->format('Y-m-d') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Gio bat dau moi</label>
            <input type="time" name="new_time" class="form-control" min="07:00" max="17:00"
                value="{{ $suggestedTime ?? ($booking->GioBatDau ? \Carbon\Carbon::parse($booking->GioBatDau)->format('H:i') : '') }}">
            <small class="text-muted">
                @if(!empty($suggestedTime))
                    Goi y gan nhat: {{ $suggestedTime }} (khung 07:00 - 17:00).
                @else
                    Chi chon trong khung 07:00 - 17:00. Chon 07:00 hoac 17:00 se co phu thu 30.000d.
                @endif
            </small>
        </div>
        <div class="col-md-4 text-end d-flex flex-column gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-clock-history me-1"></i>Doi thoi gian
            </button>
        </div>
    </div>
</form>


                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <form method="POST" action="{{ route('bookings.findingStaffAction', $booking->ID_DD) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="wait">
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-hourglass-split me-1"></i>Tiếp tục chờ
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-circle me-1"></i>Hủy đơn
                                </button>
                            </div>

                            {{-- Smart Staff Suggestions --}}
                            @if(!empty($staffSuggestions) && count($staffSuggestions) > 0)
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="fw-bold mb-3 text-success">
                                        <i class="bi bi-stars me-2"></i>Gợi ý nhân viên phù hợp
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        Chúng tôi tìm thấy {{ count($staffSuggestions) }} nhân viên có lịch rảnh gần với thời gian bạn đã chọn. Click "Chọn ngay" để tự động cập nhật đơn.
                                    </p>
                                    
                                    <div class="row g-3">
                                        @foreach($staffSuggestions as $suggestion)
                                            <div class="col-md-4">
                                                <div class="card h-100 border-success suggestion-card" style="cursor: pointer; transition: all 0.3s;">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <img src="{{ $suggestion['hinh_anh'] }}" 
                                                                 alt="{{ $suggestion['ten_nv'] }}" 
                                                                 class="rounded-circle me-2"
                                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold text-dark small">{{ $suggestion['ten_nv'] }}</div>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    @php
                                                                        $stars = $suggestion['avg_rating'];
                                                                    @endphp
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <i class="bi {{ $i <= $stars ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                                                    @endfor
                                                                    <span class="ms-1">({{ $suggestion['jobs_completed'] }})</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-2">
                                                            @php
                                                                $suggestionDate = \Carbon\Carbon::parse($suggestion['suggested_date']);
                                                                $bookingDate = \Carbon\Carbon::parse($booking->NgayLam);
                                                                $today = \Carbon\Carbon::today();
                                                                $isToday = $suggestionDate->isSameDay($today);
                                                                $isSameDate = $suggestionDate->isSameDay($bookingDate);
                                                            @endphp
                                                            
                                                            {{-- Date Badge --}}
                                                            @if($isToday)
                                                                <span class="badge bg-primary mb-1">
                                                                    <i class="bi bi-calendar-check me-1"></i>Hôm nay
                                                                </span>
                                                            @elseif($isSameDate)
                                                                <span class="badge bg-info mb-1">
                                                                    <i class="bi bi-calendar-event me-1"></i>{{ $suggestionDate->format('d/m/Y') }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning text-dark mb-1">
                                                                    <i class="bi bi-calendar-event me-1"></i>{{ $suggestionDate->format('d/m/Y') }}
                                                                </span>
                                                            @endif
                                                            
                                                            {{-- Time Badge --}}
                                                            <span class="badge bg-success mb-1">
                                                                <i class="bi bi-clock me-1"></i>{{ $suggestion['suggested_time'] }}
                                                            </span>
                                                            
                                                            {{-- Additional Info Badges --}}
                                                            @if($suggestion['days_diff'] == 0 && $suggestion['time_diff_minutes'] <= 60)
                                                                <span class="badge bg-light text-dark mb-1">Gần nhất</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success w-100 apply-suggestion-btn"
                                                                data-staff-id="{{ $suggestion['id_nv'] }}"
                                                                data-staff-name="{{ $suggestion['ten_nv'] }}"
                                                                data-suggested-date="{{ $suggestion['suggested_date'] }}"
                                                                data-suggested-time="{{ $suggestion['suggested_time'] }}"
                                                                data-booking-id="{{ $booking->ID_DD }}">
                                                            <i class="bi bi-check-circle me-1"></i>Chọn ngay
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-calendar3 me-1"></i>Ngày đặt đơn</p>
                                    <p class="info-value">{{ \Carbon\Carbon::parse($booking->NgayTao)->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-clock me-1"></i>Thời gian làm việc</p>
                                    <p class="info-value">
                                        {{ $booking->NgayLam ? \Carbon\Carbon::parse($booking->NgayLam)->format('d/m/Y') : 'Theo gói tháng' }}
                                        <span class="text-muted fw-normal mx-1">|</span>
                                        {{ $booking->GioBatDau ? \Carbon\Carbon::parse($booking->GioBatDau)->format('H:i') : '' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-broom me-1"></i>Dịch vụ</p>
                                    <p class="info-value text-primary">
                                        {{ $booking->dichVu->TenDV ?? $booking->ID_DV }}
                                    </p>
                                </div>
                                <div>
                                    <p class="info-label"><i class="bi bi-wallet2 me-1"></i>Tổng thanh toán</p>
                                    <p class="price-tag mb-0">
                                        {{ number_format($booking->TongTienSauGiam) }} đ
                                    </p>
                                    @php
                                        $payment = $booking->lichSuThanhToan->first();
                                        $method = $payment?->PhuongThucThanhToan;
                                        $methodLabel = match($method) {
                                            'VNPay' => 'VNPay',
                                            'TienMat' => 'Tiền mặt',
                                            default => 'Chưa thanh toán',
                                        };
                                    @endphp
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.9rem;">
                                        <i class="bi bi-credit-card me-1"></i>Phương thức: {{ $methodLabel }}
                                    </p>
                                </div>
                                @if($booking->chiTietKhuyenMai && $booking->chiTietKhuyenMai->count())
                                    <div class="mt-3">
                                        <p class="info-label"><i class="bi bi-ticket-perforated me-1"></i>Khuyến mãi áp dụng</p>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($booking->chiTietKhuyenMai as $ct)
                                                <li class="d-flex justify-content-between align-items-center mb-1">
                                                    <span>{{ $ct->khuyenMai->Ten_KM ?? $ct->ID_KM }}</span>
                                                    <span class="text-success fw-semibold">-{{ number_format($ct->TienGiam) }} đ</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($booking->nhanVien)
                        @php
                            $staff = $booking->nhanVien;
                            $avatarRaw = $staff?->HinhAnh;
                            if ($avatarRaw && \Illuminate\Support\Str::startsWith($avatarRaw, ['http://', 'https://', '//'])) {
                                $avatar = $avatarRaw;
                                $host = parse_url($avatarRaw, PHP_URL_HOST);
                                $path = parse_url($avatarRaw, PHP_URL_PATH);
                                if ($host && in_array($host, ['10.0.2.2', 'localhost', '127.0.0.1'], true) && $path) {
                                    $avatar = request()->getSchemeAndHttpHost() . $path;
                                }
                            } elseif ($avatarRaw) {
                                $storageUrl = \Illuminate\Support\Facades\Storage::url($avatarRaw);
                                $avatar = url($storageUrl);
                            } else {
                                $avatar = null;
                            }
                            $avatar = $avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($staff->Ten_NV ?? 'NV');
                        @endphp
                        <div class="col-12">
                            <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                                <img src="{{ $avatar }}" alt="Nhân viên" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <p class="info-label mb-1"><i class="bi bi-person-badge me-1"></i>Nhân viên phụ trách</p>
                                    <p class="info-value mb-0">{{ $staff->Ten_NV }}</p>
                                    <small class="text-muted">SĐT: {{ $staff->SDT }}</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($booking->GhiChu)
                        <div class="col-12">
                            <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                                <p class="info-label text-warning-emphasis"><i class="bi bi-journal-text me-1"></i>Ghi chú của bạn:</p>
                                <p class="mb-0 text-dark fst-italic">"{{ $booking->GhiChu }}"</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Hiển thị danh sách buổi nếu là gói tháng --}}
                    @if($booking->LoaiDon === 'month' && count($sessions) > 0)
                    <div class="mt-4">
                        <button class="btn btn-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#sessionsList" aria-expanded="false">
                            <i class="bi bi-list-ul me-2"></i>Xem danh sách {{count($sessions)}} buổi của gói tháng
                        </button>
                        
                        <div class="collapse mt-3" id="sessionsList">
                            <div class="card card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th><i class="bi bi-calendar3"></i> Ngày làm</th>
                                                <th><i class="bi bi-clock"></i> Giờ làm</th>
                                                <th><i class="bi bi-info-circle"></i> Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sessions as $index => $session)
                                            @php
                                                // Tính giờ kết thúc dựa vào giờ bắt đầu + thời lượng
                                                $startTime = \Carbon\Carbon::parse($session->GioBatDau);
                                                $duration = $booking->ThoiLuongGio ?? $booking->dichVu->ThoiLuong ?? 2;
                                                $endTime = $startTime->copy()->addHours($duration);
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y') }}</td>
                                                <td>
                                                    <strong>{{ $startTime->format('H:i') }}</strong>
                                                    <span class="text-muted mx-1">→</span>
                                                    <strong>{{ $endTime->format('H:i') }}</strong>
                                                    <small class="text-muted">({{ $duration }}h)</small>
                                                </td>
                                                <td>
                                                    @if($session->TrangThaiBuoi == 'scheduled')
                                                        <span class="badge bg-info"><i class="bi bi-clock-history"></i> Đã lên lịch</span>
                                                    @elseif($session->TrangThaiBuoi == 'completed')
                                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Hoàn thành</span>
                                                    @elseif($session->TrangThaiBuoi == 'cancelled')
                                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Đã hủy</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $session->TrangThaiBuoi }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-5 text-center">
                        {{-- Show success/error messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        
                        @if(in_array($booking->TrangThaiDon, ['completed','done']) && !$existingRating)
                            <hr class="my-4 text-muted opacity-25">
                            <div class="card border-0 shadow-sm text-start">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3"><i class="bi bi-stars text-warning me-2"></i>Đánh giá đơn hàng</h5>
                                    <p class="text-muted small mb-3">Vui lòng đánh giá chất lượng dịch vụ (1-5 sao) và để lại nhận xét nếu có.</p>
                                    <form action="{{ route('bookings.rating', $booking->ID_DD) }}" method="POST" class="text-start" id="ratingForm">
                                        @csrf
                                        <div class="mb-3 d-flex gap-2 align-items-center rating-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button type="button" class="btn btn-link p-0 star-btn" data-value="{{ $i }}">
                                                    <i class="bi bi-star-fill fs-4 text-secondary"></i>
                                                </button>
                                            @endfor
                                            <input type="hidden" name="rating" id="ratingValue" value="5">
                                            <span class="ms-2 text-muted small" id="ratingLabel">Rất hài lòng (5/5)</span>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ratingComment" class="form-label small text-muted">Nhận xét (tùy chọn)</label>
                                            <textarea name="comment" id="ratingComment" rows="3" class="form-control" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success px-4 rounded-pill">
                                            Gửi đánh giá
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif($existingRating)
                            <hr class="my-4 text-muted opacity-25">
                            <div class="card border-0 shadow-sm text-start">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3"><i class="bi bi-chat-heart text-success me-2"></i>Đánh giá của bạn</h5>
                                    <div class="mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= (int)$existingRating->Diem ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                        @endfor
                                        <span class="ms-2 fw-semibold">{{ number_format($existingRating->Diem, 1) }}/5</span>
                                    </div>
                                    @if($existingRating->NhanXet)
                                        <p class="mb-0 text-muted fst-italic">"{{ $existingRating->NhanXet }}"</p>
                                    @else
                                        <p class="mb-0 text-muted">Bạn chưa để lại nhận xét.</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(in_array($booking->TrangThaiDon, ['finding_staff', 'assigned', 'confirmed']))
                            <hr class="my-4 text-muted opacity-25">
                            <p class="text-muted small mb-3">Nếu bạn muốn thay đổi ý định, bạn có thể hủy đơn hàng này.</p>
                            
                            <form id="cancelForm" action="{{ route('bookings.cancel', $booking->ID_DD) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="button" class="btn btn-outline-danger px-5 py-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-trash3 me-2"></i>Hủy đơn hàng này
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Cancel Confirmation Modal --}}
                    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="cancelModalLabel">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận hủy đơn hàng
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2"><strong>Bạn có chắc chắn muốn hủy đơn hàng này?</strong></p>
                                    <p class="text-muted small mb-0">
                                        @if($booking->LoaiDon === 'hour')
                                            Bạn sẽ được hoàn lại 100% số tiền đã thanh toán qua VNPay.
                                        @else
                                            Bạn sẽ được hoàn lại 80% giá trị các buổi chưa thực hiện.
                                        @endif
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không, giữ lại</button>
                                    <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelForm').submit();">
                                        <i class="bi bi-check-circle me-1"></i>Có, hủy đơn hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Rating functionality (only if rating elements exist)
        const starButtons = document.querySelectorAll('.star-btn');
        const ratingValue = document.getElementById('ratingValue');
        const ratingLabel = document.getElementById('ratingLabel');

        if (starButtons.length && ratingValue && ratingLabel) {
            const labels = {
                1: 'Rất tệ (1/5)',
                2: 'Chưa hài lòng (2/5)',
                3: 'Bình thường (3/5)',
                4: 'Hài lòng (4/5)',
                5: 'Rất hài lòng (5/5)',
            };

            function setRating(value) {
                ratingValue.value = value;
                ratingLabel.textContent = labels[value] || `${value}/5`;
                starButtons.forEach(btn => {
                    const v = parseInt(btn.dataset.value, 10);
                    const icon = btn.querySelector('i');
                    if (v <= value) {
                        icon.classList.remove('text-secondary');
                        icon.classList.add('text-warning');
                    } else {
                        icon.classList.remove('text-warning');
                        icon.classList.add('text-secondary');
                    }
                });
            }

            starButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const value = parseInt(btn.dataset.value, 10);
                    setRating(value);
                });
            });

            setRating(parseInt(ratingValue.value || '5', 10));
        }

        // Handle staff suggestion clicks
        const suggestionButtons = document.querySelectorAll('.apply-suggestion-btn');
        console.log('Found suggestion buttons:', suggestionButtons.length);
        
        if (suggestionButtons.length === 0) {
            console.warn('No suggestion buttons found! Check if suggestions are being rendered.');
        }
        
        suggestionButtons.forEach(btn => {
            console.log('Attaching event to button:', btn);
            btn.addEventListener('click', async function(e) {
                console.log('Button clicked!', {
                    staffId: this.dataset.staffId,
                    date: this.dataset.suggestedDate,
                    time: this.dataset.suggestedTime
                });
                e.preventDefault();
                
                const staffId = this.dataset.staffId;
                const staffName = this.dataset.staffName;
                const suggestedDate = this.dataset.suggestedDate;
                const suggestedTime = this.dataset.suggestedTime;
                const bookingId = this.dataset.bookingId;
                
                // Format date for display
                const dateObj = new Date(suggestedDate);
                const formattedDate = dateObj.toLocaleDateString('vi-VN');
                
                // Confirm with user
                if (!confirm(`Bạn có chắc muốn chọn nhân viên ${staffName} vào ngày ${formattedDate} lúc ${suggestedTime}?`)) {
                    return;
                }
                
                // Disable button and show loading
                this.disabled = true;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang xử lý...';
                
                try {
                    const response = await fetch(`/my-bookings/${bookingId}/apply-suggestion`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_nv: staffId,
                            suggested_date: suggestedDate,
                            suggested_time: suggestedTime
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Check if payment is required (VNPay surcharge)
                        if (data.requires_payment && data.payment_url) {
                            alert(data.message);
                            // Redirect to VNPay payment page
                            window.location.href = data.payment_url;
                        } else {
                            // Show success message
                            let message = `Thành công! Đã cập nhật đơn với nhân viên ${data.data.staff_name} vào ${data.data.new_date} lúc ${data.data.new_time}`;
                            
                            if (data.data.surcharge_added) {
                                message += `\n\nPhụ thu giờ cao điểm: ${data.data.surcharge_amount.toLocaleString('vi-VN')}đ`;
                            }
                            
                            alert(message);
                            // Reload page to show updated info
                            window.location.reload();
                        }
                    } else {
                        // Show error message
                        console.error('Server error:', data);
                        const errorMsg = data.error || data.message || 'Không thể áp dụng gợi ý';
                        alert('Lỗi: ' + errorMsg);
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    }
                } catch (error) {
                    console.error('Error applying suggestion:', error);
                    alert('Có lỗi xảy ra khi áp dụng gợi ý. Vui lòng kiểm tra console để biết thêm chi tiết.');
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            });
        });

        // Add hover effect to suggestion cards
        const suggestionCards = document.querySelectorAll('.suggestion-card');
        suggestionCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                this.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '';
                this.style.transform = '';
            });
        });
    });
</script>
@endsection
