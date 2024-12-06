<?php

namespace App\Http\Controllers;

// use App\Models\Member;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\DetailProduk;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $produk = Produk::with('detailProduk')->orderBy('kode_produk')->get();
        
        $diskon = Setting::first()->diskon ?? 0;

        if ($nomor_invoice = session('nomor_invoice')) {
            $penjualan = Penjualan::find($nomor_invoice);

            return view('penjualan_detail.index', compact('produk', 'diskon', 'nomor_invoice', 'penjualan'));
        } else {
            if (auth()->user()->level == 1) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $totalHarga = DB::selectOne("SELECT total_harga(?) AS total_price", [$id]);

        $detail = PenjualanDetail::where('nomor_invoice', $id)
            ->get();
    
        $data = [];
        $total = 0;
        $total_item = 0;
    
        foreach ($detail as $item) {
            $row = [];
            $row['nama_produk'] = $item->nama_produk ?? 'Produk tidak ditemukan';
            $row['harga_jual']  = 'Rp. '. format_uang($item->harga_jual_produk ?? 0);
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_penjualan_detail .'" value="'. $item->jumlah .'">';
            $row['subtotal']    = 'Rp. '. format_uang($item->harga_jual_produk * $item->jumlah);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('transaksi.destroy', $item->id_penjualan_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                  </div>';
            $data[] = $row;

            $total_item += $item->jumlah;
        }

        $formattedTotal = number_format($totalHarga->total_price ?? 0, 2, ',', '.');
    
        $data[] = [
            'nama_produk' => '<div class="total hide">'. $totalHarga->total_price .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'harga_jual'  => '',
            'jumlah'      => '',
            'subtotal'    => '<strong>Total: Rp. ' . $formattedTotal . '</strong>',
            'aksi'        => '',
        ];
    
        return datatables(collect($data))
            ->addIndexColumn()
            ->rawColumns(['aksi', 'nama_produk', 'jumlah'])
            ->make(true);
    }
    
    public function store(Request $request)
    {
        $produk = Produk::where('kode_produk', $request->kode_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PenjualanDetail();
        $detail->nomor_invoice = $request->id_penjualan;
        $detail->nama_produk = $produk->nama_produk;
        $detail->harga_jual_produk = $produk->harga_jual;
        $detail->jumlah = 1;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function selectproduk(Request $request)
    {
        return response()->json($request);
    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon = 0, $total = 0, $diterima = 0)
    {
        $bayar   = $total - ($diskon / 100 * $total);
        $kembali = DB::selectOne("SELECT hitung_kembalian(?, ?) AS kembalian", [$bayar, $diterima]);
        $formattedKembalian = number_format($kembali->kembalian ?? 0, 2, ',', '.');
        $data    = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah'),
            'kembalirp' => 'Rp. ' . $formattedKembalian,
            'kembali_terbilang' => ucwords(terbilang($kembali->kembalian ?? 0). ' Rupiah'),
        ];

        return response()->json($data);
    }
}
