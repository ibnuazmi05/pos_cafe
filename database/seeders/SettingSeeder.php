<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'cafe_name' => 'Cafe Kita',
            'cafe_address' => 'Jl. Contoh No. 1, Semarang',
            'tax_enabled' => '1',   // 1 = PPN aktif, 0 = nonaktif
            'tax_rate' => '11',     // persen; ubah sesuai ketentuan (mis. PB1 10%)
            'discount' => '0', // batas diskon kasir (persen dari subtotal)
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
