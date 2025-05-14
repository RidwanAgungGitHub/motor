<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;

class BarangSeeder extends Seeder
{
    public function run()
    {
        $barangs = [
            [
                'nama_barang' => 'Monitor LED',
                'merek' => 'Samsung',
                'harga' => 2500000,
                'satuan' => 'unit',
                'stok' => 15
            ],
            [
                'nama_barang' => 'Keyboard Mechanical',
                'merek' => 'Logitech',
                'harga' => 750000,
                'satuan' => 'pcs',
                'stok' => 25
            ],
            [
                'nama_barang' => 'Mouse Wireless',
                'merek' => 'Logitech',
                'harga' => 350000,
                'satuan' => 'pcs',
                'stok' => 30
            ],
        ];

        foreach ($barangs as $barang) {
            Barang::create($barang);
        }
    }
}
