@extends('layouts.admin')

@section('title', 'Quản lý gói tháng')

@push('styles')
<style>
    main { margin-top: 1.4rem; }
    .container { grid-template-columns: 14rem auto; }
    @media (max-width: 1200px) { .container { grid-template-columns: 7rem auto; } }
    @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }

    aside { transition: all 300ms ease; position: sticky; top: 0; height: 100vh; }
    .logo-collapsed { display: none; }
    aside.collapsed { width: 5rem; }
    aside.collapsed .logo-full { display: none; }
    aside.collapsed .logo-collapsed { display: block; }
    aside.collapsed .sidebar h3 { display: none; }
    aside.collapsed .sidebar a { justify-content: center; }
    aside.collapsed .close { display: none; }
    body.sidebar-collapsed .container { grid-template-columns: 5rem auto; }

    .page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .card { background: var(--color-white); padding: var(--card-padding); border-radius: var(--card-border-radius); box-shadow: var(--box-shadow); border: 1px solid rgba(0,0,0,0.02); transition: all 300ms ease; }
    .card:hover { box-shadow: none; transform: translateY(-2px); }
    .filter-card { margin-bottom: 1rem; }
    .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: flex-end; }
    .filters label { font-weight: 600; margin-bottom: 0.35rem; display: block; color: var(--color-dark); }
    .filters input { width: 100%; padding: 0.75rem 0.9rem; border-radius: var(--border-radius-2); background: var(--color-background); border: 1px solid var(--color-light); color: var(--color-dark); }

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
    .form-control input, .form-control textarea { width: 100%; background: var(--color-background); border: 1px solid var(--color-light); border-radius: var(--border-radius-2); padding: 0.75rem; color: var(--color-dark); }
    .form-control textarea { min-height: 110px; resize: vertical; }
    .form-actions { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-top: 1rem; justify-content: flex-end; grid-column: 1 / -1; }
    .card-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .hint { color: var(--color-info-dark); font-size: 0.9rem; }

    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 640px; }
    th { text-align: left; padding: 0.85rem; font-size: 0.85rem; text-transform: uppercase; color: var(--color-dark); }
    td { padding: 0.85rem; background: var(--color-white); border-bottom: 1px solid var(--color-light); color: var(--color-dark-variant); }
    tbody tr:hover td { background: var(--color-background); color: var(--color-dark); }
    .table-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem; gap: 1rem; }
    .muted { color: var(--color-info-dark); }
    .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .actions .btn { padding: 0.45rem 0.9rem; }

    .pagination { display: flex; gap: 0.45rem; align-items: center; list-style: none; margin-top: 1rem; }
    .pagination li a, .pagination li span { display: inline-block; padding: 0.5rem 0.85rem; border-radius: var(--border-radius-1); background: var(--color-white); border: 1px solid var(--color-light); color: var(--color-dark); }
    .pagination li.active span { background: var(--color-primary); color: var(--color-white); border-color: var(--color-primary); }
    .pagination li.disabled span { color: var(--color-info-dark); background: var(--color-background); }
</style>
@endpush

@section('content')
@php
    $formAction = $isEditing && $editingId ? route('admin.packages.update', $editingId) : route('admin.packages.store');
    $formMethod = $isEditing ? 'PUT' : 'POST';
    $formTitle = $isEditing ? 'Cập nhật gói tháng' : 'Thêm gói tháng mới';
    $submitLabel = $isEditing ? 'Lưu thay đổi' : 'Thêm gói tháng';
    $updateRouteTemplate = route('admin.packages.update', '__ID__');
