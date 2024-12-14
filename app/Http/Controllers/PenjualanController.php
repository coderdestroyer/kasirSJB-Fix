<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\DetailProduk;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::with('detailPenjualan')
        ->whereNotNull('created_at')
        ->whereNotNull('updated_at')
        ->orderBy('nomor_invoice','desc')
        ->has('detailPenjualan')
        ->get()
        ->map(function($penjualan){
            $penjualan->total_item = $penjualan->detailPenjualan->sum('jumlah');
            $penjualan->total_harga = $penjualan->detailPenjualan->sum(function ($detail){
                return $detail->jumlah * $detail->harga_jual_produk;
            });
        return $penjualan;
        });
        

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail('. route('penjualan.show', $penjualan->nomor_invoice) .')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData('. route('penjualan.destroy', $penjualan->nomor_invoice) .')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->timestamps = false;
        $penjualan->id_user = auth()->id();
        $penjualan->id_kasir = auth()->id();
        $penjualan->tanggal_penjualan = now(); 
        $penjualan->save();

        session(['nomor_invoice' => $penjualan->nomor_invoice]);
        return redirect()->route('transaksi.index');
    }

    // public function store(Request $request)
    // {
    //     $penjualan = Penjualan::findOrFail($request->id_penjualan);
    //     $penjualan->created_at = now();
    //     $penjualan->updated_at = now();
    //     $penjualan->update();

    //     $detail = PenjualanDetail::where('nomor_invoice', $penjualan->id_penjualan)->get();
    //     foreach ($detail as $item) {
    //         $item->update();
    //         $detailProduk = DetailProduk::where('kode_produk', $item->kode_produk)->first();
    //         $detailProduk->stok_produk -= $item->jumlah;
    //         $detailProduk->update();
    //     }

    //     return redirect()->route('transaksi.selesai');
    // }
     public function store(Request $request)
    {
        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->created_at = now();
        $penjualan->updated_at = now();
        $penjualan->update();

        $detail = PenjualanDetail::where('nomor_invoice', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $item->update();
            $detailProduk = DetailProduk::where('kode_produk', $item->kode_produk)->first();
            $detailProduk->stok_produk -= $item->jumlah;
            $detailProduk->update();
        }

        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
    {   
        $penjualan = Penjualan::find($id);
        $detail = PenjualanDetail::where('nomor_invoice', $id)->get();

         return datatables()
             ->of($detail)
             ->addIndexColumn()
             ->addColumn('nama_produk', function ($detail) {
                 return $detail->nama_produk;
             })
             ->addColumn('harga_jual', function ($detail) {
                 return 'Rp. '. format_uang($detail->harga_jual_produk);
             })
             ->addColumn('jumlah', function ($detail) {
                 return format_uang($detail->jumlah);
             })
             ->addColumn('subtotal', function ($detail) {
                return format_uang($detail->jumlah * $detail->harga_jual_produk);
            })
             ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('nomor_invoice', $penjualan->nomor_invoice)->get();
        foreach ($detail as $item) {
            $produk = Produk::with('detailProduk')->find($item->kode_produk);
            if ($produk) {
                $produk->detailProduk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    // public function notaKecil()
    // {
    //     $setting = Setting::first();
    //     $penjualan = Penjualan::find(session('id_penjualan'));
    //     if (! $penjualan) {
    //         abort(404);
    //     }
    //     $detail = PenjualanDetail::with('produk')
    //         ->where('id_penjualan', session('id_penjualan'))
    //         ->get();
        
    //     return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    // }
    public function notaKecil(Request $request)
{
    $setting = Setting::first();
    $penjualan = Penjualan::with('detailPenjualan')->find(session('nomor_invoice'));

    if (!$penjualan) {
        abort(404);
    }

    // Hitung ulang total harga dan total item
    $penjualan->total_item = $penjualan->detailPenjualan->sum('jumlah');
    $penjualan->total_harga = $penjualan->detailPenjualan->sum(function ($detail) {
        return $detail->jumlah * $detail->harga_jual_produk;
    });

    // Atur default jika diskon tidak ada
    $penjualan->diskon = $penjualan->diskon ?? 0;

    // Hitung total bayar
    $penjualan->bayar = $penjualan->total_harga - $penjualan->diskon;

    // Gunakan nilai diterima dari request atau default ke nilai bayar
    $penjualan->diterima = $request->input('diterima', $penjualan->bayar);

    // Hitung kembalian
    $penjualan->kembali = max(0, $penjualan->diterima - $penjualan->bayar);

    // Ambil detail produk
    $detail = PenjualanDetail::with('produk')
        ->where('nomor_invoice', session('nomor_invoice'))
        ->get();
        

    return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
}




public function notaBesar()
{
    $setting = Setting::first();
    $penjualan = Penjualan::with('detailPenjualan')->find(session('nomor_invoice'));

    if (! $penjualan) {
        abort(404, 'Data penjualan tidak ditemukan.');
    }

    // Hitung ulang total harga dan total item untuk memastikan konsistensi
    $penjualan->total_item = $penjualan->detailPenjualan->sum('jumlah');
    $penjualan->total_harga = $penjualan->detailPenjualan->sum(function ($detail) {
        return $detail->jumlah * $detail->harga_jual_produk;
    });

    // Atur nilai default jika tidak ada
    $penjualan->diskon = $penjualan->diskon ?? 0;
    $penjualan->bayar = $penjualan->total_harga - $penjualan->diskon;
    $penjualan->diterima = $penjualan->diterima ?? $penjualan->bayar;
    $penjualan->kembali = max(0, $penjualan->diterima - $penjualan->bayar);

    // Ambil detail produk
    $detail = PenjualanDetail::with('produk')
        ->where('nomor_invoice', session('nomor_invoice'))
        ->get();

    $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
    $pdf->setPaper([0, 0, 609, 440], 'portrait');
    return $pdf->stream('Transaksi-'. date('Y-m-d-His') .'.pdf');
}


}