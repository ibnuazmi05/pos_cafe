<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\EmployeeFilterRequest;
// use App\Http\Requests\Superadmin\ResetEmployeePasswordRequest;
use App\Http\Requests\Superadmin\StoreEmployeeRequest;
use App\Http\Requests\Superadmin\UpdateEmployeeRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(EmployeeFilterRequest $request): View
    {
        $q = $request->validated('q');
        $role = $request->validated('role');
        $status = $request->validated('status');

        $employees = User::query()
            ->when($q, function ($query, $q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($role, fn($query, $role) => $query->where('role', $role))
            ->when($status, fn($query, $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('superadmin.employees.index', [
            'employees' => $employees,
            'filters' => $request->validated(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('superadmin.employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $employee = User::create($data);

        ActivityLogger::log('employee.created', $employee, ['role' => $employee->role->value]);

        return redirect()->route('superadmin.employees.index')
            ->with('success', "Pegawai {$employee->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('superadmin.employees.edit', ['employee' => $user]);
    }

    public function update(UpdateEmployeeRequest $request, User $user): RedirectResponse
    {
        $oldRole = $user->role->value;

        $user->fill($request->validated())->save();

        ActivityLogger::log('employee.updated', $user, [
            'changed' => array_keys($user->getChanges()),
        ]);

        if ($user->role->value !== $oldRole) {
            ActivityLogger::log('employee.role_changed', $user, [
                'from' => $oldRole,
                'to' => $user->role->value,
            ]);
        }

        return redirect()->route('superadmin.employees.index')
            ->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        Gate::authorize('toggleActive', $user);

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            $this->revokeSessions($user);
        }

        ActivityLogger::log($user->is_active ? 'employee.activated' : 'employee.deactivated', $user);

        return back()->with('success', $user->is_active
            ? "{$user->name} diaktifkan kembali."
            : "{$user->name} dinonaktifkan. Riwayat transaksinya tetap tersimpan.");
    }

    // Untuk menambahkakn fitur reset password pegawai, kita bisa menambahkan metode ini kembali.
    // public function resetPassword(ResetEmployeePasswordRequest $request, User $user): RedirectResponse
    // {
    //     $user->forceFill(['password' => $request->validated('password')]);
    //     $user->setRememberToken(Str::random(60));
    //     $user->save();

    //     $this->revokeSessions($user);

    //     ActivityLogger::log('employee.password_reset', $user);

    //     return back()->with('success', "Password {$user->name} berhasil direset.");
    // }

    /** Paksa logout semua sesi pegawai (hanya berlaku jika SESSION_DRIVER=database). */
    private function revokeSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}
