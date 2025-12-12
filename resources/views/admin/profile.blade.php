@extends('layouts.admin')

@section('title', 'Tài khoản Admin')

@section('content')
@push('styles')
<style>
    /* Override grid for 2 columns (no aside) */
    .container {
        grid-template-columns: 14rem auto;
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

    .card {
        background: var(--color-white);
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        margin-bottom: 2rem;
    }

    .card:hover {
        box-shadow: none;
    }

    .card-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--color-light);
        padding-bottom: 1rem;
    }

    .card-header h2 {
        font-size: 1.4rem;
        color: var(--color-dark);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--color-dark);
    }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border-radius: var(--border-radius-1);
        background: var(--color-white);
        border: 1px solid var(--color-info-dark);
        color: var(--color-dark);
        font-family: poppins, sans-serif;
    }

    .form-control:focus {
        border-color: var(--color-primary);
        outline: none;
    }

    .btn-primary {
        background: var(--color-primary);
        color: var(--color-white);
        padding: 0.8rem 2rem;
        border-radius: var(--border-radius-1);
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 300ms ease;
    }

    .btn-primary:hover {
        background: #5a66d1;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--border-radius-1);
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: var(--color-success);
        color: var(--color-white);
    }

    .alert-danger {
        background: var(--color-danger);
        color: var(--color-white);
    }

    .section-title {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: var(--color-dark-variant);
        border-bottom: 1px dashed var(--color-light);
        padding-bottom: 0.5rem;
    }

    /* Staff Account Table */
    .staff-table {
        width: 100%;
        border-collapse: collapse;
    }

    .staff-table th, .staff-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--color-light);
    }

    .staff-table th {
        font-weight: 600;
        color: var(--color-dark-variant);
        background: var(--color-background);
    }

    .staff-table tr:hover {
        background: var(--color-light);
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        border-radius: var(--border-radius-1);
        border: none;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-role {
        background: #E0F2FE;
        color: #0369A1;
    }

    .btn-password {
        background: #FEF3C7;
        color: #B45309;
    }

    .search-box {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .search-box input {
        flex: 1;
        padding: 0.6rem 1rem;
        border: 1px solid var(--color-info-dark);
        border-radius: var(--border-radius-1);
    }

    .search-box button {
        padding: 0.6rem 1rem;
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--border-radius-1);
        cursor: pointer;
    }

    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .role-staff { background: #DCFCE7; color: #15803D; }
    .role-admin { background: #E0F2FE; color: #0369A1; }
    .role-customer { background: #FEF3C7; color: #B45309; }

    /* Modal styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: var(--color-white);
        border-radius: var(--card-border-radius);
        max-width: 400px;
        width: 90%;
        padding: 1.5rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--color-light);
        padding-bottom: 0.5rem;
    }

    .modal-header h3 {
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        color: var(--color-dark-variant);
    }
</style>
@endpush

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'profile'])

    <main>
        <!-- Admin Profile Card -->
        <div class="card">
            <div class="card-header">
                <h2>Thông tin tài khoản</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="list-style: none;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" value="{{ $user->TenDN }}" disabled style="background: var(--color-light); cursor: not-allowed;">
                    <small style="color: var(--color-info-dark);">Tên đăng nhập không thể thay đổi.</small>
                </div>

                <h3 class="section-title">Đổi mật khẩu</h3>

                <div class="form-group">
                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control">
                </div>

                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>

        <!-- Staff Account Management Card -->
        <div class="card">
            <div class="card-header">
                <h2>Quản lý tài khoản nhân viên</h2>
            </div>

            <form action="{{ route('admin.profile.show') }}" method="GET" class="search-box">
                <input type="text" name="search" placeholder="Tìm theo tên, SĐT, email..." value="{{ request('search') }}">
                <button type="submit">Tìm kiếm</button>
            </form>

            @if($staffAccounts->count() > 0)
            <div style="overflow-x: auto;">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Trạng thái</th>
                            <th>Role</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffAccounts as $account)
                        <tr>
                            <td>{{ $account->TenDN }}</td>
                            <td>{{ $account->nhanVien?->Ten_NV ?? '-' }}</td>
                            <td>{{ $account->nhanVien?->Email ?? $account->email ?? '-' }}</td>
                            <td>{{ $account->nhanVien?->SDT ?? '-' }}</td>
                            <td>
                                @php
                                    $statusClass = match($account->TrangThaiTK) {
                                        'active' => 'role-staff',
                                        'banned' => 'role-customer',
                                        default => 'role-admin'
                                    };
                                    $statusText = match($account->TrangThaiTK) {
                                        'active' => 'Hoạt động',
                                        'banned' => 'Đã khóa',
                                        default => 'Chưa kích hoạt'
                                    };
                                @endphp
                                <span class="role-badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                            <td>
                                @php
                                    $roleClass = match($account->ID_LoaiTK) {
                                        'staff' => 'role-staff',
                                        'admin' => 'role-admin',
                                        default => 'role-customer'
                                    };
                                    $roleText = match($account->ID_LoaiTK) {
                                        'staff' => 'Nhân viên',
                                        'admin' => 'Admin',
                                        default => 'Khách hàng'
                                    };
                                @endphp
                                <span class="role-badge {{ $roleClass }}">{{ $roleText }}</span>
                            </td>
                            <td>
                                <button type="button" class="btn-sm btn-role" onclick="openRoleModal('{{ $account->ID_TK }}', '{{ $account->nhanVien?->Ten_NV ?? $account->TenDN }}', '{{ $account->ID_LoaiTK }}')">
                                    Đổi role
                                </button>
                            </td>
                        </tr>
                        @endforeach
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $staffAccounts->withQueryString()->links() }}
            </div>
            @else
            <p style="text-align: center; color: var(--color-info-dark); padding: 2rem;">Không có tài khoản nhân viên nào.</p>
            @endif
        </div>

        <!-- Account Types Management Card -->
        <div class="card">
            <div class="card-header">
                <h2>Quản lý loại tài khoản</h2>
            </div>

            <!-- Add New Account Type Form -->
            <form action="{{ route('admin.profile.accountTypes.store') }}" method="POST" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--color-background); border-radius: var(--border-radius-1);">
                @csrf
                <h4 style="margin-bottom: 1rem; color: var(--color-dark-variant);">Thêm loại tài khoản mới</h4>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <div style="flex: 1; min-width: 150px;">
                        <label class="form-label">Mã loại TK</label>
                        <input type="text" name="id_loai_tk" class="form-control" placeholder="VD: manager" required maxlength="20">
                    </div>
                    <div style="flex: 2; min-width: 200px;">
                        <label class="form-label">Tên loại</label>
                        <input type="text" name="ten_loai" class="form-control" placeholder="VD: Quản lý" required maxlength="50">
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 0.8rem 1.5rem;">Thêm</button>
                </div>
            </form>

            <!-- Account Types Table -->
            <div style="overflow-x: auto;">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Mã loại TK</th>
                            <th>Tên loại</th>
                            <th>Số TK sử dụng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accountTypes as $type)
                        <tr>
                            <td><code>{{ $type->ID_LoaiTK }}</code></td>
                            <td>{{ $type->TenLoai }}</td>
                            <td>{{ \App\Models\TaiKhoan::where('ID_LoaiTK', $type->ID_LoaiTK)->count() }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="button" class="btn-sm btn-role" onclick="openEditTypeModal('{{ $type->ID_LoaiTK }}', '{{ $type->TenLoai }}')">
                                        Sửa
                                    </button>
                                    <form action="{{ route('admin.profile.accountTypes.destroy', $type->ID_LoaiTK) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xoá loại tài khoản này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm" style="background: #FEE2E2; color: #DC2626;">Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Role Modal -->
<div class="modal-overlay" id="roleModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Đổi loại tài khoản</h3>
            <button class="modal-close" onclick="closeRoleModal()">&times;</button>
        </div>
        <p style="margin-bottom: 1rem;">Tài khoản: <strong id="roleAccountName"></strong></p>
        <form id="roleForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label class="form-label">Loại tài khoản:</label>
                <select id="roleSelect" name="role" class="form-control">
                    @foreach($accountTypes as $type)
                    <option value="{{ $type->ID_LoaiTK }}">{{ $type->TenLoai }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" onclick="closeRoleModal()" style="padding: 0.6rem 1rem; background: var(--color-light); border: none; border-radius: var(--border-radius-1); cursor: pointer;">Hủy</button>
                <button type="submit" class="btn-primary" style="padding: 0.6rem 1rem;">Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Account Type Modal -->
<div class="modal-overlay" id="editTypeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sửa loại tài khoản</h3>
            <button class="modal-close" onclick="closeEditTypeModal()">&times;</button>
        </div>
        <p style="margin-bottom: 1rem;">Mã: <strong id="editTypeId"></strong></p>
        <form id="editTypeForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label class="form-label">Tên loại:</label>
                <input type="text" id="editTypeName" name="ten_loai" class="form-control" required maxlength="50">
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" onclick="closeEditTypeModal()" style="padding: 0.6rem 1rem; background: var(--color-light); border: none; border-radius: var(--border-radius-1); cursor: pointer;">Hủy</button>
                <button type="submit" class="btn-primary" style="padding: 0.6rem 1rem;">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRoleModal(accountId, accountName, currentRole) {
        document.getElementById('roleAccountName').textContent = accountName;
        document.getElementById('roleSelect').value = currentRole;
        document.getElementById('roleForm').action = `/admin/profile/employee/${accountId}/role`;
        document.getElementById('roleModal').classList.add('active');
    }

    function closeRoleModal() {
        document.getElementById('roleModal').classList.remove('active');
    }

    function openEditTypeModal(typeId, typeName) {
        document.getElementById('editTypeId').textContent = typeId;
        document.getElementById('editTypeName').value = typeName;
        document.getElementById('editTypeForm').action = `/admin/profile/account-types/${typeId}`;
        document.getElementById('editTypeModal').classList.add('active');
    }

    function closeEditTypeModal() {
        document.getElementById('editTypeModal').classList.remove('active');
    }

    // Close modal on overlay click
    document.getElementById('roleModal').addEventListener('click', function(e) {
        if (e.target === this) closeRoleModal();
    });
    document.getElementById('editTypeModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditTypeModal();
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRoleModal();
            closeEditTypeModal();
        }
    });
</script>
@endsection
