<?php

namespace App\Http\Controllers;

use App\Models\PermintaanPerubahan;

class PersetujuanController extends Controller
{
    public function index()
    {
        $permintaan = PermintaanPerubahan::with(['pemesanan', 'jadwalLama', 'jadwalBaru'])
            ->orderByDesc('created_at')
            ->get();

        return view('persetujuan.index', compact('permintaan'));
    }
}