@endphp

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'packages'])

    <main>
        <div class="page-header">
            <div>
                <h1>Gói tháng</h1>
                <p class="text-muted">Danh sách gói tháng, tạo/sửa/xóa với kiểm tra ràng buộc đơn đặt.</p>
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
            <form id="filter-form" class="filters" method="GET" action="{{ route('admin.packages.index') }}">
                <div class="field">
                    <label for="search">Tìm theo tên</label>
                    <input type="text" id="search" name="search" placeholder="Nhập tên gói..."
                        value="{{ $search }}">
                </div>
                <div class="field">
                    <button type="submit" class="btn primary-btn">Tìm kiếm</button>
                </div>
            </form>
        </div>

        <div class="card form-card">
            <div class="card-header">
                <h2 id="form-title">{{ $formTitle }}</h2>
            </div>
            <form id="package-form" action="{{ $formAction }}" method="POST" class="form-grid" novalidate>
                @csrf
                <input type="hidden" name="_method" id="form-method" value="{{ $formMethod }}">
                <input type="hidden" name="editing_id" id="editing-id" value="{{ $editingId }}">

                <div class="form-control">
                    <label for="TenGoi">Tên gói <span class="danger">*</span></label>
                    <input type="text" id="TenGoi" name="TenGoi" value="{{ old('TenGoi') }}" required>
                </div>

                <div class="form-control">
                    <label for="SoNgay">Số ngày <span class="danger">*</span></label>
                    <input type="number" id="SoNgay" name="SoNgay" min="1" step="1" value="{{ old('SoNgay') }}" required>
                </div>

                <div class="form-control">
                    <label for="PhanTramGiam">Phần trăm giảm (%) <span class="danger">*</span></label>
                    <input type="number" id="PhanTramGiam" name="PhanTramGiam" min="0" max="100" step="0.1"
                        value="{{ old('PhanTramGiam') }}" required>
                </div>

                <div class="form-control" style="grid-column: 1 / -1;">
                    <label for="Mota">Mô tả</label>
                    <textarea id="Mota" name="Mota" placeholder="Mô tả ngắn về gói tháng">{{ old('Mota') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="form-submit" class="btn primary-btn">{{ $submitLabel }}</button>
                    <button type="button" class="btn ghost-btn" id="reset-form-secondary">Hủy chỉnh sửa</button>
                </div>
            </form>
        </div>

        <div class="card table-card">
            <div class="table-header">
                <h2>Bảng gói tháng</h2>
                <span class="muted">{{ $packages->total() }} mục</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID_Goi</th>
                            <th>Tên gói</th>
                            <th>Số ngày</th>
                            <th>% giảm</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $package)
                            <tr>
                                <td>{{ $package->ID_Goi }}</td>
                                <td>{{ $package->TenGoi }}</td>
                                <td>{{ $package->SoNgay }}</td>
                                <td>{{ rtrim(rtrim(number_format($package->PhanTramGiam, 2, '.', ''), '0'), '.') }}%</td>
                                <td>{{ $package->Mota ?? '—' }}</td>
                                <td>
                                    <div class="actions">
                                        <button
                                            type="button"
                                            class="btn btn-edit-package"
                                            data-id="{{ $package->ID_Goi }}"
                                            data-name="{{ $package->TenGoi }}"
                                            data-days="{{ $package->SoNgay }}"
                                            data-discount="{{ $package->PhanTramGiam }}"
                                            data-description="{{ $package->Mota }}"
                                        >
                                            Sửa
                                        </button>
                                        <form action="{{ route('admin.packages.destroy', $package->ID_Goi) }}" method="POST"
                                            onsubmit="return confirm('Xóa gói {{ $package->TenGoi }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn danger">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">Chưa có gói tháng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $packages->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const packageForm = document.getElementById('package-form');
        const methodInput = document.getElementById('form-method');
        const editingInput = document.getElementById('editing-id');
        const titleEl = document.getElementById('form-title');
        const submitBtn = document.getElementById('form-submit');
        const resetButtons = [document.getElementById('reset-form'), document.getElementById('reset-form-secondary')].filter(Boolean);
        const updateRouteTemplate = @json($updateRouteTemplate);
        const storeRoute = @json(route('admin.packages.store'));

        const clearFormValues = () => {
            ['TenGoi', 'SoNgay', 'PhanTramGiam', 'Mota'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) { el.value = ''; }
            });
        };

        const switchToCreate = () => {
            packageForm.action = storeRoute;
            methodInput.value = 'POST';
            editingInput.value = '';
            titleEl.textContent = 'Thêm gói tháng mới';
            submitBtn.textContent = 'Thêm gói tháng';
            clearFormValues();
        };

        const switchToUpdate = (payload) => {
            packageForm.action = updateRouteTemplate.replace('__ID__', payload.id);
            methodInput.value = 'PUT';
            editingInput.value = payload.id;
            titleEl.textContent = 'Cập nhật gói tháng';
            submitBtn.textContent = 'Lưu thay đổi';
            document.getElementById('TenGoi').value = payload.name || '';
            document.getElementById('SoNgay').value = payload.days || '';
            document.getElementById('PhanTramGiam').value = payload.discount || '';
            document.getElementById('Mota').value = payload.description || '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.querySelectorAll('.btn-edit-package').forEach((button) => {
            button.addEventListener('click', () => {
                const payload = {
                    id: button.dataset.id,
                    name: button.dataset.name,
                    days: button.dataset.days,
                    discount: button.dataset.discount,
                    description: button.dataset.description,
                };
                switchToUpdate(payload);
            });
        });

        resetButtons.forEach((btn) => btn.addEventListener('click', switchToCreate));

        // Sidebar toggle
        const logoToggle = document.getElementById('logo-toggle');
        const aside = document.querySelector('aside');
        const body = document.body;
        if (logoToggle && aside) {
            logoToggle.addEventListener('click', () => {
                aside.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        }
    });
</script>
@endpush
