<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" x-data @change="if ($event.target.tagName === 'SELECT') $el.requestSubmit()"
                @input.debounce.150ms="if ($event.target.name === 'q') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <x-input-label for="q" value="Cari Pegawai" />
                    <x-text-input id="q" name="q" class="mt-1" placeholder="Ketik nama atau email..."
                        :value="$filters['q'] ?? ''" x-init="if ($el.value) {
                            $el.focus();
                            $el.setSelectionRange($el.value.length, $el.value.length)
                        }" />
                </div>
                <div class="min-w-44">
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role"
                        class="mt-1 block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none">
                        <option value="">Semua Role</option>
                        @foreach (\App\Enums\Role::options() as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-36">
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status"
                        class="mt-1 block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            </form>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Email</th>
                                <th class="px-5 py-3 font-semibold">Telepon</th>
                                <th class="px-5 py-3 font-semibold">Role</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $employee->name }}</td>
                                    <td class="px-5 py-3.5 text-stone-600">{{ $employee->email }}</td>
                                    <td class="px-5 py-3.5 text-stone-700 font-mono text-xs">
                                        {{ $employee->phone ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-stone-700"><x-status :value="$employee->role" /></td>
                                    <td class="px-5 py-3.5 text-stone-700 font-medium">
                                        {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex justify-end gap-3 text-xs">
                                            <a href="{{ route('superadmin.employees.edit', $employee) }}"
                                                class="font-semibold text-amber-800 hover:text-amber-900 hover:underline">Edit</a>
                                            @can('toggleActive', $employee)
                                                <form method="POST"
                                                    action="{{ route('superadmin.employees.toggle-active', $employee) }}"
                                                    onsubmit="return confirm('{{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ e($employee->name) }}?')">
                                                    @csrf @method('PATCH')
                                                    <button
                                                        class="font-semibold {{ $employee->is_active ? 'text-rose-600 hover:text-rose-700' : 'text-emerald-700 hover:text-emerald-800' }} hover:underline">
                                                        {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-stone-400">Tidak ada pegawai
                                        ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $employees->links() }}
        </div>
    </div>
</x-app-layout>
