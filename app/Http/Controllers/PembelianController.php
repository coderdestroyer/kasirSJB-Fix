<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\DetailProduk;
use Illuminate\Http\Request;
use App\Models\CartPembelian;
use App\Models\PembelianDetail;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index()
    {
        $supplier = Supplier::orderBy('nama')->get();

        return view('pembelian.index', compact('supplier'));
    }

    public function data()
    {
        $pembelian = Pembelian::has('detilPembelian')->whereNotNull('updated_at')->orderBy('id_pembelian', 'desc')->get();
        $detilpembelian = PembelianDetail::orderBy('id_pembelian', 'desc')->get();

        return datatables()
            ->of($pembelian)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })
            ->addColumn('supplier', function ($pembelian) {
                return $pembelian->supplier->nama;
            })
            ->addColumn('status', function ($pembelian) {
                $belumLunas = $pembelian->detilPembelian->contains('status', 'belum lunas');
                if ($belumLunas) {
                    return 'Belum Lunas';
                } else {
                    return 'Lunas';
                }
            })
            ->addColumn('aksi', function ($pembelian) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`' . route('pembelian.show', $pembelian->id_pembelian) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                     <button onclick="editData(`' . route('pembelian.edit', $pembelian->id_pembelian) . '`)" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(`' . route('pembelian.destroy', $pembelian->id_pembelian) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
    public function edit($id)
    {

        $pembelian = Pembelian::findOrFail($id);
        $pembelianDetail = PembelianDetail::where('id_pembelian', $id)->get();

        return response()->json([
            'pembelian' => $pembelian,
            'pembelianDetail' => $pembelianDetail
        ]);
    }


    public function create($id)
    {
        // $pembelian = new Pembelian();
        // $pembelian->id_supplier = $id;
        // $pembelian->tanggal_pembelian = now();
        // $pembelian->save();

        // session(['id_pembelian' => $pembelian->id_pembelian]);
        // session(['id_supplier' => $pembelian->id_supplier]);

        $lastData = Pembelian::orderBy('id_pembelian', 'desc')->first();
        $id_pembelian = $lastData ? $lastData->id_pembelian + 1 : 1;

        DB::table('cart_pembelian')->where('id_pembelian', $id_pembelian)->delete();
        session(['id_pembelian' => $id_pembelian]);
        session(['id_supplier' => $id]);
        return redirect()->route('pembelian_detail.index');
    }

    public function updatePayment(Request $request, $id)
    {
        // Ambil data pembelian berdasarkan ID
        $pembelian = Pembelian::findOrFail($id);

        $jumlahBayar = $request->input('jumlah_bayar');
        $pembelian->jumlah_bayar += $jumlahBayar;
        $pembelian->save();

        $totalHutang = 0;
        $pembelianDetail = PembelianDetail::where('id_pembelian', $id)->get();

        foreach ($pembelianDetail as $detail) {
            $totalHutang += $detail->harga_beli_produk * $detail->jumlah;
        }

        $statusPembayaran = ($pembelian->jumlah_bayar >= $totalHutang) ? 'lunas' : 'belum lunas';

        if ($pembelian->jumlah_bayar >= $totalHutang) {
            PembelianDetail::where('id_pembelian', $id)
                ->update(['status' => 'lunas']);
        } else {
            PembelianDetail::where('id_pembelian', $id)
                ->update(['status' => 'belum lunas']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil diperbarui',
            'jumlah_bayar' => $pembelian->jumlah_bayar,
            'status_pembayaran' => $statusPembayaran
        ]);
    }


    public function store(Request $request)
    {
        // $pembelian = Pembelian::findOrFail($request->id_pembelian);
        // $pembelian->update();

        // $detail = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        // foreach ($detail as $item) {
        //     $produk = Produk::where('nama_produk', $item->nama_produk)->first();
        //     $produk->detailProduk->stok_produk += $item->jumlah;
        //     $produk->detailProduk->save();
        //     $item->touch();
        // }

        $id_pembelian  = $request->id_pembelian;
        $id_supplier  = $request->id_supplier;
        $bayar = $request->bayarrp;

        // Ambil data item dari cart_pembelian
        $cartItems = DB::table('cart_pembelian')->where('id_pembelian', $id_pembelian)->get();

        // Membuat pembelian baru
        $pembelian = new Pembelian();
        $pembelian->id_supplier = $id_supplier;
        $pembelian->jumlah_bayar = $bayar;
        $pembelian->tanggal_pembelian = now();
        $pembelian->save();
        $id_pembelian_last = $pembelian->id_pembelian;
        $totalHutang = 0;

        foreach ($cartItems as $item) {
            $produk = Produk::find($item->kode_produk);

            if ($produk) {
                // Membuat detail pembelian
                $pembelianDetail = new PembelianDetail();
                $pembelianDetail->id_pembelian = $id_pembelian_last;
                $pembelianDetail->nama_produk = $produk->nama_produk;
                $pembelianDetail->harga_beli_produk = $produk->detailProduk->harga_beli_produk;
                $pembelianDetail->jumlah = $item->jumlah;
                $pembelianDetail->uom_beli = $item->uom;

                $jumlahProduk = $item->jumlah;

                if ($item->uom == 'dus') {
                    $jumlahProduk *= 50;
                } elseif ($item->uom == 'roll') {
                    $jumlahProduk *= 100;
                }

                $pembelianDetail->konversi_uom = $jumlahProduk;
                $pembelianDetail->save();

                $detailProduk = DetailProduk::where('kode_produk', $item->kode_produk)->first();
                if ($detailProduk) {
                    $detailProduk->stok_produk += $jumlahProduk;
                    $detailProduk->update();
                }

                $totalHutang += $pembelianDetail->harga_beli_produk * $jumlahProduk;
            }
        }

        $status = ($bayar < $totalHutang) ? 'belum lunas' : 'lunas';

        PembelianDetail::where('id_pembelian', $id_pembelian_last)
            ->update(['status' => $status]);

        CartPembelian::where('id_pembelian', $request->id_pembelian)->delete();
        session()->forget('id_pembelian');
        session()->forget('id_supplier');

        return redirect()->route('pembelian.index');
    }

    public function show($id)
    {
        $detail = DB::table('view_pembelian_detail')
            ->where('id_pembelian', $id)
            ->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('nama_produk', function ($detail) {
                return $detail->nama_produk;
            })
            ->addColumn('harga_beli', function ($detail) {
                return 'Rp. ' . format_uang($detail->harga_beli_produk);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('status', function ($detail) {
                return $detail->status;
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. ' . format_uang($detail->subtotal);
            })
            ->make(true);
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);
        $detail    = PembelianDetail::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::with('detailProduk')->find($item->id_produk);
            if ($produk) {
                $produk->detailProduk->stok -= $item->jumlah;
                $produk->update();
            }
            $item->delete();
        }

        $pembelian->delete();

        return response(null, 204);
    }
}
