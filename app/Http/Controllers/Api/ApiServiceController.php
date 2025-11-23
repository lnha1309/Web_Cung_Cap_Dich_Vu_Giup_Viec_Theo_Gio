<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DichVu;
use Illuminate\Http\Request;

class ApiServiceController extends Controller
{
    /**
     * Get all services
     * GET /api/services
     */
    public function index()
    {
        $services = DichVu::all();

        return response()->json([
            'success' => true,
            'data' => $services->map(function ($service) {
                return [
                    'id' => $service->ID_DV,
                    'name' => $service->TenDV,
                    'description' => $service->MoTa,
                    'price' => (float) $service->GiaDV,
                    'max_area' => (int) $service->DienTichToiDa,
                    'rooms' => (int) $service->SoPhong,
                    'duration_hours' => (float) $service->ThoiLuong,
                ];
            })
        ]);
    }

    /**
     * Get service by ID
     * GET /api/services/{id}
     */
    public function show($id)
    {
        $service = DichVu::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'Dịch vụ không tồn tại.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->ID_DV,
                'name' => $service->TenDV,
                'description' => $service->MoTa,
                'price' => (float) $service->GiaDV,
                'max_area' => (int) $service->DienTichToiDa,
                'rooms' => (int) $service->SoPhong,
                'duration_hours' => (float) $service->ThoiLuong,
            ]
        ]);
    }

    /**
     * Get service by duration (hours)
     * GET /api/services/by-duration/{hours}
     */
    public function getByDuration($hours)
    {
        $hours = (int) $hours;

        if (!in_array($hours, [2, 3, 4])) {
            return response()->json([
                'success' => false,
                'error' => 'Thời lượng phải là 2, 3 hoặc 4 giờ.'
            ], 422);
        }

        $idDv = match ($hours) {
            2 => 'DV001',
            3 => 'DV002',
            4 => 'DV003',
            default => null,
        };

        $service = DichVu::find($idDv);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy dịch vụ.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->ID_DV,
                'name' => $service->TenDV,
                'description' => $service->MoTa,
                'price' => (float) $service->GiaDV,
                'max_area' => (int) $service->DienTichToiDa,
                'rooms' => (int) $service->SoPhong,
                'duration_hours' => (float) $service->ThoiLuong,
            ]
        ]);
    }

    /**
     * Calculate quote based on duration
     * POST /api/services/quote
     */
    public function calculateQuote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'duration' => ['required', 'integer', 'in:2,3,4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $duration = (int) $request->duration;

        $idDv = match ($duration) {
            2 => 'DV001',
            3 => 'DV002',
            4 => 'DV003',
            default => null,
        };

        $service = DichVu::find($idDv);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy dịch vụ.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $service->ID_DV,
                'service_name' => $service->TenDV,
                'price' => (float) $service->GiaDV,
                'duration_hours' => (float) $service->ThoiLuong,
            ]
        ]);
    }
}
