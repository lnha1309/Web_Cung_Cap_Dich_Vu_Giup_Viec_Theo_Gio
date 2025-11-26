<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = NhanVien::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_NV', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }
}
