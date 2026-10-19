@extends('layouts.struk')

@section('title', 'Struk ' . $transaction->invoice_no)

@section('content')
    <div class="no-print max-w-xs mx-auto mt-4 flex gap-2">
        <button onclick="window.print()"
            class="flex-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Cetak</button>
        @if (auth()->user()->isKasir())
            <a href="{{ route('kasir.pos') }}"
                class="flex-1 rounded-md bg-white border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">Kembali
                ke Transaksi</a>
        @endif
    </div>

    <div class="receipt receipt-wrap bg-white shadow max-w-xs mx-auto my-4 p-4 text-gray-900">
        <div class="text-center">
            <p class="text-sm font-bold">{{ $cafeName }}</p>
            @if ($cafeAddress)
                <p>{{ $cafeAddress }}</p>
            @endif
        </div>
        <div class="my-2 border-t border-dashed border-gray-500"></div>
        <div class="flex justify-between"><span>No</span><span>{{ $transaction->invoice_no }}</span></div>
        <div class="flex justify-between">
            <span>Tanggal</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between"><span>Kasir</span><span>{{ $transaction->cashier->name }}</span></div>
        <div class="my-2 border-t border-dashed border-gray-500"></div>

        @foreach ($transaction->items as $item)
            <div>
                <p>{{ $item->product_name }}</p>
                <div class="flex justify-between">
                    <span>&nbsp;&nbsp;{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($item->note)
                    <p class="text-gray-500">&nbsp;&nbsp;* {{ $item->note }}</p>
                @endif
            </div>
        @endforeach

        <div class="my-2 border-t border-dashed border-gray-500"></div>
        <div class="flex justify-between">
            <span>Subtotal</span><span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ((float) $transaction->discount > 0)
            <div class="flex justify-between">
                <span>Diskon</span><span>-{{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ((float) $transaction->tax > 0)
            <div class="flex justify-between"><span>Pajak
                    ({{ \App\Support\Format::qty($transaction->tax_rate) }}%)</span><span>{{ number_format($transaction->tax, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ((float) $transaction->rounding != 0)
            <div class="flex justify-between">
                <span>Pembulatan</span>
                <span>
                    {{ (float) $transaction->rounding > 0 ? '+' : '-' }}Rp
                    {{ number_format(abs((float) $transaction->rounding), 0, ',', '.') }}
                </span>
            </div>
        @endif
        <div class="flex justify-between text-sm font-bold">
            <span>TOTAL</span><span>{{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        <div class="my-2 border-t border-dashed border-gray-500"></div>
        <div class="flex justify-between"><span>Bayar
                ({{ $transaction->payment_method->label() }})</span><span>{{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Kembali</span><span>{{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>
        <div class="my-2 border-t border-dashed border-gray-500"></div>
        <p class="text-center">Terima kasih atas kunjungan Anda!</p>
    </div>

    @if ($autoprint)
        <script>
            window.addEventListener('load', () => setTimeout(() => window.print(), 300));
        </script>
    @endif
@endsection
