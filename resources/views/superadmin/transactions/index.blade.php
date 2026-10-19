<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="GET" x-data
                @change="if ($event.target.tagName === 'SELECT' || $event.target.type === 'date') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 items-end">
                <div>
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-full" :value="$filters['date_from'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-full" :value="$filters['date_to'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="cashier_id" value="Kasir" />
                    <select id="cashier_id" name="cashier_id"
                        class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none">
                        <option value="">Semua Kasir</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" @selected((string) ($filters['cashier_id'] ?? '') === (string) $c->id)>{{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="payment_method" value="Metode" />
                    <select id="payment_method" name="payment_method"
                        class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach ($methods as $v => $l)
                            <option value="{{ $v }}" @selected(($filters['payment_method'] ?? '') === $v)>{{ $l }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="verification_status" value="Verifikasi" />
                    <select id="verification_status" name="verification_status"
                        class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach ($verifications as $v => $l)
                            <option value="{{ $v }}" @selected(($filters['verification_status'] ?? '') === $v)>{{ $l }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <a href="{{ route('superadmin.transactions.index') }}"
                        class="min-w-35 inline-flex items-center justify-center px-4 py-2.5 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition w-full">
                        Reset
                    </a>
                </div>
            </form>

            <div class="bg-white border border-stone-200/90 rounded-2xl p-4 text-sm flex flex-wrap gap-8 shadow-xs">
                <div><span class="text-xs text-stone-500 block">Transaksi Selesai</span> <strong
                        class="text-base text-stone-900">{{ $summary->count }} transaksi</strong></div>
                <div><span class="text-xs text-stone-500 block">Total Omzet Penjualan</span> <strong
                        class="text-base text-amber-800">@rupiah($summary->total)</strong></div>
            </div>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Invoice</th>
                                <th class="px-5 py-3 font-semibold">Waktu</th>
                                <th class="px-5 py-3 font-semibold">Kasir</th>
                                <th class="px-5 py-3 font-semibold">Metode</th>
                                <th class="px-5 py-3 text-right font-semibold">Total</th>
                                <th class="px-5 py-3 font-semibold">Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($transactions as $t)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">
                                        <a href="{{ route('superadmin.transactions.show', $t) }}"
                                            class="text-amber-800 hover:text-amber-900 hover:underline">{{ $t->invoice_no }}</a>
                                    </td>
                                    <td class="px-5 py-3.5 text-stone-600">
                                        {{ $t->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-stone-700">{{ $t->cashier->name }}</td>
                                    <td class="px-5 py-3.5 text-stone-700">{{ $t->payment_method->label() }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-stone-900">@rupiah($t->total)</td>
                                    <td class="px-5 py-3.5"><x-status :value="$t->verification_status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-stone-400">Tidak ada transaksi
                                        ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</x-app-layout>
