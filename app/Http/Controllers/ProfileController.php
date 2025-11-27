<?php

namespace App\Http\Controllers;

use App\Models\DiaChi;
use App\Models\Quan;
use App\Support\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $account = Auth::user();
        $customer = $account->khachHang;

        if (!$customer) {
            abort(404);
        }

        $addresses = $customer->diaChis()
            ->where('is_Deleted', false)
            ->orderBy('ID_DC')
            ->take(3)
            ->get();

        return view('profile', [
            'account'   => $account,
            'customer'  => $customer,
            'addresses' => $addresses,
        ]);
    }

    public function update(Request $request)
    {
        $account = Auth::user();
        $customer = $account->khachHang;

        if (!$customer) {
            abort(404);
        }

        $validated = $request->validate(
            [
                'Ten_KH'                  => ['required', 'string', 'max:255'],
                'SDT'                     => ['required', 'string', 'max:15', 'unique:KhachHang,SDT,' . $customer->ID_KH . ',ID_KH'],
                'addresses'               => ['array'],
                'addresses.*.id'          => ['nullable', 'string'],
                'addresses.*.DiaChiDayDu' => ['nullable', 'string', 'max:1000'],
                'addresses.*.district'    => ['nullable', 'string', 'max:255'],
                'addresses.*.CanHo'       => ['nullable', 'string', 'max:255'],
                'addresses.*.Nhan'        => ['nullable', 'string', 'max:100'],
            ],
            [],
            [
                'Ten_KH' => 'Họ và tên',
                'SDT'    => 'Số điện thoại',
            ]
        );

        $customer->Ten_KH = $validated['Ten_KH'];
        $customer->SDT = $validated['SDT'];
        $customer->save();

        $addressesData = $validated['addresses'] ?? [];

        // Cập nhật / tạo tối đa 3 địa chỉ đang hoạt động (chưa bị xoá mềm)
        $existing = $customer->diaChis()
            ->where('is_Deleted', false)
            ->orderBy('ID_DC')
            ->take(3)
            ->get()
            ->keyBy('ID_DC');

        $usedIds = [];

        foreach ($addressesData as $item) {
            $text         = trim($item['DiaChiDayDu'] ?? '');
            $id           = $item['id'] ?? null;
            $districtName = trim($item['district'] ?? '');
            $apartment    = trim($item['CanHo'] ?? '');
            $label        = trim($item['Nhan'] ?? '');

            $idQuan = null;
            if ($districtName !== '') {
                $quan = Quan::where('TenQuan', 'like', '%' . $districtName . '%')->first();
                if ($quan) {
                    $idQuan = $quan->ID_Quan;
                }

                // Nếu chọn địa chỉ ngoài danh sách quận/huyện TP.HCM thì không cho lưu
                if ($text !== '' && $idQuan === null) {
                    return back()
                        ->withErrors(['addresses' => 'Dịch vụ hiện tại chỉ phục vụ trong khu vực TP.HCM.'])
                        ->withInput();
                }
            }

            if ($id && isset($existing[$id])) {
                if ($text === '') {
                    // Xoá mềm: đánh dấu is_Deleted = true, giữ lại bản ghi để không ảnh hưởng FK
                    $existing[$id]->is_Deleted = true;
                    $existing[$id]->save();
                } else {
                    $existing[$id]->DiaChiDayDu = $text;
                    $existing[$id]->CanHo       = $apartment !== '' ? $apartment : null;
                    $existing[$id]->Nhan        = $label !== '' ? $label : null;
                    if ($idQuan !== null) {
                        $existing[$id]->ID_Quan = $idQuan;
                    }
                    $existing[$id]->save();
                    $usedIds[] = $id;
                }
            } elseif ($text !== '' && count($usedIds) + $existing->count() < 3) {
                $newId = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');
                DiaChi::create([
                    'ID_DC'       => $newId,
                    'ID_KH'       => $customer->ID_KH,
                    'ID_Quan'     => $idQuan,
                    'CanHo'       => $apartment !== '' ? $apartment : null,
                    'Nhan'        => $label !== '' ? $label : null,
                    'DiaChiDayDu' => $text,
                    'is_Deleted'  => false,
                ]);
            }
        }

        return redirect()
            ->route('profile.show')
            ->with('status', 'Cập nhật thông tin cá nhân thành công.');
    }
}

