<?php

namespace App\Http\Controllers;

// use App\Models\Member;
use App\Models\Cart;
use App\Models\Produk;
use App\Models\Setting;
use App\Models\Penjualan;
use App\Models\DetailProduk;
use Illuminate\Http\Request;
use App\Models\PenjualanDetail;
use Illuminate\Support\Facades\DB;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $produk = Produk::with('detailProduk')->orderBy('kode_produk')->get();

        $diskon = Setting::first()->diskon ?? 0;

        if ($nomor_invoice = session('nomor_invoice')) {
            // $penjualan = Penjualan::find($nomor_invoice);
            $cartItems = DB::table('cart')->where('id_user', auth()->id())->get();

            $nomor_invoice = session('nomor_invoice');
            $cartItems = DB::table('cart')->where('id_user', auth()->id())->get();
            // return view('penjualan_detail.index', compact('produk', 'diskon', 'nomor_invoice', 'penjualan'));
            return view('penjualan_detail.index', compact('produk', 'cartItems', 'diskon', 'nomor_invoice'));
        } else {
            if (auth()->user()->level == 1) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    // public function data($id)
    // {
    //     $totalHarga = DB::selectOne("SELECT total_harga(?) AS total_price", [$id]);

    //     $detail = PenjualanDetail::where('nomor_invoice', $id)
    //         ->get();

    //     $data = [];
    //     $total = 0;
    //     $total_item = 0;

    //     foreach ($detail as $item) {
    //         $row = [];
    //         $row['nama_produk'] = $item->nama_produk ?? 'Produk tidak ditemukan';
    //         $row['harga_jual']  = 'Rp. '. format_uang($item->harga_jual_produk ?? 0);
    //         $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_penjualan_detail .'" value="'. $item->jumlah .'">';
    //         $row['subtotal']    = 'Rp. '. format_uang($item->harga_jual_produk * $item->jumlah);
    //         $row['aksi']        = '<div class="btn-group">
    //                                 <button onclick="deleteData(`'. route('transaksi.destroy', $item->id_penjualan_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
    //                               </div>';
    //         $data[] = $row;

    //         $total_item += $item->jumlah;
    //     }

    //     $formattedTotal = number_format($totalHarga->total_price ?? 0, 2, ',', '.');

    //     $data[] = [
    //         'nama_produk' => '<div class="total hide">'. $totalHarga->total_price .'</div>
    //             <div class="total_item hide">'. $total_item .'</div>',
    //         'harga_jual'  => '',
    //         'jumlah'      => '',
    //         'subtotal'    => '<strong>Total: Rp. ' . $formattedTotal . '</strong>',
    //         'aksi'        => '',
    //     ];

    //     return datatables(collect($data))
    //         ->addIndexColumn()
    //         ->rawColumns(['aksi', 'nama_produk', 'jumlah'])
    //         ->make(true);
    // }

    public function data()
    {
        $user_id = auth()->id();  // Mengambil ID user yang sedang login

        // Memanggil stored function untuk menghitung total harga
        $total_harga = DB::select("SELECT calculate_total_harga(?) AS total_harga", [$user_id]);

        $cartItems = DB::table('cart')->where('id_user', $user_id)->get();
        $data = [];
        $total_item = 0;

        foreach ($cartItems as $item) {
            $produk = Produk::find($item->kode_produk);
            if ($produk) {
                $row = [];
                $row['nama_produk'] = $produk->nama_produk;
                $row['harga_jual']  = 'Rp. ' . format_uang($produk->harga_jual);
                $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="' . $item->id_cart . '" value="' . $item->jumlah . '">';
                $row['subtotal']    = 'Rp. ' . format_uang($produk->harga_jual * $item->jumlah);
                $row['aksi']        = '<div class="btn-group">
                                        <button onclick="deleteData(`' . route('transaksi.destroy', $item->id_cart) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                    </div>';
                $data[] = $row;

                $total_item += $item->jumlah;
            }
    }

            // Mengambil total harga dari stored function
            $formattedTotal = number_format($total_harga[0]->total_harga, 2, ',', '.');

            $data[] = [
                'nama_produk' => '<div class="total hide">' . $total_harga[0]->total_harga . '</div>
                <div class="total_item hide">' . $total_item . '</div>',
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
        // $produk = Produk::where('kode_produk', $request->kode_produk)->first();
        // if (! $produk) {
        //     return response()->json('Data gagal disimpan', 400);
        // }

        // $detail = new PenjualanDetail();
        // $detail->nomor_invoice = $request->id_penjualan;
        // $detail->nama_produk = $produk->nama_produk;
        // $detail->harga_jual_produk = $produk->harga_jual;
        // $detail->jumlah = 1;
        // $detail->save();

        // return response()->json('Data berhasil disimpan', 200);

        $produk = Produk::where('kode_produk', $request->kode_produk)->first();

        if (!$produk) {
            return response()->json('Produk tidak ditemukan', 404);
        }

        // Simpan produk ke tabel cart
        // $cart = new Cart();
        // $cart->id_user = auth()->id(); // id_user sesuai dengan user yang login
        // $cart->kode_produk = $produk->kode_produk;
        // $cart->jumlah = 1; // Jumlah awal, bisa diubah sesuai dengan permintaan user
        // $cart->save();
        DB::statement("CALL add_item_to_cart(?, ?, ?)", [
            auth()->id(),           // ID user yang login
            $produk->kode_produk,   // Kode produk yang dipilih
            1                       // Jumlah produk yang akan dimasukkan
        ]);


        return response()->json('Produk berhasil ditambahkan ke cart', 200);
    }

    public function selectproduk(Request $request)
    {
        return response()->json($request);
    }

    // public function update(Request $request, $id)
    // {
    //     $detail = PenjualanDetail::find($id);
    //     $detail->jumlah = $request->jumlah;
    //     $detail->update();
    // }


    public function update(Request $request, $id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->jumlah = $request->jumlah;
            $cart->update();
        }

        return response()->json('Data berhasil diperbarui', 200);
    }

    // public function destroy($id)
    // {
    //     $detail = PenjualanDetail::find($id);
    //     $detail->delete();

    //     return response(null, 204);
    // }
    public function destroy($id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->delete();
        }

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
            'terbilang' => ucwords(terbilang($bayar) . ' Rupiah'),
            'kembalirp' => $formattedKembalian,
            'kembali_terbilang' => ucwords(terbilang($kembali->kembalian ?? 0) . ' Rupiah'),
        ];

        return response()->json($data);
    }
}
