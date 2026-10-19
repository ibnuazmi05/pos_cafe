<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $supplier = Supplier::query()
            ->when($f['q'] ?? null, fn($q, $v) => $q->where(fn($w) => $w->where('name', 'like', Format::like($v))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.suppliers.index', [
            'suppliers' => Supplier::withCount('purchaseOrders')->orderBy('name')->paginate(20),
            'filters' => $f,
        ]);
    }

    public function create(): View
    {
        return view('admin.suppliers.form');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());
        ActivityLogger::log('supplier.created', $supplier, ['name' => $supplier->name]);

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.form', ['supplier' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());
        ActivityLogger::log('supplier.updated', $supplier, ['name' => $supplier->name]);

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier sudah memiliki riwayat pemesanan, tidak dapat dihapus.');
        }

        $supplier->delete();
        ActivityLogger::log('supplier.deleted', $supplier, ['name' => $supplier->name]);

        return back()->with('success', 'Supplier dihapus.');
    }
}
