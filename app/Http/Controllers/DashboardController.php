<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Kasir;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\StokLow; // Import model StokLow
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk mengakses DB
use App\Models\ProdukTerlaris; // Tambahkan di bagian atas

class DashboardController extends Controller
{
    public function index()
{
    $kategori = Kategori::count();
    $produk = Produk::count();
    $supplier = Supplier::count();
    $member = Kasir::count();

    // Ambil data produk dengan stok di bawah minimum
    $minStok = DB::table('produk')
    ->join('detail_produk', 'produk.kode_produk', '=', 'detail_produk.kode_produk')
    ->select(
        'produk.nama_produk',
        'detail_produk.stok_produk',
        'detail_produk.min_stok',
        'detail_produk.merk' // Ambil merk dari detail_produk
    )
    ->whereColumn('detail_produk.stok_produk', '<', 'detail_produk.min_stok')
    ->get();


    // Produk terlaris
    $produkTerlaris = ProdukTerlaris::orderBy('total_terjual', 'DESC')->limit(5)->get();

    // Produk terlaris hari ini
    $produkTerlarisHarian = DB::table('view_produk_terjual_harian')
        ->whereDate('tanggal', today())
        ->orderByDesc('total_terjual')
        ->get();

    $tanggal_awal = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');

    $data_tanggal = [];
    $data_pendapatan = [];

    while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
        $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

        $total_penjualan = 0;
        $penjualans = Penjualan::with('detailPenjualan')->where('created_at', 'LIKE', "%$tanggal_awal%")->get();
        foreach ($penjualans as $penjualan) {
            foreach ($penjualan->detailPenjualan as $detail) {
                $total_penjualan += $detail->harga_jual_produk * $detail->jumlah;
            }
        }

        $total_pembelian = 0;
        $pembelians = Pembelian::with('detilPembelian')->where('created_at', 'LIKE', "%$tanggal_awal%")->get();
        foreach ($pembelians as $pembelian) {
            foreach ($pembelian->detilPembelian as $detail) {
                $total_pembelian += $detail->harga_beli_produk * $detail->jumlah;
            }
        }

        $pendapatan = $total_penjualan - $total_pembelian;
        $data_pendapatan[] = $pendapatan;

        $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
    }

    $tanggal_awal = date('Y-m-01');

    if (auth()->user()->level == 1) {
        return view('admin.dashboard', compact(
            'kategori',
            'produk',
            'supplier',
            'tanggal_awal',
            'tanggal_akhir',
            'data_tanggal',
            'data_pendapatan',
            'minStok', // Kirim data produk dengan stok minimum
            'produkTerlaris',
            'produkTerlarisHarian'
        ));
    } else {
        return view('kasir.dashboard');
    }
}
}
