@props(['action', 'from', 'to', 'type'])
@php
    $quick = [
        'Hari ini' => [now()->toDateString(), now()->toDateString()],
        '7 hari' => [now()->subDays(6)->toDateString(), now()->toDateString()],
        'Bulan ini' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        'Bulan lalu' => [
            now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
            now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
        ],
    ];
    $extra = request()->only(['status', 'supplier_id']);
@endphp

<div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs space-y-4">
    <form method="GET" action="{{ $action }}" x-data
        @change="if ($event.target.tagName === 'SELECT' || $event.target.type === 'date') $el.requestSubmit()"
        class="flex flex-wrap gap-4 items-end">
        <div>
            <x-input-label for="date_from" value="Dari" />
            <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$from" />
        </div>
        <div>
            <x-input-label for="date_to" value="Sampai" />
            <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$to" />
        </div>
        {{ $slot }}
        <div>
            <a href="{{ $action }}"
                class="min-w-35 inline-flex items-center justify-center px-4 py-2.5 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition w-full">
                Reset
            </a>
        </div>
    </form>

    <x-input-error :messages="$errors->get('date_to')" />

    <div class="flex flex-wrap items-center justify-between gap-3 text-xs pt-2 border-t border-stone-100">
        <div class="flex flex-wrap items-center gap-2 text-stone-500">
            <span class="font-medium">Rentang Cepat:</span>
            @foreach ($quick as $label => [$qFrom, $qTo])
                <a href="{{ $action }}?{{ http_build_query(array_merge($extra, ['date_from' => $qFrom, 'date_to' => $qTo])) }}"
                    class="rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 text-stone-600 hover:bg-stone-100 hover:text-stone-900 transition font-medium">{{ $label }}</a>
            @endforeach
        </div>

        @can('export-reports')
            @php $exportQuery = array_merge($extra, ['date_from' => $from, 'date_to' => $to]); @endphp
            <div class="flex items-center gap-2">
                <span class="font-medium text-stone-500">Ekspor:</span>
                <a href="{{ route('superadmin.reports.export', array_merge(['type' => $type, 'format' => 'pdf'], $exportQuery)) }}"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-1 font-semibold text-stone-700 hover:bg-stone-50 hover:border-stone-300 transition">PDF</a>
                <a href="{{ route('superadmin.reports.export', array_merge(['type' => $type, 'format' => 'xlsx'], $exportQuery)) }}"
                    class="rounded-lg border border-stone-200 bg-white px-3 py-1 font-semibold text-stone-700 hover:bg-stone-50 hover:border-stone-300 transition">Excel</a>
            </div>
        @endcan
    </div>
</div>
