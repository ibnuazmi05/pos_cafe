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
                    <x-select name="user_id" label="Pegawai" :options="$users->pluck('name', 'id')" :selected="$filters['user_id'] ?? null"
                        blank="Semua Pegawai" />
                </div>
                <div class="min-w-44">
                    <x-select name="action" label="Aksi" :options="$actions->mapWithKeys(fn($a) => [$a => $a])" :selected="$filters['action'] ?? null" blank="Semua Aksi" />
                </div>
                <div>
                    <a href="{{ route('superadmin.activity-logs.index') }}"
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
                            <th class="px-5 py-3.5">Waktu</th>
                            <th class="px-5 py-3.5">Pegawai</th>
                            <th class="px-5 py-3.5">Aksi</th>
                            <th class="px-5 py-3.5">Objek</th>
                            <th class="px-5 py-3.5">Detail</th>
                            <th class="px-5 py-3.5">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-stone-600">
                                    {{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                                <td class="px-5 py-3.5 font-medium text-stone-800">{{ $log->user?->name ?? '-' }}</td>
                                <td class="px-5 py-3.5"><code
                                        class="text-sm font-mono text-stone-700 px-1.5 py-0.5 rounded">{{ $log->action }}</code>
                                </td>
                                <td class="px-5 py-3.5 text-stone-700">
                                    {{ $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-stone-500 max-w-xs truncate">
                                    {{ $log->properties ? \Illuminate\Support\Str::limit(json_encode($log->properties, JSON_UNESCAPED_UNICODE), 120) : '' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-stone-400 font-mono">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-stone-400">Belum ada aktivitas
                                    tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
