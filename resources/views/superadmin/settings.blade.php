<x-app-layout>

    <div class="py-6" x-data="{
        cafeName: @js(old('cafe_name', $settings['cafe_name'] ?? '')),
        cafeAddress: @js(old('cafe_address', $settings['cafe_address'] ?? '')),
        taxEnabled: @js((bool) old('tax_enabled', $settings['tax_enabled'] ?? false)),
        taxRate: @js((float) old('tax_rate', $settings['tax_rate'] ?? 11)),
        discount: @js((float) old('discount', $settings['discount'] ?? 0)),
        rounding: 500,
        subtotal: 72000,
        get discountAmount() {
            const disc = parseFloat(this.discount) || 0;
            return Math.round(this.subtotal * (disc / 100));
        },
        get afterDiscount() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },
        get taxAmount() {
            if (!this.taxEnabled) return 0;
            const rate = parseFloat(this.taxRate) || 0;
            return Math.round(this.afterDiscount * (rate / 100));
        },
        get rawTotal() {
            return this.afterDiscount + this.taxAmount;
        },
        get total() {
            const step = Number(this.rounding) || 0;
            if (step <= 0) {
                return this.rawTotal;
            }
            return Math.round(this.rawTotal / step) * step;
        },
    
        get roundingAmount() {
            return this.total - this.rawTotal;
        },
        get paidAmount() {
            return 100000;
        },
        get changeAmount() {
            return Math.max(0, this.paidAmount - this.total);
        },
        fmt(val) {
            return 'Rp ' + Math.round(val || 0).toLocaleString('id-ID');
        }
    }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <x-errors-box />

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-7">
                    <form method="POST" action="{{ route('superadmin.settings.update') }}"
                        class="bg-white border border-stone-200/90 rounded-2xl shadow-xs p-6 space-y-6">
                        @csrf @method('PUT')

                        <div class="space-y-4">
                            <h3 class="font-bold text-base text-stone-900 pb-2 border-b border-stone-100">Identitas
                                Struk</h3>
                            <x-field name="cafe_name" label="Nama Cafe" :value="$settings['cafe_name']" required x-model="cafeName" />
                            <x-field type="textarea" :rows="3" name="cafe_address" label="Alamat"
                                :value="$settings['cafe_address']" x-model="cafeAddress" />
                        </div>

                        <div class="space-y-4">
                            <h3 class="font-bold text-base text-stone-900 pb-2 border-b border-stone-100">Pajak</h3>
                            <label
                                class="inline-flex items-center gap-2.5 text-sm text-stone-700 font-medium cursor-pointer">
                                <input type="hidden" name="tax_enabled" value="0">
                                <input type="checkbox" name="tax_enabled" value="1"
                                    class="rounded-md border-stone-300 text-amber-700 focus:ring-amber-500 focus:ring-offset-0"
                                    x-model="taxEnabled" @checked(old('tax_enabled', $settings['tax_enabled']))>
                                Kenakan PPN pada transaksi
                            </label>
                            <x-field name="tax_rate" label="Tarif Pajak (%)" type="number" step="0.01"
                                min="0" :value="$settings['tax_rate']" required x-model="taxRate"
                                hint="Sesuaikan dengan ketentuan yang berlaku bagi usaha Anda (mis. PPN 11% atau pajak restoran daerah)." />
                        </div>

                        <div class="space-y-4">
                            <h3 class="font-bold text-base text-stone-900 pb-2 border-b border-stone-100">Diskon Global
                            </h3>
                            <x-field name="discount" label="Tarif Diskon (%)" type="number" step="1"
                                min="0" max="100" :value="$settings['discount']" required x-model="discount"
                                hint="Isi 0 untuk menonaktifkan diskon default." />
                        </div>

                        <div
                            class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80 text-xs text-stone-500 leading-relaxed">
                            Perubahan berlaku untuk transaksi berikutnya. Transaksi yang sudah selesai menyimpan tarif
                            saat itu dan tidak berubah.
                        </div>

                        <div class="pt-2">
                            <x-primary-button>Simpan Pengaturan</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-5">
                    <div class="sticky top-20 space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <h3 class="font-bold text-stone-900 text-sm">Preview Struk Belanja</h3>
                        </div>

                        <div
                            class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs font-mono text-sm/6 text-stone-900 space-y-3">
                            <div class="text-center space-y-1">
                                <p class="font-bold text-sm text-stone-900" x-text="cafeName || 'Nama Cafe'"></p>
                                <p class="text-[12px] text-stone-500 leading-4 whitespace-pre-line" x-show="cafeAddress"
                                    x-text="cafeAddress"></p>
                            </div>

                            <div class="border-t border-dashed border-stone-300"></div>

                            <div class="space-y-0.5 text-[11px] text-stone-600">
                                <div class="flex justify-between">
                                    <span>No. Invoice</span>
                                    <span class="font-semibold text-stone-800">INV-{{ date('Ymd') }}-001</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Tanggal</span>
                                    <span>{{ date('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kasir</span>
                                    <span>{{ auth()->user()->name }}</span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-stone-300"></div>

                            <div class="space-y-1.5 text-[11px]">
                                <div>
                                    <p class="font-medium text-stone-800">Kopi Susu Gula Aren</p>
                                    <div class="flex justify-between text-stone-500">
                                        <span>&nbsp;&nbsp;2 x 22.000</span>
                                        <span class="text-stone-800 font-medium">44.000</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-stone-800">Croissant Butter</p>
                                    <div class="flex justify-between text-stone-500">
                                        <span>&nbsp;&nbsp;1 x 28.000</span>
                                        <span class="text-stone-800 font-medium">28.000</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-stone-300"></div>

                            <div class="space-y-1 text-[11px]">
                                <div class="flex justify-between text-stone-600">
                                    <span>Subtotal</span>
                                    <span>Rp 72.000</span>
                                </div>
                                <template x-if="parseFloat(discount || 0) > 0">
                                    <div class="flex justify-between text-rose-600">
                                        <span>Diskon (<span x-text="discount"></span>%)</span>
                                        <span x-text="'-' + fmt(discountAmount)"></span>
                                    </div>
                                </template>
                                <template x-if="taxEnabled && parseFloat(taxRate || 0) > 0">
                                    <div class="flex justify-between text-stone-600">
                                        <span>PPN (<span x-text="taxRate"></span>%)</span>
                                        <span x-text="fmt(taxAmount)"></span>
                                    </div>
                                </template>
                                <template x-if="rounding && parseFloat(rounding || 0) > 0">
                                    <div class="flex justify-between text-stone-600">
                                        <span>Pembulatan</span>
                                        <span x-text="fmt(roundingAmount)"></span>
                                    </div>
                                </template>
                                <div
                                    class="flex justify-between text-xs font-bold text-stone-900 pt-1.5 border-t border-stone-200">
                                    <span>TOTAL</span>
                                    <span x-text="fmt(total)"></span>
                                </div>
                                <div class="flex justify-between text-stone-600 pt-1">
                                    <span>Bayar (Cash)</span>
                                    <span x-text="fmt(paidAmount)"></span>
                                </div>
                                <div class="flex justify-between text-stone-600">
                                    <span>Kembali</span>
                                    <span x-text="fmt(changeAmount)"></span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-stone-300"></div>

                            <p class="text-center text-[10px] text-stone-400">Terima kasih atas kunjungan Anda!</p>
                        </div>

                        <p class="text-[11px] text-stone-400 text-center">Preview ini diperbarui secara otomatis secara
                            real-time saat Anda mengubah pengaturan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
