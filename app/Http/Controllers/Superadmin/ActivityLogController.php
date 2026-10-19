<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\ActivityLogFilterRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(ActivityLogFilterRequest $request): View
    {
        $filters = $request->validated();

        $logs = ActivityLog::query()
            ->with('user')
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('superadmin.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'users' => User::whereIn('role', [Role::Admin->value, Role::Kasir->value])->orderBy('name')->get(['id', 'name']),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
