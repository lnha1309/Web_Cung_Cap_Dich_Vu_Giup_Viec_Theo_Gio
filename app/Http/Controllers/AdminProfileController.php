<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\TaiKhoan;
use App\Models\NhanVien;
use App\Models\LoaiTaiKhoan;

class AdminProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        
        // Load danh sách tài khoản nhân viên (staff) với tìm kiếm
        $query = TaiKhoan::where('ID_LoaiTK', 'staff');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('TenDN', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('nhanVien', function($nv) use ($search) {
                      $nv->where('Ten_NV', 'like', "%{$search}%")
                        ->orWhere('SDT', 'like', "%{$search}%")
                        ->orWhere('Email', 'like', "%{$search}%");
                  });
            });
        }
        
        $staffAccounts = $query->with('nhanVien')->paginate(10);
        
        // Load danh sách loại tài khoản
        $accountTypes = LoaiTaiKhoan::all();
        
        return view('admin.profile', compact('user', 'staffAccounts', 'accountTypes'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user->MatKhau = $request->new_password; // Mutator will hash it
        $user->save();

        return redirect()->route('admin.profile.show')->with('success', 'Đổi mật khẩu thành công.');
    }

    /**
     * Cập nhật role cho tài khoản nhân viên
     */
    public function updateEmployeeRole(Request $request, $accountId)
    {
        $request->validate([
            'role' => ['required', 'string'],
        ]);

        $account = TaiKhoan::findOrFail($accountId);
        
        $oldRole = LoaiTaiKhoan::find($account->ID_LoaiTK);
        $newRole = LoaiTaiKhoan::find($request->role);
        
        if (!$newRole) {
            return redirect()->route('admin.profile.show')->with('error', 'Loại tài khoản không tồn tại.');
        }
        
        $account->ID_LoaiTK = $request->role;
        $account->save();

        $employeeName = $account->nhanVien ? $account->nhanVien->Ten_NV : $account->TenDN;

        return redirect()->route('admin.profile.show')->with('success', "Đã thay đổi role từ '{$oldRole->TenLoai}' sang '{$newRole->TenLoai}' cho {$employeeName}.");
    }

    /**
     * Thêm loại tài khoản mới
     */
    public function storeAccountType(Request $request)
    {
        $request->validate([
            'id_loai_tk' => ['required', 'string', 'max:20', 'unique:LoaiTaiKhoan,ID_LoaiTK'],
            'ten_loai' => ['required', 'string', 'max:50'],
        ], [
            'id_loai_tk.required' => 'Vui lòng nhập mã loại tài khoản.',
            'id_loai_tk.unique' => 'Mã loại tài khoản đã tồn tại.',
            'ten_loai.required' => 'Vui lòng nhập tên loại tài khoản.',
        ]);

        LoaiTaiKhoan::create([
            'ID_LoaiTK' => $request->id_loai_tk,
            'TenLoai' => $request->ten_loai,
        ]);

        return redirect()->route('admin.profile.show')->with('success', "Đã thêm loại tài khoản '{$request->ten_loai}'.");
    }

    /**
     * Cập nhật loại tài khoản
     */
    public function updateAccountType(Request $request, $id)
    {
        $request->validate([
            'ten_loai' => ['required', 'string', 'max:50'],
        ], [
            'ten_loai.required' => 'Vui lòng nhập tên loại tài khoản.',
        ]);

        $accountType = LoaiTaiKhoan::findOrFail($id);
        $accountType->TenLoai = $request->ten_loai;
        $accountType->save();

        return redirect()->route('admin.profile.show')->with('success', "Đã cập nhật loại tài khoản '{$request->ten_loai}'.");
    }

    /**
     * Xoá loại tài khoản
     */
    public function destroyAccountType($id)
    {
        $accountType = LoaiTaiKhoan::findOrFail($id);
        
        // Kiểm tra có tài khoản đang sử dụng loại này không
        $count = TaiKhoan::where('ID_LoaiTK', $id)->count();
        if ($count > 0) {
            return redirect()->route('admin.profile.show')->with('error', "Loại tài khoản '{$accountType->TenLoai}' hiện đang được sử dụng cho {$count} tài khoản. Vui lòng đổi loại tài khoản cho các tài khoản này trước khi xoá.");
        }

        $tenLoai = $accountType->TenLoai;
        $accountType->delete();

        return redirect()->route('admin.profile.show')->with('success', "Đã xoá loại tài khoản '{$tenLoai}'.");
    }
}
