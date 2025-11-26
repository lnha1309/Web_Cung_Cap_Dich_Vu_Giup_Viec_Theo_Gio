<?php

namespace App\Http\Controllers;

use App\Models\KhachHang;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = KhachHang::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_KH', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }
}
