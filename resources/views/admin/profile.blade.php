@extends('layouts.admin')

@section('title', 'Tài khoản Admin')

@section('content')
@push('styles')
<style>
    .card {
        background: var(--color-white);
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        max-width: 800px;
        margin: 0 auto;
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
        background: #5a66d1; /* Slightly darker primary */
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
</style>
@endpush

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'profile'])

    <main>
        <div class="card">
            <div class="card-header">
                <h2>Thông tin tài khoản</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
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

                <div class="form-group">
                    <label for="name" class="form-label">Họ tên</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <h3 class="section-title">Đổi mật khẩu</h3>
                <p style="margin-bottom: 1rem; color: var(--color-info-dark); font-size: 0.9rem;">Để trống nếu không muốn thay đổi mật khẩu.</p>

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
    </main>
</div>
@endsection
