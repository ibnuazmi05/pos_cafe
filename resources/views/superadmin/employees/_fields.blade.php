@php $employee = $employee ?? null; $isSuper = $employee?->isSuperadmin() ?? false; @endphp

<div>
    <x-input-label for="name" value="Nama Lengkap" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $employee?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
</div>

<div>
    <x-input-label for="email" value="Alamat Email" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $employee?->email)" required />
    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
</div>

<div>
    <x-input-label for="phone" value="Nomor Telepon" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $employee?->phone)" />
    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
</div>

<div>
    <x-input-label for="role" value="Role Akses" />
    @if ($isSuper)
        <p class="mt-1 text-sm text-stone-600">Superadmin (tidak dapat diubah)</p>
    @else
        <select id="role" name="role" class="mt-1 block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" required>
            @foreach ([\App\Enums\Role::Admin, \App\Enums\Role::Kasir] as $r)
                <option value="{{ $r->value }}" @selected(old('role', $employee?->role?->value ?? 'kasir') === $r->value)>{{ $r->label() }}</option>
            @endforeach
        </select>
    @endif
    <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
</div>
