<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Lapangan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $pemilik = User::updateOrCreate(
            ['email' => 'owner@sewalap.id'],
            [
                'name' => 'Owner Lapangan Utama',
                'password' => bcrypt('password'),
                'role' => 'pemilik',
                'status' => 'aktif',
                'no_hp' => '081234567890',
            ]
        );

        $penyewa = User::updateOrCreate(
            ['email' => 'member@sewalap.id'],
            [
                'name' => 'Member Aktif',
                'password' => bcrypt('password'),
                'role' => 'penyewa',
                'status' => 'aktif',
                'no_hp' => '089876543210',
            ]
        );

        $hargaColumn = null;

        if (Schema::hasColumn('lapangan', 'harga_per_jam')) {
            $hargaColumn = 'harga_per_jam';
        } elseif (Schema::hasColumn('lapangan', 'harga_sewa')) {
            $hargaColumn = 'harga_sewa';
        }

        $lapanganData = [
            [
                'nama_lapangan' => 'Arena Futsal Premium',
                'kategori' => 'Futsal',
                'lokasi' => 'Jakarta Selatan',
                'harga' => 150000,
                'status' => 'promo',
                'is_verified' => true,
                'rating' => 4.7,
                'deskripsi' => 'Lapangan futsal dengan rumput sintetis berkualitas tinggi dan fasilitas lengkap.',
            ],
            [
                'nama_lapangan' => 'Lapangan Badminton Elite',
                'kategori' => 'Badminton',
                'lokasi' => 'Bandung',
                'harga' => 120000,
                'status' => 'standard',
                'is_verified' => true,
                'rating' => 4.5,
                'deskripsi' => 'Lapangan indoor berstandar nasional dengan pencahayaan yang nyaman.',
            ],
            [
                'nama_lapangan' => 'Arena Basket Street',
                'kategori' => 'Basket',
                'lokasi' => 'Surabaya',
                'harga' => 180000,
                'status' => 'pending',
                'is_verified' => false,
                'rating' => 4.2,
                'deskripsi' => 'Lapangan basket outdoor dengan vibes streetball dan mural menarik.',
            ],
        ];

        $lapanganRecords = collect($lapanganData)->map(function (array $data) use ($pemilik, $hargaColumn) {
            $harga = $data['harga'];
            $kategoriNama = $data['kategori'];
            unset($data['harga']);

            $kategori = Kategori::firstOrCreate(
                ['nama_kategori' => $kategoriNama],
                ['deskripsi' => $kategoriNama . ' unggulan']
            );

            $payload = array_merge($data, [
                'pemilik_id' => $pemilik->id,
                'id_kategori' => $kategori->id,
                'foto' => ['examples/lapangan-1.jpg'],
            ]);

            if ($hargaColumn) {
                $payload[$hargaColumn] = $harga;
            }

            return Lapangan::updateOrCreate(
                [
                    'nama_lapangan' => $data['nama_lapangan'],
                    'pemilik_id' => $pemilik->id,
                ],
                $payload
            );
        });

        $lapanganFutsal = $lapanganRecords->first();

        if (!$lapanganFutsal) {
            return;
        }

        if (!Schema::hasTable('jadwal_lapangan')) {
            return;
        }

        $jadwalTanggal = now()->toDateString();
        $jadwalStart = '18:00:00';
        $jadwalEnd = '20:00:00';

        $jadwalId = DB::table('jadwal_lapangan')->updateOrInsert(
            [
                'lapangan_id' => $lapanganFutsal->id,
                'tanggal' => $jadwalTanggal,
                'jam_mulai' => $jadwalStart,
            ],
            [
                'jam_selesai' => $jadwalEnd,
                'tersedia' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $jadwal = DB::table('jadwal_lapangan')->where([
            'lapangan_id' => $lapanganFutsal->id,
            'tanggal' => $jadwalTanggal,
            'jam_mulai' => $jadwalStart,
        ])->orderByDesc('id')->first();

        if (!$jadwal) {
            return;
        }

        $pemesananAttributes = [
            'penyewa_id' => $penyewa->id,
            'lapangan_id' => $lapanganFutsal->id,
        ];

        $pemesananValues = ['status' => 'dibayar'];

        if (Schema::hasColumn('pemesanan', 'jadwal_id')) {
            $pemesananAttributes['jadwal_id'] = $jadwal->id;
            $pemesananValues['jadwal_id'] = $jadwal->id;
        }

        $pemesanan = Pemesanan::updateOrCreate(
            $pemesananAttributes,
            $pemesananValues
        );

        $hargaLapangan = $hargaColumn ? (float) ($lapanganFutsal->getAttribute($hargaColumn) ?? 0) : 0;
        $totalPembayaran = max($hargaLapangan * 2, 0);

        Pembayaran::updateOrCreate(
            [
                'order_id' => 'INV-' . strtoupper(Str::random(8)),
            ],
            [
                'pemesanan_id' => $pemesanan->id,
                'metode' => 'qris',
                'jumlah' => $totalPembayaran,
                'status' => 'berhasil',
                'payment_url' => 'https://payments.example.com/mock',
                'tanggal_pembayaran' => Carbon::now()->subDay(),
            ]
        );
    }
}
