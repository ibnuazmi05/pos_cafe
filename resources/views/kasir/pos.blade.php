<x-app-layout>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function pos(cfg) {
            return {
                products: cfg.products,
                categories: cfg.categories,
                taxEnabled: cfg.taxEnabled,
                taxRate: cfg.taxRate,
                search: '',
                category: 'all',
                cart: [],
                discountPercent: cfg.discount,
                useDiscount: true,
                rounding: cfg.rounding,
                method: 'cash',
                paid: '',
                busy: false,
                error: '',
                done: null,
                page: 1,
                perPage: 12,

                init() {
                    this.$watch('search', () => { this.page = 1; });
                },

                get filtered() {
                    const q = this.search.trim().toLowerCase();
                    return this.products.filter(p =>
                        (this.category === 'all' || p.category_id === this.category) &&
                        (q === '' || p.name.toLowerCase().includes(q)));
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
                },
                get paginatedProducts() {
                    const start = (this.page - 1) * this.perPage;
                    return this.filtered.slice(start, start + this.perPage);
                },
                get pageStart() {
                    return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1;
                },
                get pageEnd() {
                    return Math.min(this.page * this.perPage, this.filtered.length);
                },
                setCategory(c) {
                    this.category = c;
                    this.page = 1;
                },
                qtyInCart(id) {
                    const l = this.cart.find(x => x.id === id);
                    return l ? l.qty : 0;
                },
                add(p) {
                    if (p.max !== null && this.qtyInCart(p.id) >= p.max) return;
                    const l = this.cart.find(x => x.id === p.id);
                    if (l) {
                        l.qty++;
                    } else {
                        this.cart.push({
                            id: p.id,
                            name: p.name,
                            price: p.price,
                            qty: 1,
                            note: '',
                            max: p.max
                        });
                    }
                },
                inc(l) {
                    if (l.max === null || l.qty < l.max) l.qty++;
                },
                dec(l) {
                    if (l.qty > 1) {
                        l.qty--;
                    } else {
                        this.remove(l);
                    }
                },
                remove(l) {
                    this.cart = this.cart.filter(x => x !== l);
                },

                get subtotal() {
                    return this.cart.reduce((s, l) => s + l.price * l.qty, 0);
                },
                get discount() {
                    return Math.min(Math.round(this.subtotal * this.discountPercent / 100), this.subtotal);
                },
                get taxBase() {
                    return this.subtotal - this.discount;
                },
                get tax() {
                    return this.taxEnabled ? Math.round(this.taxBase * this.taxRate / 100) : 0;
                },
                get rawTotal() {
                    return this.taxBase + this.tax;
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
                get paidNumber() {
                    return parseFloat(this.paid) || 0;
                },
                get change() {
                    return this.method === 'cash' ? Math.max(0, this.paidNumber - this.total) : 0;
                },
                get canPay() {
                    return !this.busy && this.cart.length > 0 &&
                        (this.method !== 'cash' || this.paidNumber >= this.total);
                },
                quick(amount) {
                    this.paid = amount;
                },
                roundedUp(step) {
                    return Math.ceil(this.total / step) * step;
                },
                fmt(n) {
                    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
                },

                // Cetak struk lewat iframe tersembunyi (tanpa tab baru, tanpa pindah halaman)
                printReceipt(url) {
                    if (!url) return;

                    let frame = document.getElementById('print-frame');
                    if (!frame) {
                        frame = document.createElement('iframe');
                        frame.id = 'print-frame';
                        frame.setAttribute('aria-hidden', 'true');
                        frame.style.cssText =
                            'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
                        document.body.appendChild(frame);
                    }

                    const sep = url.includes('?') ? '&' : '?';
                    // _t memastikan iframe selalu dimuat ulang (agar cetak ulang tetap berfungsi)
                    frame.src = url + sep + 'autoprint=1&_t=' + Date.now();
                },

                async submit() {
                    if (!this.canPay) return;
                    this.busy = true;
                    this.error = '';
                    try {
                        const res = await fetch(cfg.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': cfg.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                items: this.cart.map(l => ({
                                    product_id: l.id,
                                    quantity: l.qty,
                                    note: l.note || null
                                })),
                                apply_discount: this.discount > 0,
                                payment_method: this.method,
                                paid_amount: this.method === 'cash' ? this.paidNumber : this.total,
                            }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.done = data; // tampilkan modal sukses
                            this.printReceipt(data.receipt_url); // cetak di iframe
                        } else if (res.status === 419) {
                            this.error = 'Sesi habis. Muat ulang halaman lalu coba lagi.';
                        } else if (res.status === 409 && data.message) {
                            this.error = data.message;
                        } else if (data.errors) {
                            this.error = Object.values(data.errors).flat().join(' ');
                        } else {
                            this.error = data.message || 'Terjadi kesalahan. Coba lagi.';
                        }
                    } catch (e) {
                        this.error = 'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.';
                    } finally {
                        this.busy = false;
                    }
                },
            };
        }
    </script>

    <div class="py-6" x-data="pos(@js($config))">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="mt-2 grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                {{-- Daftar menu --}}
                <div class="lg:col-span-3 space-y-4">
                    <div class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="search" x-model="search" placeholder="Cari menu cafe..."
                                class="w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-400 focus:bg-white focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">
                        </div>

                        <div class="flex flex-wrap gap-2 text-sm">
                            <button type="button" @click="setCategory('all')"
                                :class="category === 'all' ?
                                    'bg-amber-700 text-white font-semibold border-amber-700 shadow-xs' :
                                    'bg-stone-50 text-stone-600 border-stone-200 hover:bg-stone-100 hover:text-stone-900'"
                                class="rounded-xl px-4 py-2 border text-xs sm:text-sm font-semibold transition duration-150">Semua</button>
                            <template x-for="c in categories" :key="c.id">
                                <button type="button" @click="setCategory(c.id)"
                                    :class="category === c.id ?
                                        'bg-amber-700 text-white font-semibold border-amber-700 shadow-xs' :
                                        'bg-stone-50 text-stone-600 border-stone-200 hover:bg-stone-100 hover:text-stone-900'"
                                    class="rounded-xl px-4 py-2 border text-xs sm:text-sm font-semibold transition duration-150"
                                    x-text="c.name"></button>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3.5">
                        <template x-for="p in paginatedProducts" :key="p.id">
                            <button type="button" @click="add(p)"
                                :disabled="p.max !== null && qtyInCart(p.id) >= p.max"
                                class="group relative text-left bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition duration-150 flex flex-col justify-between min-h-[110px]">
                                <div>
                                    <span
                                        class="block font-semibold text-stone-900 group-hover:text-amber-800 transition line-clamp-2"
                                        x-text="p.name"></span>
                                    <span class="mt-1 block text-sm font-bold text-amber-800"
                                        x-text="fmt(p.price)"></span>
                                </div>
                                <div class="mt-2">
                                    <span x-show="p.max !== null && p.max <= 10" class="text-xs font-medium"
                                        :class="p.max === 0 ? 'text-rose-600' : 'text-stone-500'"
                                        x-text="p.max === 0 ? 'Bahan habis' : 'Sisa ' + p.max + ' porsi'"></span>
                                </div>
                                <span x-show="qtyInCart(p.id) > 0"
                                    class="absolute top-3 right-3 rounded-lg bg-amber-700 px-2 py-0.5 text-xs font-bold text-white shadow-xs"
                                    x-text="qtyInCart(p.id)"></span>
                            </button>
                        </template>
                        <div x-show="filtered.length === 0"
                            class="col-span-full py-12 text-center text-stone-400 bg-white rounded-2xl border border-stone-200/90 p-6">
                            <svg class="mx-auto h-8 w-8 text-stone-300 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-sm">Menu tidak ditemukan.</p>
                        </div>
                    </div>

                    <template x-if="totalPages > 1">
                        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between pt-3">
                            <div class="flex flex-1 justify-between sm:hidden gap-2">
                                <button type="button" @click="page--" :disabled="page <= 1"
                                    class="inline-flex items-center px-4 py-2 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-700 bg-white hover:bg-stone-50 hover:text-stone-900 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-xs">
                                    Sebelumnya
                                </button>
                                <button type="button" @click="page++" :disabled="page >= totalPages"
                                    class="inline-flex items-center px-4 py-2 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-700 bg-white hover:bg-stone-50 hover:text-stone-900 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-xs">
                                    Berikutnya
                                </button>
                            </div>

                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
                                <div>
                                    <p class="text-xs text-stone-500">
                                        Menampilkan <span class="font-semibold text-stone-800" x-text="pageStart"></span> sampai <span class="font-semibold text-stone-800" x-text="pageEnd"></span> dari <span class="font-semibold text-stone-800" x-text="filtered.length"></span> menu
                                    </p>
                                </div>

                                <div>
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="page--" :disabled="page <= 1"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/90 bg-white text-stone-600 hover:bg-stone-50 hover:text-stone-900 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-xs text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                            </svg>
                                        </button>

                                        <template x-for="p in totalPages" :key="p">
                                            <button type="button" @click="page = p"
                                                :class="page === p ? 'bg-amber-700 text-white font-bold border-amber-700 shadow-xs' : 'bg-white text-stone-700 hover:bg-stone-50 hover:text-stone-900 border-stone-200/90 shadow-xs font-semibold'"
                                                class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-xl text-xs border transition"
                                                x-text="p"></button>
                                        </template>

                                        <button type="button" @click="page++" :disabled="page >= totalPages"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/90 bg-white text-stone-600 hover:bg-stone-50 hover:text-stone-900 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-xs text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </nav>
                    </template>
                </div>

                {{-- Keranjang & pembayaran --}}
                <div class="lg:col-span-2">
                    <div
                        class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs space-y-4 lg:sticky lg:top-20">
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <h3 class="font-bold text-base text-stone-900">Pesanan Aktif</h3>
                            <span class="text-xs font-medium text-stone-500"
                                x-text="cart.reduce((sum, i) => sum + i.qty, 0) + ' item'"></span>
                        </div>

                        <div x-show="cart.length === 0" class="py-8 text-center text-stone-400 space-y-1">
                            <svg class="mx-auto h-8 w-8 text-stone-300 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-sm">Keranjang masih kosong</p>
                            <p class="text-xs text-stone-400">Pilih menu di sebelah kiri untuk menambahkan pesanan</p>
                        </div>

                        <ul class="divide-y divide-stone-100 max-h-64 overflow-y-auto pr-1">
                            <template x-for="l in cart" :key="l.id">
                                <li class="py-2.5 space-y-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-36 flex-1">
                                            <p class="text-sm font-semibold text-stone-800 truncate" x-text="l.name">
                                            </p>
                                            <p class="text-xs text-stone-500" x-text="fmt(l.price)"></p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="dec(l)"
                                                class="h-7 w-7 rounded-lg border border-stone-300 text-stone-600 hover:bg-stone-100 flex items-center justify-center font-bold text-sm transition">&minus;</button>
                                            <span class="w-6 text-center text-xs font-semibold text-stone-800"
                                                x-text="l.qty"></span>
                                            <button type="button" @click="inc(l)"
                                                :disabled="l.max !== null && l.qty >= l.max"
                                                class="h-7 w-7 rounded-lg border border-stone-300 text-stone-600 hover:bg-stone-100 disabled:opacity-40 flex items-center justify-center font-bold text-sm transition">+</button>
                                        </div>
                                        <p class="w-24 text-right text-sm font-bold text-stone-900"
                                            x-text="fmt(l.price * l.qty)">
                                        </p>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <dl class="space-y-1.5 text-sm pt-2 border-t border-stone-100">
                            <div class="flex justify-between text-stone-600">
                                <dt>Subtotal</dt>
                                <dd class="font-medium text-stone-900" x-text="fmt(subtotal)"></dd>
                            </div>
                            <div class="flex justify-between text-rose-600 font-medium" x-show="discount > 0">
                                <dt>Diskon</dt>
                                <dd x-text="'-' + fmt(discount)"></dd>
                            </div>
                            <div class="flex justify-between text-stone-600" x-show="taxEnabled">
                                <dt>PPN (<span x-text="taxRate"></span>%)</dt>
                                <dd class="font-medium text-stone-900" x-text="fmt(tax)"></dd>
                            </div>
                            <div class="flex justify-between text-stone-600" x-show="roundingAmount !== 0">
                                <dt>Pembulatan</dt>
                                <dd class="font-medium text-stone-900" x-text="fmt(roundingAmount)"></dd>
                            </div>
                            <div
                                class="flex justify-between border-t border-stone-200 pt-2.5 text-base font-bold text-stone-900">
                                <dt>Total Tagihan</dt>
                                <dd class="text-amber-800 text-lg" x-text="fmt(total)"></dd>
                            </div>
                        </dl>

                        <div class="space-y-3 pt-2">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                @foreach (\App\Enums\PaymentMethod::cases() as $m)
                                    <button type="button" @click="method = '{{ $m->value }}'"
                                        :class="method === '{{ $m->value }}' ?
                                            'bg-amber-700 text-white border-amber-700 font-semibold shadow-xs' :
                                            'bg-stone-50 text-stone-700 border-stone-200 hover:bg-stone-100'"
                                        class="rounded-xl border py-2.5 text-xs sm:text-sm font-medium transition duration-150">{{ $m->label() }}</button>
                                @endforeach
                            </div>

                            <div x-show="method === 'cash'" class="space-y-2.5">
                                <label class="block text-xs font-semibold text-stone-600">Nominal Diterima</label>
                                <div
                                    class="flex items-center w-full border border-stone-300 rounded-xl bg-white px-3.5 py-1 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-500/20 shadow-xs transition">
                                    <span class="text-sm font-semibold text-stone-500 mr-2">Rp</span>
                                    <input type="number" min="0" step="500" x-model="paid"
                                        placeholder="0"
                                        class="block w-full border-0 outline-none focus:ring-0 text-base font-semibold text-stone-900 placeholder:text-stone-400 p-1">
                                </div>
                                <div class="flex flex-wrap gap-1.5 text-xs">
                                    <button type="button" @click="quick(total)"
                                        class="rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 font-medium text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition">Uang
                                        pas</button>
                                    <button type="button" @click="quick(roundedUp(5000))"
                                        class="rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 font-medium text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition"
                                        x-text="fmt(roundedUp(5000))"></button>
                                    <button type="button" @click="quick(roundedUp(10000))"
                                        class="rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 font-medium text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition"
                                        x-text="fmt(roundedUp(10000))"></button>
                                    <button type="button" @click="quick(roundedUp(50000))"
                                        class="rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 font-medium text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition"
                                        x-text="fmt(roundedUp(50000))"></button>
                                </div>
                                <div class="flex justify-between items-center text-sm pt-1">
                                    <span class="text-stone-500">Kembalian:</span>
                                    <span class="font-bold text-base text-emerald-700" x-text="fmt(change)"></span>
                                </div>
                            </div>
                            <p x-show="method !== 'cash'"
                                class="text-xs text-stone-500 bg-stone-50 rounded-xl p-2.5 border border-stone-200">
                                Pembayaran non-tunai sebesar pas nilai total tagihan.
                            </p>
                        </div>

                        <p x-show="error" x-cloak
                            class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 font-medium"
                            x-text="error"></p>

                        <button type="button" @click="submit()" :disabled="!canPay"
                            class="w-full rounded-xl bg-amber-700 py-3 text-sm font-semibold text-white shadow-xs hover:bg-amber-800 active:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition duration-150">
                            <span x-show="!busy">Proses Pembayaran</span><span x-show="busy"
                                x-cloak>Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaksi berhasil --}}
        <div x-show="done" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/70 p-4 backdrop-blur-xs">
            <div
                class="w-full max-w-sm rounded-2xl bg-white p-6 text-center space-y-4 shadow-2xl border border-stone-200">
                <div class="mx-auto flex h-10 w-10 items-center justify-center text-amber-700">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Transaksi Berhasil</h3>
                    <p class="text-xs text-stone-500 mt-0.5" x-text="done && done.invoice_no"></p>
                </div>
                <div class="rounded-xl bg-stone-50 border border-stone-100 p-4 space-y-1">
                    <p class="text-xs text-stone-500">Total Pembayaran</p>
                    <p class="text-2xl font-bold text-amber-800" x-text="done && fmt(done.total)"></p>
                    <p class="text-xs text-stone-600 pt-1" x-show="done && done.change > 0">
                        Kembalian: <strong class="text-emerald-700" x-text="done && fmt(done.change)"></strong>
                    </p>
                </div>
                <div class="flex gap-2.5 pt-1">
                    <button type="button" @click="printReceipt(done && done.receipt_url)"
                        class="flex-1 rounded-xl border border-stone-300 py-2.5 text-xs font-semibold text-stone-700 hover:bg-stone-50 transition">
                        Cetak Struk
                    </button>
                    <button type="button" @click="window.location.reload()"
                        class="flex-1 rounded-xl bg-amber-700 py-2.5 text-xs font-semibold text-white hover:bg-amber-800 transition">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
