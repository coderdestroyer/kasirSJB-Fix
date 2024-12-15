<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getDataPembelian($awal, $akhir)
    {
        $no = 1;
        $data = [];
        $totalSeluruhnya = 0;
        $pembelian = Pembelian::whereBetween('tanggal_pembelian', [$awal, $akhir])
            ->withSum('detilPembelian', 'harga_beli_produk')
            ->get();

        foreach ($pembelian as $item) {
            if ($item->detil_pembelian_sum_harga_beli_produk > 0) {
                $row = [];
                // Cek apakah created_at tidak null sebelum format
                $row['DT_RowIndex'] = $no++;
                $row['tanggal'] = $item->created_at ? tanggal_indonesia($item->created_at->format('Y-m-d'), false) : 'Tanggal tidak tersedia';
                $row['pembelian'] = 'Rp ' . format_uang($item->detil_pembelian_sum_harga_beli_produk);
                $data[] = $row;
                $totalSeluruhnya += $item->detil_pembelian_sum_harga_beli_produk;
            }
        }
        $data[] = [
            'DT_RowIndex' => '-',
            'tanggal' => 'Total',
            'pembelian' => 'Rp ' . format_uang($totalSeluruhnya),
        ];

        return $data;
    }

    public function getDataPenjualan($awal, $akhir)
    {
        $no = 1;
        $data = [];
        $totalSeluruhnya = 0;

        $penjualan = Penjualan::whereBetween('tanggal_penjualan', [$awal, $akhir])
            ->withSum('detailPenjualan', 'harga_jual_produk')
            ->get();

        foreach ($penjualan as $item) {
            if ($item->detail_penjualan_sum_harga_jual_produk > 0) {
                $row = [];
                // Cek apakah created_at tidak null sebelum format
                $row['DT_RowIndex'] = 'INV-0' . $item->nomor_invoice;
                $row['tanggal'] = $item->created_at ? tanggal_indonesia($item->created_at->format('Y-m-d'), false) : 'Tanggal tidak tersedia';
                $row['penjualan'] = 'Rp ' . format_uang($item->detail_penjualan_sum_harga_jual_produk);
                $totalSeluruhnya += $item->detail_penjualan_sum_harga_jual_produk;

                $data[] = $row;
            }
        }
        $data[] = [
            'DT_RowIndex' => '-',
            'tanggal' => 'Total',
            'penjualan' => 'Rp ' . format_uang($totalSeluruhnya),
        ];

        return $data;
    }

    public function dataPembelian($awal, $akhir)
    {
        $data = $this->getDataPembelian($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function dataPenjualan($awal, $akhir)
    {
        $data = $this->getDataPenjualan($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir)
    {
        // Ambil data pembelian dan penjualan
        $dataPembelian = $this->getDataPembelian($awal, $akhir);
        $dataPenjualan = $this->getDataPenjualan($awal, $akhir);

        // Kirim kedua data ke view
        $pdf = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'dataPembelian', 'dataPenjualan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Pendapatan-' . date('Y-m-d-His') . '.pdf');
    }
}