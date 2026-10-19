<x-app-layout>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <x-errors-box />

            @include('shared.transaction-detail', ['transaction' => $transaction])

            @can('verify', $transaction)
                <form method="POST" action="{{ route('admin.verifications.update', $transaction) }}"
                    class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    @csrf @method('PATCH')
                    <h3 class="font-medium text-gray-900">Verifikasi</h3>
                    <p class="text-sm text-gray-500">Cocokkan transaksi dengan pembayaran sebenarnya. Isi transaksi tidak
                        dapat diubah, hanya status verifikasi dan catatan.</p>

                    <div class="flex gap-6 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="verification_status" value="verified" @checked(old('verification_status', $transaction->verification_status->value === 'flagged' ? '' : 'verified') === 'verified')>
                            Sesuai (terverifikasi)
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="verification_status" value="flagged" @checked(old('verification_status', $transaction->verification_status->value) === 'flagged')>
                            Ada selisih (tandai)
                        </label>
                    </div>

                    <div>
                        <x-input-label for="note" value="Catatan (wajib jika ada selisih)" />
                        <textarea id="note" name="note" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('note', $transaction->note) }}</textarea>
                    </div>

                    <x-primary-button>Simpan Verifikasi</x-primary-button>
                    <a href="{{ route('admin.verifications.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </form>
            @endcan
        </div>
    </div>
</x-app-layout>
