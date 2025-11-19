<?php

namespace App\Http\Controllers;

use App\Models\DiaChi;
use App\Models\DonDat;
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

        $addresses = $customer->diaChis()->orderBy('ID_DC')->take(3)->get();

        return view('profile', [
            'account' => $account,
            'customer' => $customer,
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
                'Ten_KH' => ['required', 'string', 'max:255'],
                'SDT' => ['required', 'string', 'max:15', 'unique:KhachHang,SDT,' . $customer->ID_KH . ',ID_KH'],
                'addresses' => ['array'],
                'addresses.*.id' => ['nullable', 'string'],
                'addresses.*.DiaChiDayDu' => ['nullable', 'string', 'max:1000'],
                'addresses.*.district' => ['nullable', 'string', 'max:255'],
                'addresses.*.CanHo' => ['nullable', 'string', 'max:255'],
            ],
            [],
            [
                'Ten_KH' => 'Họ và tên',
                'SDT' => 'Số điện thoại',
            ]
        );

        $customer->Ten_KH = $validated['Ten_KH'];
        $customer->SDT = $validated['SDT'];
        $customer->save();

        $addressesData = $validated['addresses'] ?? [];

        // Cập nhật / tạo / xóa tối đa 3 địa chỉ
        $existing = $customer->diaChis()->orderBy('ID_DC')->take(3)->get()->keyBy('ID_DC');
        $usedIds = [];

        foreach ($addressesData as $item) {
            $text = trim($item['DiaChiDayDu'] ?? '');
            $id = $item['id'] ?? null;
            $districtName = trim($item['district'] ?? '');
            $apartment = trim($item['CanHo'] ?? '');

            $idQuan = null;
            if ($districtName !== '') {
                $quan = Quan::where('TenQuan', 'like', '%' . $districtName . '%')->first();
                if (!$quan) {
                    $plain = preg_replace('/^(Quận|Huyện|TP Thủ Đức)/iu', '', $districtName);
                    $plain = trim($plain);
                    if ($plain !== '') {
                        $quan = Quan::where('TenQuan', 'like', '%' . $plain . '%')->first();
                    }
                }
                if ($quan) {
                    $idQuan = $quan->ID_Quan;
                }

                // Nếu chọn 1 địa chỉ ngoài danh sách quận/huyện của TP.HCM thì không cho lưu
                if ($text !== '' && $idQuan === null) {
                    return back()
                        ->withErrors(['addresses' => 'Dịch vụ hiện tại chỉ phục vụ trong khu vực TP.HCM.'])
                        ->withInput();
                }
            }

            if ($id && isset($existing[$id])) {
                if ($text === '') {
                    // Nếu để trống, chỉ cho phép xóa địa chỉ nếu chưa được dùng trong đơn đặt lịch nào
                    if (DonDat::where('ID_DC', $id)->exists()) {
                        return back()
                            ->withErrors(['addresses' => 'Địa chỉ này đã được sử dụng trong đơn đặt nên không thể xóa.'])
                            ->withInput();
                    }

                    $existing[$id]->delete();
                } else {
                    $existing[$id]->DiaChiDayDu = $text;
                    $existing[$id]->CanHo = $apartment !== '' ? $apartment : null;
                    if ($idQuan !== null) {
                        // Chỉ cập nhật quận/huyện nếu người dùng đã chọn lại trên bản đồ
                        $existing[$id]->ID_Quan = $idQuan;
                    }
                    $existing[$id]->save();
                    $usedIds[] = $id;
                }
            } elseif ($text !== '' && count($usedIds) + $existing->count() < 3) {
                $newId = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');
                DiaChi::create([
                    'ID_DC' => $newId,
                    'ID_KH' => $customer->ID_KH,
                    'ID_Quan' => $idQuan,
                    'CanHo' => $apartment !== '' ? $apartment : null,
                    'DiaChiDayDu' => $text,
                ]);
            }
        }

        return redirect()->route('profile.show')->with('status', 'Cập nhật thông tin cá nhân thành công.');
    }
}

