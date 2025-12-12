@extends('layouts.admin')

@section('title', 'Danh sách dịch vụ')

@push('styles')
<style>
    .container {
        grid-template-columns: 14rem auto;
    }

    aside {
        transition: all 300ms ease;
        position: sticky;
        top: 0;
        height: 100vh;
    }

    body.sidebar-collapsed .container {
        grid-template-columns: 5rem auto;
    }

    @media screen and (max-width: 1200px) {
        .container {
            grid-template-columns: 7rem auto;
        }
    }

    @media screen and (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
        }
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .page-header p {
        margin-top: 0.3rem;
    }

    .card {
        background: var(--color-white);
        padding: var(--card-padding);
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .card:hover {
        box-shadow: none;
        transform: translateY(-2px);
    }

    .filter-card {
        margin-bottom: 1rem;
    }

    .filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        align-items: flex-end;
    }

    .filters .field label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.35rem;
        color: var(--color-dark);
    }

    .filters input,
    .filters select {
        width: 100%;
        padding: 0.75rem 0.9rem;
        border-radius: var(--border-radius-2);
        background: var(--color-background);
        border: 1px solid var(--color-light);
        color: var(--color-dark);
    }

    .btn {
        border: none;
        border-radius: var(--border-radius-2);
        padding: 0.7rem 1.4rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 200ms ease;
        background: var(--color-light);
        color: var(--color-dark);
    }

    .btn:hover {
        filter: brightness(0.98);
    }

    .primary-btn {
        background: var(--color-primary);
        color: var(--color-white);
        box-shadow: 0 8px 18px rgba(115, 128, 236, 0.25);
    }

    .btn.danger {
        background: var(--color-danger);
        color: var(--color-white);
        box-shadow: 0 8px 18px rgba(255, 119, 130, 0.2);
    }

    .ghost-btn {
        background: transparent;
        color: var(--color-dark);
        border: 1px dashed var(--color-light);
    }

    .alert {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        border-radius: var(--border-radius-2);
        border: 1px solid;
    }

    .alert.success {
        background: #e8f5e9;
        border-color: #a5d6a7;
        color: #2e7d32;
    }

    .alert.danger {
        background: #ffebee;
        border-color: #ffcdd2;
        color: #c62828;
    }

    .form-card {
        margin-bottom: 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }

    .form-control label {
        font-weight: 600;
        display: block;
        margin-bottom: 0.35rem;
        color: var(--color-dark);
    }

    .form-control input,
    .form-control textarea {
        width: 100%;
        background: var(--color-background);
        border: 1px solid var(--color-light);
        border-radius: var(--border-radius-2);
        padding: 0.75rem;
        color: var(--color-dark);
    }

    .form-control textarea {
        min-height: 110px;
        resize: vertical;
    }

    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1rem;
        justify-content: flex-end;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .hint {
        color: var(--color-info-dark);
        font-size: 0.9rem;
    }

    .table-card .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    th {
        text-align: left;
        padding: 0.85rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--color-dark);
    }

    td {
        padding: 0.85rem;
        background: var(--color-white);
        border-bottom: 1px solid var(--color-light);
        color: var(--color-dark-variant);
    }

    tbody tr:hover td {
        background: var(--color-background);
        color: var(--color-dark);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        gap: 1rem;
    }

    .muted {
        color: var(--color-info-dark);
    }

    .actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .actions .btn {
        padding: 0.45rem 0.9rem;
    }

    .tag {
        background: var(--color-light);
        color: var(--color-dark);
        padding: 0.2rem 0.7rem;
        border-radius: 999px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .badge-status { padding: 0.35rem 0.8rem; border-radius: 999px; font-weight: 600; font-size: 0.8rem; }
    .badge-active { background: #e8f5e9; color: #2e7d32; }
    .badge-deleted { background: #ffebee; color: #c62828; }

    .pagination {
        display: flex;
        gap: 0.45rem;
        align-items: center;
        list-style: none;
        margin-top: 1rem;
    }

    .pagination li a,
    .pagination li span {
        display: inline-block;
        padding: 0.5rem 0.85rem;
        border-radius: var(--border-radius-1);
        background: var(--color-white);
        border: 1px solid var(--color-light);
        color: var(--color-dark);
    }

    .pagination li.active span {
        background: var(--color-primary);
        color: var(--color-white);
        border-color: var(--color-primary);
    }

    .pagination li.disabled span {
        color: var(--color-info-dark);
        background: var(--color-background);
    }

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
        th:nth-child(4), td:nth-child(4), /* Area */
        th:nth-child(5), td:nth-child(5)  /* Rooms */
        {
            display: none;
        }
    }
</style>
@endpush

@section('content')
@php
    $formAction = $isEditing && $editingId ? route('admin.services.update', $editingId) : route('admin.services.store');
    $formMethod = $isEditing ? 'PUT' : 'POST';
    $formTitle = $isEditing ? 'Cập nhật dịch vụ' : 'Thêm dịch vụ mới';
    $submitLabel = $isEditing ? 'Lưu thay đổi' : 'Thêm dịch vụ';
    $updateRouteTemplate = route('admin.services.update', '__ID__');
@endphp

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'services'])

    <main>
        <div class="page-header">
            <div>
                <h1>Danh sách dịch vụ</h1>
                <p class="text-muted">Quản lý bảng dịch vụ, tìm kiếm theo tên và sắp xếp theo giá/thời lượng.</p>
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
            <form id="filter-form" class="filters" method="GET" action="{{ route('admin.services.index') }}">
                <div class="field">
                    <label for="search">Tìm theo tên</label>
                    <input type="text" id="search" name="search" placeholder="Nhập tên dịch vụ..."
                        value="{{ $search }}">
                </div>
                <div class="field">
                    <label for="sort">Sắp xếp</label>
                    <select name="sort" id="sort">
                        <option value="">Mặc định</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>Giá tăng dần</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>Giá giảm dần</option>
                        <option value="duration_asc" @selected($sort === 'duration_asc')>Thời lượng tăng dần</option>
                        <option value="duration_desc" @selected($sort === 'duration_desc')>Thời lượng giảm dần</option>
                    </select>
                </div>
                <div class="field">
                    <button type="submit" class="btn primary-btn">Áp dụng</button>
                </div>
            </form>
        </div>

        <div class="card form-card">
            <div class="card-header">
                <h2 id="form-title">{{ $formTitle }}</h2>
            </div>
            <form id="service-form" action="{{ $formAction }}" method="POST" class="form-grid" novalidate>
                @csrf
                <input type="hidden" name="_method" id="form-method" value="{{ $formMethod }}">
                <input type="hidden" name="editing_id" id="editing-id" value="{{ $editingId }}">

                <div class="form-control">
                    <label for="TenDV">Tên dịch vụ <span class="danger">*</span></label>
                    <input type="text" id="TenDV" name="TenDV" value="{{ old('TenDV') }}" required>
                </div>

                <div class="form-control">
                    <label for="GiaDV">Giá (VND) <span class="danger">*</span></label>
                    <input type="number" id="GiaDV" name="GiaDV" min="0" step="0.01"
                        value="{{ old('GiaDV') }}" required>
                </div>

                <div class="form-control">
                    <label for="ThoiLuong">Thời lượng (giờ) <span class="danger">*</span></label>
                    <input type="number" id="ThoiLuong" name="ThoiLuong" min="0" step="0.25"
                        value="{{ old('ThoiLuong') }}" required>
                </div>

                <div class="form-control">
                    <label for="DienTichToiDa">Diện tích tối đa (m²)</label>
                    <input type="number" id="DienTichToiDa" name="DienTichToiDa" min="0" step="0.1"
                        value="{{ old('DienTichToiDa') }}">
                </div>

                <div class="form-control">
                    <label for="SoPhong">Số phòng</label>
                    <input type="number" id="SoPhong" name="SoPhong" min="0" step="1"
                        value="{{ old('SoPhong') }}">
                </div>

                <div class="form-control" style="grid-column: 1 / -1;">
                    <label for="MoTa">Mô tả</label>
                    <textarea id="MoTa" name="MoTa" placeholder="Mô tả ngắn về dịch vụ">{{ old('MoTa') }}</textarea>
                </div>

                <div class="form-actions" style="grid-column: 1 / -1;">
                    <button type="submit" id="form-submit" class="btn primary-btn">{{ $submitLabel }}</button>
                    <button type="button" class="btn ghost-btn" id="reset-form-secondary">Huỷ chỉnh sửa</button>
                </div>
            </form>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <h2>Bảng dịch vụ</h2>
                <span class="muted">{{ $services->total() }} mục</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID_DV</th>
                            <th>Tên dịch vụ</th>
                            <th>Giá</th>
                            <th>Diện tích tối đa</th>
                            <th>Số phòng</th>
                            <th>Thời lượng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>{{ $service->ID_DV }}</td>
                                <td>{{ $service->TenDV }}</td>
                                <td>{{ number_format($service->GiaDV, 0, ',', '.') }} đ</td>
                                <td>{{ $service->DienTichToiDa !== null ? rtrim(rtrim(number_format($service->DienTichToiDa, 2, '.', ''), '0'), '.') : '—' }}</td>
                                <td>{{ $service->SoPhong ?? '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format($service->ThoiLuong, 2, '.', ''), '0'), '.') }} giờ</td>
                                <td>
                                    @if($service->is_delete)
                                        <span class="badge-status badge-deleted">Đã xoá</span>
                                    @else
                                        <span class="badge-status badge-active">Hoạt động</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <button
                                            type="button"
                                            class="btn btn-edit-service"
                                            data-id="{{ $service->ID_DV }}"
                                            data-name="{{ $service->TenDV }}"
                                            data-description="{{ $service->MoTa }}"
                                            data-price="{{ $service->GiaDV }}"
                                            data-max-area="{{ $service->DienTichToiDa }}"
                                            data-rooms="{{ $service->SoPhong }}"
                                            data-duration="{{ $service->ThoiLuong }}"
                                        >
                                            Sửa
                                        </button>
                                        @if($service->is_delete)
                                            <form action="{{ route('admin.services.restore', $service->ID_DV) }}" method="POST"
                                                onsubmit="return confirm('Khôi phục dịch vụ {{ $service->TenDV }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn primary-btn">Khôi phục</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.services.destroy', $service->ID_DV) }}" method="POST"
                                                onsubmit="return confirm('Xoá dịch vụ {{ $service->TenDV }}?');">
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
                                <td colspan="8" class="text-muted">Chưa có dịch vụ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $services->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const serviceForm = document.getElementById('service-form');
        const methodInput = document.getElementById('form-method');
        const editingInput = document.getElementById('editing-id');
        const titleEl = document.getElementById('form-title');
        const submitBtn = document.getElementById('form-submit');
        const resetButtons = [document.getElementById('reset-form'), document.getElementById('reset-form-secondary')].filter(Boolean);
        const updateRouteTemplate = @json($updateRouteTemplate);
        const storeRoute = @json(route('admin.services.store'));

        const clearFormValues = () => {
            ['TenDV', 'GiaDV', 'ThoiLuong', 'DienTichToiDa', 'SoPhong', 'MoTa'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = '';
                }
            });
        };

        const switchToCreate = () => {
            serviceForm.action = storeRoute;
            methodInput.value = 'POST';
            editingInput.value = '';
            titleEl.textContent = 'Thêm dịch vụ mới';
            submitBtn.textContent = 'Thêm dịch vụ';
            clearFormValues();
        };

        const switchToUpdate = (payload) => {
            serviceForm.action = updateRouteTemplate.replace('__ID__', payload.id);
            methodInput.value = 'PUT';
            editingInput.value = payload.id;
            titleEl.textContent = 'Cập nhật dịch vụ';
            submitBtn.textContent = 'Lưu thay đổi';
            document.getElementById('TenDV').value = payload.name ?? '';
            document.getElementById('GiaDV').value = payload.price ?? '';
            document.getElementById('ThoiLuong').value = payload.duration_hours ?? '';
            document.getElementById('DienTichToiDa').value = payload.max_area ?? '';
            document.getElementById('SoPhong').value = payload.rooms ?? '';
            document.getElementById('MoTa').value = payload.description ?? '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.querySelectorAll('.btn-edit-service').forEach((button) => {
            button.addEventListener('click', () => {
                const payload = {
                    id: button.dataset.id,
                    name: button.dataset.name || '',
                    description: button.dataset.description || '',
                    price: button.dataset.price || '',
                    max_area: button.dataset.maxArea || '',
                    rooms: button.dataset.rooms || '',
                    duration_hours: button.dataset.duration || '',
                };
                switchToUpdate(payload);
            });
        });

        resetButtons.forEach((btn) => btn.addEventListener('click', switchToCreate));

        // Keep sort selection reactive
        const sortSelect = document.getElementById('sort');
        const filterForm = document.getElementById('filter-form');
        if (sortSelect && filterForm) {
            sortSelect.addEventListener('change', () => filterForm.submit());
        }


    });
</script>
@endpush
