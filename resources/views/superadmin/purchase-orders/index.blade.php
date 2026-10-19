<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="GET" x-data
                @change="if ($event.target.tagName === 'SELECT' || $event.target.type === 'date') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs flex flex-wrap gap-4 items-end">
                <div class="min-w-40">
                    <x-input-label for="date_from" value="Dari Tanggal" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$filters['date_from'] ?? ''" />
                </div>
                <div class="min-w-40">
                    <x-input-label for="date_to" value="Sampai Tanggal" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$filters['date_to'] ?? ''" />
                </div>
                <div class="min-w-48">
                    <x-select name="supplier_id" label="Supplier" :options="$suppliers->pluck('name', 'id')" :selected="$filters['supplier_id'] ?? null"
                        blank="Semua Supplier" />
                </div>
                <div class="min-w-40">
                    <x-select name="status" label="Status" :options="$statuses" :selected="$filters['status'] ?? null" blank="Semua Status" />
                </div>
                <div>
                    <a href="{{ route('superadmin.purchase-orders.index') }}"
                        class="min-w-35 inline-flex items-center justify-center px-4 py-2.5 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition w-full">
                        Reset
                    </a>
                </div>
            </form>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100 font-semibold">
                        <tr>
                            <th class="px-5 py-3.5">No. PO</th>
                            <th class="px-5 py-3.5">Supplier</th>
                            <th class="px-5 py-3.5">Dibuat oleh</th>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5 text-right">Total</th>
                            <th class="px-5 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($orders as $o)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-5 py-3.5 font-medium">
                                    <a href="{{ route('superadmin.purchase-orders.show', $o) }}"
                                        class="text-amber-800 hover:text-amber-900 hover:underline font-semibold">{{ $o->po_no }}</a>
                                </td>
                                <td class="px-5 py-3.5">{{ $o->supplier->name }}</td>
                                <td class="px-5 py-3.5">{{ $o->creator->name }}</td>
                                <td class="px-5 py-3.5">{{ $o->created_at->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-right font-medium">@rupiah($o->total)</td>
                                <td class="px-5 py-3.5"><x-status :value="$o->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-stone-400">Belum ada pemesanan
                                    bahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
