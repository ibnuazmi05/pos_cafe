<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShiftStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ForceCloseShiftRequest;
use App\Models\Shift;
use App\Services\ShiftService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\User;
use App\Enums\Role;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $shifts) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Shift::class);

        $f = $request->validate([
            'status' => ['nullable', Rule::in([...ShiftStatus::values(), 'all'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cashier_id' => ['nullable', 'integer'],
        ]);

        $selected = $f['status'] ?? 'all';

        $shifts = Shift::query()
            ->with('user')
            ->when($selected !== 'all', fn($q) => $q->where('status', $selected))
            ->when($f['date_from'] ?? null, fn($q, $v) => $q->whereDate('opened_at', '>=', $v))
            ->when($f['date_to'] ?? null, fn($q, $v) => $q->whereDate('opened_at', '<=', $v))
            ->when($f['cashier_id'] ?? null, fn($q, $v) => $q->where('user_id', $v))
            ->latest('opened_at')
            ->paginate(20)
            ->withQueryString();

        $expected = $shifts->getCollection()
            ->filter(fn(Shift $s) => $s->isOpen())
            ->mapWithKeys(fn(Shift $s) => [$s->id => $this->shifts->expectedCash($s)]);

        return view('admin.shifts.index', [
            'filters' => $f,
            'shifts' => $shifts,
            'expected' => $expected,
            'selected' => $selected,
            'cashiers' => User::where('role', Role::Kasir->value)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function forceClose(ForceCloseShiftRequest $request, Shift $shift): RedirectResponse
    {
        try {
            $this->shifts->close(
                $shift,
                $request->user(),
                (float) $request->validated('closing_cash'),
                $request->validated('note'),
                force: true,
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Shift ditutup paksa dan tercatat di log aktivitas.');
    }
}
