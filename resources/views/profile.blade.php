@extends('layouts.base')

@section('title', 'Thông tin cá nhân')

@section('content')
<section class="profile-container">
    <h1 class="profile-title">Thông tin cá nhân</h1>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
        @csrf

        <div class="profile-grid">
            <div class="form-group">
                <label>Họ và tên</label>
                <input
                    type="text"
                    name="Ten_KH"
                    value="{{ old('Ten_KH', $customer->Ten_KH) }}"
                    data-editable="true"
                    readonly
                    class="form-control"
                >
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input
                    type="text"
                    name="SDT"
                    value="{{ old('SDT', $customer->SDT) }}"
                    data-editable="true"
                    readonly
                    class="form-control"
                >
            </div>

            <div class="form-group">
                <label>Email</label>
                <input
                    type="email"
                    value="{{ $customer->Email }}"
                    readonly
                    class="form-control form-control-readonly"
                >
            </div>

            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input
                    type="text"
                    value="{{ $account->TenDN }}"
                    readonly
                    class="form-control form-control-readonly"
                >
            </div>
        </div>

        <div class="address-section">
            <h2 class="section-title">Địa chỉ</h2>
            <p class="section-subtitle">Bạn có thể lưu tối đa 3 địa chỉ.</p>

            @php
                $oldAddresses = old('addresses', []);
            @endphp

            @for ($i = 0; $i < 3; $i++)
                @php
                    $existing = $addresses[$i] ?? null;
                    $oldItem = $oldAddresses[$i] ?? null;
                    $value = $oldItem['DiaChiDayDu'] ?? ($existing->DiaChiDayDu ?? '');
                    $id = $oldItem['id'] ?? ($existing->ID_DC ?? null);
                    $apartment = $oldItem['CanHo'] ?? ($existing->CanHo ?? '');
                    $district = $oldItem['district'] ?? '';
                    $label = $oldItem['Nhan'] ?? ($existing->Nhan ?? '');
                @endphp
                <div class="form-group address-group">
                    <label>Địa chỉ {{ $i + 1 }}</label>
                    <input type="hidden" name="addresses[{{ $i }}][id]" value="{{ $id }}">
                    <input type="hidden" name="addresses[{{ $i }}][district]" value="{{ $district }}">
                    <input
                        type="text"
                        name="addresses[{{ $i }}][DiaChiDayDu]"
                        value="{{ $value }}"
                        class="address-input form-control mb-2"
                        data-index="{{ $i }}"
                        data-editable="true"
                        readonly
                        placeholder="Nhap dia chi va chon tu goi y Google Maps"
                    >
                    <input
                        type="text"
                        name="addresses[{{ $i }}][CanHo]"
                        value="{{ $apartment }}"
                        data-editable="true"
                        readonly
                        class="form-control form-control-sm"
                        placeholder="Can ho (vi du: Can ho A2-12, Chung cu XYZ)"
                    >
                    <input
                        type="text"
                        name="addresses[{{ $i }}][Nhan]"
                        value="{{ $label }}"
                        data-editable="true"
                        readonly
                        class="form-control form-control-sm"
                        placeholder="Nhãn (ví dụ: Nhà, Văn phòng)"
                        style="margin-top: 6px;"
                    >
                    <button
                        type="button"
                        class="delete-address"
                        data-index="{{ $i }}"
                    >
                        Xoá địa chỉ này
                    </button>
                </div>
            @endfor
        </div>

        <div class="form-actions">
            <button
                type="button"
                id="editProfileButton"
                class="btn btn-outline"
            >
                Sửa thông tin
            </button>
            <button
                type="submit"
                id="saveProfileButton"
                class="btn btn-primary"
                style="display:none;"
            >
                Lưu thay đổi
            </button>
        </div>
    </form>
</section>

@push('styles')
<style>
    .profile-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.04);
        background-color: #ffffff;
    }

    .profile-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #1a1a1a;
    }

    .alert {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .alert-error {
        background-color: #ffebee;
        color: #c62828;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 18px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 2px solid #ddd;
        font-size: 14px;
    }

    .form-control-readonly {
        border: 2px solid #eee;
        background-color: #fafafa;
    }

    .form-control-sm {
        padding: 8px 12px;
        font-size: 13px;
    }

    .mb-2 {
        margin-bottom: 6px;
    }

    .address-section {
        margin-top: 24px;
    }

    .section-title {
        font-size: 18px;
        margin-bottom: 12px;
        color: #1a1a1a;
    }

    .section-subtitle {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }

    .delete-address {
        margin-top: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid #c62828;
        background: #ffebee;
        color: #c62828;
        font-size: 12px;
        cursor: pointer;
        display: none;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
    }

    .btn {
        padding: 10px 18px;
        border-radius: 999px;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-outline {
        border: 2px solid #004d2e;
        background: #ffffff;
        color: #004d2e;
    }

    .btn-primary {
        border: none;
        background: #004d2e;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .profile-container {
            margin: 20px;
            padding: 20px;
        }

        .profile-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editButton = document.getElementById('editProfileButton');
        var saveButton = document.getElementById('saveProfileButton');
        var editableFields = document.querySelectorAll('[data-editable="true"]');
        var deleteButtons = document.querySelectorAll('.delete-address');

        if (editButton) {
            editButton.addEventListener('click', function () {
                editableFields.forEach(function (field) {
                    field.readOnly = false;
                });
                editButton.style.display = 'none';
                if (saveButton) {
                    saveButton.style.display = 'inline-block';
                }
                deleteButtons.forEach(function (btn) {
                    btn.style.display = 'inline-block';
                });
            });
        }

        deleteButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Ban co chac muon xoa dia chi nay khong?')) {
                    return;
                }

                var group = btn.closest('.form-group');
                if (!group) {
                    return;
                }

                var addressInput = group.querySelector('input[name$="[DiaChiDayDu]"]');
                var apartmentInput = group.querySelector('input[name$="[CanHo]"]');
                var districtInput = group.querySelector('input[name$="[district]"]');

                if (addressInput) {
                    addressInput.value = '';
                }
                if (apartmentInput) {
                    apartmentInput.value = '';
                }
                if (districtInput) {
                    districtInput.value = '';
                }
            });
        });

        // Google Places autocomplete cho cac o dia chi
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            document.querySelectorAll('.address-input').forEach(function (input) {
                var index = input.getAttribute('data-index');
                var autocomplete = new google.maps.places.Autocomplete(input, {
                    componentRestrictions: { country: 'vn' },
                    fields: ['formatted_address', 'address_components'],
                });

                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (!place || !place.address_components) {
                        return;
                    }

                    if (place.formatted_address) {
                        input.value = place.formatted_address;
                    }

                    var districtName = '';
                    place.address_components.forEach(function (component) {
                        if (
                            component.types.indexOf('administrative_area_level_2') !== -1 ||
                            component.types.indexOf('sublocality_level_1') !== -1
                        ) {
                            districtName = component.long_name;
                        }
                    });

                    var districtField = document.querySelector(
                        'input[name="addresses[' + index + '][district]"]'
                    );
                    if (districtField) {
                        districtField.value = districtName;
                    }
                });
            });
        }
    });
</script>
@endsection
