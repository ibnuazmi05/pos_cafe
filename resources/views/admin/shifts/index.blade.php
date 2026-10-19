<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <x-errors-box />

            @php
                $activeFilters = array_filter(
                    request()->only(['date_from', 'date_to', 'cashier_id']),
                    fn($v) => filled($v),
                );
            @endphp

            <div class="flex flex-wrap gap-2 text-sm">
                @foreach (['all' => 'Semua Shift', 'open' => 'Shift Berjalan', 'closed' => 'Sudah Ditutup'] as $key => $label)
                    <a href="{{ route('admin.shifts.index', array_merge($activeFilters, ['status' => $key])) }}"
                        class="rounded-full px-4 py-1.5 border text-xs sm:text-sm font-medium transition duration-150 {{ $selected === $key ? 'bg-amber-700 text-white border-amber-700 shadow-xs' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50 hover:text-stone-900' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" x-data
                @change="if ($event.target.tagName === 'SELECT' || $event.target.type === 'date') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs flex flex-wrap gap-4 items-end">
                <input type="hidden" name="status" value="{{ $selected }}">

                <div class="min-w-40">
                    <x-input-label for="date_from" value="Dari Tanggal" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$filters['date_from'] ?? ''" />
                </div>
                <div class="min-w-40">
                    <x-input-label for="date_to" value="Sampai Tanggal" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$filters['date_to'] ?? ''" />
                </div>
                <div class="min-w-48">
                    <x-select name="cashier_id" label="Kasir" :options="$cashiers" :selected="$filters['cashier_id'] ?? null"
                        blank="Semua Kasir" />
                </div>
                <div>
                    <a href="{{ route('admin.shifts.index', $selected !== 'all' ? ['status' => $selected] : []) }}"
                        class="inline-flex items-center px-4 py-2.5 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition">
                        Reset
                    </a>
                </div>
            </form>


            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kasir</th>
                                <th class="px-5 py-3 font-semibold">Dibuka</th>
                                <th class="px-5 py-3 font-semibold">Ditutup</th>
                                <th class="px-5 py-3 text-right font-semibold">Modal</th>
                                <th class="px-5 py-3 text-right font-semibold">Diharapkan</th>
                                <th class="px-5 py-3 text-right font-semibold">Dihitung</th>
                                <th class="px-5 py-3 text-right font-semibold">Selisih</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($shifts as $s)
                                <tr class="align-top hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $s->user->name }}</td>
                                    <td class="px-5 py-3.5 text-stone-600">
                                        {{ $s->opened_at->translatedFormat('d M Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-stone-600">
                                        {{ $s->closed_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium text-stone-800">@rupiah($s->opening_cash)</td>
                                    <td class="px-5 py-3.5 text-right font-medium text-stone-800">@rupiah($s->isOpen() ? $expected[$s->id] : $s->expected_cash)</td>
                                    <td class="px-5 py-3.5 text-right font-medium text-stone-800">
                                        {{ $s->isOpen() ? '-' : \App\Support\Format::rupiah($s->closing_cash) }}</td>
                                    <td
                                        class="px-5 py-3.5 text-right font-bold {{ $s->isOpen() ? 'text-stone-400' : ((float) $s->difference == 0 ? 'text-emerald-700' : 'text-rose-600') }}">
                                        {{ $s->isOpen() ? '-' : \App\Support\Format::rupiah($s->difference) }}
                                    </td>
                                    <td class="px-5 py-3.5"><x-status :value="$s->status" /></td>
                                </tr>
                                @if ($s->isOpen())
                                    @can('forceClose', $s)
                                        <tr class="bg-rose-50/40">
                                            <td colspan="8" class="px-5 py-3">
                                                <details>
                                                    <summary
                                                        class="cursor-pointer text-xs font-semibold text-rose-600 hover:underline">
                                                        Tutup paksa shift ini</summary>
                                                    <form method="POST"
                                                        action="{{ route('admin.shifts.force-close', $s) }}"
                                                        class="mt-3 flex flex-wrap items-end gap-3"
                                                        onsubmit="return confirm('Tutup paksa shift {{ e($s->user->name) }}?')">
                                                        @csrf @method('PATCH')
                                                        <div>
                                                            <x-input-label value="Uang tunai di laci (Rp)" />
                                                            <x-text-input name="closing_cash" type="number" step="500"
                                                                min="0" required class="mt-1" />
                                                        </div>
                                                        <div class="flex-1 min-w-[16rem]">
                                                            <x-input-label value="Alasan" />
                                                            <x-text-input name="note" required maxlength="500"
                                                                class="mt-1 w-full" />
                                                        </div>
                                                        <x-danger-button>Tutup Paksa</x-danger-button>
                                                    </form>
                                                </details>
                                            </td>
                                        </tr>
                                    @endcan
                                @endif
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-stone-400">Belum ada shift.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $shifts->links() }}
        </div>
    </div>
</x-app-layout>
