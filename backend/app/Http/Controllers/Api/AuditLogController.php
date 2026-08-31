<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with(['user', 'patient.user']);

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($recordType = $request->input('record_type')) {
            $query->where('record_type', $recordType);
        }

        if ($patientId = $request->input('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => AuditLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with(['user', 'patient.user'])->findOrFail($id);

        return response()->json([
            'audit_log' => new AuditLogResource($log),
        ]);
    }
}
