@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@push('styles')
<style>
    .container { grid-template-columns: 14rem auto; }
    @media (max-width: 1200px) { .container { grid-template-columns: 7rem auto; } }
    @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }

    aside { transition: all 300ms ease; position: sticky; top: 0; height: 100vh; }
    body.sidebar-collapsed .container { grid-template-columns: 5rem auto; }

    .page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .card { background: var(--color-white); padding: var(--card-padding); border-radius: var(--card-border-radius); box-shadow: var(--box-shadow); border: 1px solid rgba(0,0,0,0.02); transition: all 300ms ease; }
    .card:hover { box-shadow: none; transform: translateY(-2px); }
    .filter-card { margin-bottom: 1rem; }
    .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end; }
    .filters label { font-weight: 600; margin-bottom: 0.35rem; display: block; color: var(--color-dark); }
    .filters input, .filters select { width: 100%; padding: 0.75rem 0.9rem; border-radius: var(--border-radius-2); background: var(--color-background); border: 1px solid var(--color-light); color: var(--color-dark); }

    .btn { border: none; border-radius: var(--border-radius-2); padding: 0.7rem 1.4rem; font-weight: 600; cursor: pointer; transition: all 200ms ease; background: var(--color-light); color: var(--color-dark); }
    .btn:hover { filter: brightness(0.98); }
    .primary-btn { background: var(--color-primary); color: var(--color-white); box-shadow: 0 8px 18px rgba(115,128,236,0.25); }
    .btn.danger { background: var(--color-danger); color: var(--color-white); box-shadow: 0 8px 18px rgba(255,119,130,0.2); }
    .ghost-btn { background: transparent; color: var(--color-dark); border: 1px dashed var(--color-light); }

    .alert { margin-bottom: 1rem; padding: 0.9rem 1rem; border-radius: var(--border-radius-2); border: 1px solid; }
    .alert.success { background: #e8f5e9; border-color: #a5d6a7; color: #2e7d32; }
    .alert.danger { background: #ffebee; border-color: #ffcdd2; color: #c62828; }

    .form-card { margin-bottom: 1.5rem; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
    .form-control label { font-weight: 600; display: block; margin-bottom: 0.35rem; color: var(--color-dark); }
    .form-control input, .form-control textarea, .form-control select { width: 100%; background: var(--color-background); border: 1px solid var(--color-light); border-radius: var(--border-radius-2); padding: 0.75rem; color: var(--color-dark); }
    .form-control textarea { min-height: 110px; resize: vertical; }
    .form-actions { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-top: 1rem; justify-content: flex-end; grid-column: 1 / -1; }
    .card-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .hint { color: var(--color-info-dark); font-size: 0.9rem; }

    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 720px; }
    th { text-align: left; padding: 0.85rem; font-size: 0.85rem; text-transform: uppercase; color: var(--color-dark); }
    td { padding: 0.85rem; background: var(--color-white); border-bottom: 1px solid var(--color-light); color: var(--color-dark-variant); }
    tbody tr:hover td { background: var(--color-background); color: var(--color-dark); }
    .table-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem; gap: 1rem; }
    .muted { color: var(--color-info-dark); }
    .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .actions .btn { padding: 0.45rem 0.9rem; }
    .badge-status { padding: 0.35rem 0.8rem; border-radius: 999px; font-weight: 600; font-size: 0.8rem; }
    .badge-activated { background: #e8f5e9; color: #2e7d32; }
    .badge-deactivated { background: #fff3e0; color: #e65100; }
    .badge-deleted { background: #ffebee; color: #c62828; }

    .pagination { display: flex; gap: 0.45rem; align-items: center; list-style: none; margin-top: 1rem; }
    .pagination li a, .pagination li span { display: inline-block; padding: 0.5rem 0.85rem; border-radius: var(--border-radius-1); background: var(--color-white); border: 1px solid var(--color-light); color: var(--color-dark); }
    .pagination li.active span { background: var(--color-primary); color: var(--color-white); border-color: var(--color-primary); }
    .pagination li.disabled span { color: var(--color-info-dark); background: var(--color-background); }

    @media screen and (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        
        .page-header div {
            margin-bottom: 0.5rem;
        }

        .filters {
            grid-template-columns: 1fr;
        }

        /* Hide less critical columns on mobile */
        th:nth-child(1), td:nth-child(1), /* ID */
        th:nth-child(4), td:nth-child(4)  /* Max Discount */
        {
            display: none;
        }
    }
</style>
@endpush

@section('content')
@php
    $formAction = $isEditing && $editingId ? route('admin.promotions.update', $editingId) : route('admin.promotions.store');
    $formMethod = $isEditing ? 'PUT' : 'POST';
    $formTitle = $isEditing ? 'Cập nhật khuyến mãi' : 'Thêm khuyến mãi mới';
    $submitLabel = $isEditing ? 'Lưu thay đổi' : 'Thêm khuyến mãi';
    $updateRouteTemplate = route('admin.promotions.update', '__ID__');
@endphp

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'promotions'])

    <main>
        <div class="page-header">
            <div>
                <h1>Khuyến mãi</h1>
                <p class="text-muted">Danh sách khuyến mãi, tạo/sửa, bật/tắt. Ngày hết hạn chỉ dùng để tính trạng thái khi cập nhật.</p>
            </div>
            <button type="button" class="btn primary-btn" id="reset-form">Reset Form</button>
        </div>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert danger">
                <strong>Vui lòng kiểm tra lại:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card filter-card">
            <form id="filter-form" class="filters" method="GET" action="{{ route('admin.promotions.index') }}">
                <div class="field">
                    <label for="search">Tìm theo tên</label>
                    <input type="text" id="search" name="search" placeholder="Nhập tên khuyến mãi..." value="{{ $search }}">
                </div>
                <div class="field">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status">
                        <option value="">Tất cả</option>
                        <option value="activated" @selected($status === 'activated')>Đang hoạt động</option>
                        <option value="deactivated" @selected($status === 'deactivated')>Ngưng</option>
                    </select>
                </div>
                <div class="field">
                    <button type="submit" class="btn primary-btn">Lọc</button>
                </div>
            </form>
        </div>

        <div class="card form-card">
            <div class="card-header">
                <h2 id="form-title">{{ $formTitle }}</h2>
            </div>
            <form id="promotion-form" action="{{ $formAction }}" method="POST" class="form-grid" novalidate>
                @csrf
                <input type="hidden" name="_method" id="form-method" value="{{ $formMethod }}">
                <input type="hidden" name="editing_id" id="editing-id" value="{{ $editingId }}">

                <div class="form-control">
                    <label for="Ten_KM">Tên khuyến mãi <span class="danger">*</span></label>
                    <input type="text" id="Ten_KM" name="Ten_KM" value="{{ old('Ten_KM') }}" required>
                </div>

                <div class="form-control">
                    <label for="PhanTramGiam">% giảm <span class="danger">*</span></label>
                    <input type="number" id="PhanTramGiam" name="PhanTramGiam" min="0" max="100" step="0.1" value="{{ old('PhanTramGiam') }}" required>
                </div>

                    <div class="form-control">
                        <label for="GiamToiDa">Giảm tối đa (VND) <span class="danger">*</span></label>
                        <input type="number" id="GiamToiDa" name="GiamToiDa" min="0" step="0.01" value="{{ old('GiamToiDa') }}" required>
                    </div>

                <div class="form-control">
                    <label for="expiry_date">Ngày hết hạn</label>
                    <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                </div>

                <div class="form-control" style="grid-column: 1 / -1;">
                    <label for="MoTa">Mô tả</label>
                    <textarea id="MoTa" name="MoTa" placeholder="Mô tả khuyến mãi">{{ old('MoTa') }}</textarea>
                </div>

                <div class="form-control" style="grid-column: 1 / -1; border-top: 1px solid var(--color-light); padding-top: 1rem; margin-top: 0.5rem;">
                    <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">Thông báo khách hàng</h3>
                    <p class="hint" style="margin-bottom: 1rem;">Gửi email thông báo cho khách hàng khi tạo hoặc cập nhật.</p>
                </div>

                <div class="form-control">
                    <label for="notification_target">Gửi tới</label>
                    <select id="notification_target" name="notification_target">
                        <option value="">-- Không gửi --</option>
                        <option value="all">Tất cả khách hàng</option>
                        <option value="loyal">Khách hàng thân thiết (> 1tr)</option>
                        <option value="new">Khách hàng mới</option>
                    </select>
                </div>

                <div class="form-control">
                    <label for="notification_note">Nội dung ghi chú (Email)</label>
                    <textarea id="notification_note" name="notification_note" placeholder="Nhập nội dung hiển thị trong email..." style="min-height: 80px;"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="form-submit" class="btn primary-btn">{{ $submitLabel }}</button>
                    <button type="button" class="btn ghost-btn" id="reset-form-secondary">Hủy chỉnh sửa</button>
                </div>
            </form>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <h2>Bảng khuyến mãi</h2>
                <span class="muted">{{ $promotions->total() }} mục</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID_KM</th>
                            <th>Tên</th>
                            <th>% giảm</th>
                            <th>Giảm tối đa</th>
                            <th>Ngày hết hạn</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promotions as $promotion)
                            <tr>
                                <td>{{ $promotion->ID_KM }}</td>
                                <td>{{ $promotion->Ten_KM }}</td>
                                <td>{{ rtrim(rtrim(number_format($promotion->PhanTramGiam, 2, '.', ''), '0'), '.') }}%</td>
                                <td>{{ number_format($promotion->GiamToiDa, 0, ',', '.') }} đ</td>
                                <td>{{ $promotion->NgayHetHan ? \Carbon\Carbon::parse($promotion->NgayHetHan)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($promotion->is_delete)
                                        <span class="badge-status badge-deleted">Đã xoá</span>
                                    @elseif($promotion->TrangThai === 'activated')
                                        <span class="badge-status badge-activated">Activated</span>
                                    @else
                                        <span class="badge-status badge-deactivated">Deactivated</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <button
                                            type="button"
                                            class="btn btn-edit-promotion"
                                            data-id="{{ $promotion->ID_KM }}"
                                            data-name="{{ $promotion->Ten_KM }}"
                                            data-percent="{{ $promotion->PhanTramGiam }}"
                                            data-max="{{ $promotion->GiamToiDa }}"
                                            data-status="{{ $promotion->TrangThai }}"
                                            data-expiry="{{ $promotion->NgayHetHan }}"
                                            data-description="{{ $promotion->MoTa }}"
                                        >
                                            Sửa
                                        </button>
                                        @if($promotion->is_delete)
                                            <form action="{{ route('admin.promotions.restore', $promotion->ID_KM) }}" method="POST" onsubmit="return confirm('Khôi phục khuyến mãi {{ $promotion->Ten_KM }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn primary-btn">Khôi phục</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.promotions.destroy', $promotion->ID_KM) }}" method="POST" onsubmit="return confirm('Xoá khuyến mãi {{ $promotion->Ten_KM }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn danger">Xoá</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Chưa có khuyến mãi nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $promotions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const promoForm = document.getElementById('promotion-form');
        const methodInput = document.getElementById('form-method');
        const editingInput = document.getElementById('editing-id');
        const titleEl = document.getElementById('form-title');
        const submitBtn = document.getElementById('form-submit');
        const resetButtons = [document.getElementById('reset-form'), document.getElementById('reset-form-secondary')].filter(Boolean);
        const updateRouteTemplate = @json($updateRouteTemplate);
        const storeRoute = @json(route('admin.promotions.store'));

        const clearFormValues = () => {
            ['Ten_KM', 'PhanTramGiam', 'GiamToiDa', 'MoTa', 'expiry_date', 'notification_note'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) { el.value = ''; }
            });
            document.getElementById('notification_target').value = '';
        };

        const switchToCreate = () => {
            promoForm.action = storeRoute;
            methodInput.value = 'POST';
            editingInput.value = '';
            titleEl.textContent = 'Thêm khuyến mãi mới';
            submitBtn.textContent = 'Thêm khuyến mãi';
            clearFormValues();
        };

        const switchToUpdate = (payload) => {
            promoForm.action = updateRouteTemplate.replace('__ID__', payload.id);
            methodInput.value = 'PUT';
            editingInput.value = payload.id;
            titleEl.textContent = 'Cập nhật khuyến mãi';
            submitBtn.textContent = 'Lưu thay đổi';
            document.getElementById('Ten_KM').value = payload.name || '';
            document.getElementById('PhanTramGiam').value = payload.percent || '';
            document.getElementById('GiamToiDa').value = payload.max || '';
            document.getElementById('MoTa').value = payload.description || '';
            // Status is auto-determined by expiry date
            document.getElementById('expiry_date').value = payload.expiry || '';
            
            // Reset notification fields when editing
            document.getElementById('notification_target').value = '';
            document.getElementById('notification_note').value = '';
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.querySelectorAll('.btn-edit-promotion').forEach((button) => {
            button.addEventListener('click', () => {
                const payload = {
                    id: button.dataset.id,
                    name: button.dataset.name,
                    percent: button.dataset.percent,
                    max: button.dataset.max,
                    status: button.dataset.status,
                    expiry: button.dataset.expiry,
                    description: button.dataset.description,
                };
                switchToUpdate(payload);
            });
        });

        resetButtons.forEach((btn) => btn.addEventListener('click', switchToCreate));

        const statusSelect = document.getElementById('status');
        const filterForm = document.getElementById('filter-form');
        if (statusSelect && filterForm) {
            statusSelect.addEventListener('change', () => filterForm.submit());
        }
    });
</script>
@endpush
