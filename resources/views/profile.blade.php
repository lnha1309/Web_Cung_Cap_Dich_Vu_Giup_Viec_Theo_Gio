@extends('layouts.base')

@section('title', 'Thong tin ca nhan')

@section('content')
<section style="max-width: 800px; margin: 40px auto; padding: 24px; border-radius: 16px; border: 1px solid #e0e0e0; box-shadow: 0 8px 24px rgba(0,0,0,0.04); background-color: #ffffff;">
    <h1 style="font-size: 24px; margin-bottom: 20px; color: #1a1a1a;">Thong tin ca nhan</h1>

    @if (session('status'))
        <div style="padding: 10px 12px; border-radius: 8px; background-color: #e8f5e9; color: #2e7d32; font-size: 14px; margin-bottom: 16px;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="padding: 10px 12px; border-radius: 8px; background-color: #ffebee; color: #c62828; font-size: 14px; margin-bottom: 16px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Ho va ten</label>
                <input
                    type="text"
                    name="Ten_KH"
                    value="{{ old('Ten_KH', $customer->Ten_KH) }}"
                    data-editable="true"
                    readonly
                    style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #ddd; font-size:14px;"
                >
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:6px; font-weight:500;">So dien thoai</label>
                <input
                    type="text"
                    name="SDT"
                    value="{{ old('SDT', $customer->SDT) }}"
                    data-editable="true"
                    readonly
                    style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #ddd; font-size:14px;"
                >
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Email</label>
                <input
                    type="email"
                    value="{{ $customer->Email }}"
                    readonly
                    style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #eee; background-color:#fafafa; font-size:14px;"
                >
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Ten dang nhap</label>
                <input
                    type="text"
                    value="{{ $account->TenDN }}"
                    readonly
                    style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #eee; background-color:#fafafa; font-size:14px;"
                >
            </div>
        </div>

        <div style="margin-top: 24px;">
            <h2 style="font-size: 18px; margin-bottom: 12px; color:#1a1a1a;">Dia chi</h2>
            <p style="font-size: 13px; color:#666; margin-bottom:10px;">Ban co the luu toi da 3 dia chi.</p>

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
                @endphp
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom:6px; font-weight:500;">Dia chi {{ $i + 1 }}</label>
                    <input type="hidden" name="addresses[{{ $i }}][id]" value="{{ $id }}">
                    <input type="hidden" name="addresses[{{ $i }}][district]" value="{{ $district }}">
                    <input
                        type="text"
                        name="addresses[{{ $i }}][DiaChiDayDu]"
                        value="{{ $value }}"
                        class="address-input"
                        data-index="{{ $i }}"
                        data-editable="true"
                        readonly
                        placeholder="Nhap dia chi va chon tu goi y Google Maps"
                        style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #ddd; font-size:14px; margin-bottom:6px;"
                    >
                    <input
                        type="text"
                        name="addresses[{{ $i }}][CanHo]"
                        value="{{ $apartment }}"
                        data-editable="true"
                        readonly
                        placeholder="Can ho (vi du: Can ho A2-12, Chung cu XYZ)"
                        style="width:100%; padding:8px 12px; border-radius:10px; border:2px solid #ddd; font-size:13px;"
                    >
                    <button
                        type="button"
                        class="delete-address"
                        data-index="{{ $i }}"
                        style="margin-top:8px; padding:6px 12px; border-radius:999px; border:1px solid #c62828; background:#ffebee; color:#c62828; font-size:12px; cursor:pointer; display:none;"
                    >
                        Xoa dia chi nay
                    </button>
                </div>
            @endfor
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
            <button
                type="button"
                id="editProfileButton"
                style="padding:10px 18px; border-radius:999px; border:2px solid #004d2e; background:#ffffff; color:#004d2e; font-weight:500; cursor:pointer;"
            >
                Sua thong tin
            </button>
            <button
                type="submit"
                id="saveProfileButton"
                style="padding:10px 18px; border-radius:999px; border:none; background:#004d2e; color:#ffffff; font-weight:500; cursor:pointer; display:none;"
            >
                Luu thay doi
            </button>
        </div>
    </form>
</section>

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

