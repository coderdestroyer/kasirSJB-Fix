<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\CartPembelian;
use App\Models\PembelianDetail;
use Illuminate\Support\Facades\DB;

class PembelianDetailController extends Controller
{
    public function index()
    {
        $id_pembelian = session('id_pembelian');
        $id_supplier = session('id_supplier');
        $produk = Produk::with('detailProduk')->orderBy('nama_produk')->get();
        $supplier = Supplier::find(session('id_supplier'));

        if (! $supplier) {
            abort(404);
        }
        $cartItems = DB::table('cart_pembelian')->where('id_pembelian', $id_pembelian)->get();

        // return view('pembelian_detail.index', compact('id_pembelian', 'produk', 'supplier'));
        return view('pembelian_detail.index', compact('id_pembelian', 'produk', 'supplier', 'id_supplier', 'cartItems'));

    }

    public function data($id)
    {
        // Ambil data dari cart_pembelian berdasarkan id_pembelian
        $cartItems = DB::table('cart_pembelian')->where('id_pembelian', $id)->get();

        $data = array();
        $total = 0;
        $total_item = 0;

        // Daftar pilihan UOM
        $uomOptions = ['pieces', 'dus', 'roll'];

        foreach ($cartItems as $item) {
            $produk = Produk::where('kode_produk', $item->kode_produk)->first();

            if ($produk) {
                $uomDropdown = '<select class="form-control input-sm uom-select" data-id="' . $item->id_cart_pembelian . '">';
                foreach ($uomOptions as $option) {
                    $selected = $item->uom == $option ? 'selected' : '';
                    $uomDropdown .= '<option value="' . $option . '" ' . $selected . '>' . ucfirst($option) . '</option>';
                }
                $uomDropdown .= '</select>';

                $row = array();
                $row['nama_produk'] = $produk->nama_produk; 
                $row['harga_beli']  = 'Rp. ' . format_uang($produk->detailProduk->harga_beli_produk);  
                $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="' . $item->id_cart_pembelian . '" value="' . $item->jumlah . '">';
                $row['uom']         = $uomDropdown; 
                $row['subtotal']    = 'Rp. ' . format_uang($produk->detailProduk->harga_beli_produk * $item->jumlah);
                $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(' . route('pembelian_detail.destroy', $item->id_cart_pembelian) . ')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
                $data[] = $row;

                $total += $produk->detailProduk->harga_beli_produk * $item->jumlah;
                $total_item += $item->jumlah;
            }
        }

        $data[] = [
            'nama_produk' => '<div class="total hide">' . $total . '</div>
                        <div class="total_item hide">' . $total_item . '</div>',
            'harga_beli'  => '',
            'jumlah'      => '',
            'uom'         => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'nama_produk', 'jumlah', 'uom'])
            ->make(true);
    }


    public function store(Request $request)
    {
        $produk = Produk::where('kode_produk', $request->kode_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        // $detail = new PembelianDetail();
        // $detail->timestamps = false;
        // $detail->id_pembelian = $request->id_pembelian;
        // $detail->nama_produk = $produk->nama_produk;
        // $detail->harga_beli_produk = $produk->detailProduk->harga_beli_produk;
        // $detail->jumlah = 1;
        // $detail->save();

        $id_pembelian = session('id_pembelian');
        $id_supplier = session('id_supplier');

        // Memanggil stored procedure untuk menambah item ke dalam keranjang
        DB::select('CALL add_item_to_cart_pembelian(?, ?, ?)', [
            $id_pembelian, 
            $produk->kode_produk, 
            $id_supplier
        ]);
        // $cartItem = CartPembelian::where('id_pembelian', $id_pembelian)
        //     ->where('kode_produk', $produk->kode_produk)
        //     ->first();

        // if ($cartItem) {
        //     $cartItem->jumlah += 1; 
        //     $cartItem->save();
        // } else {
        //     CartPembelian::create([
        //         'id_supplier' => $id_supplier,
        //         'id_pembelian' => $id_pembelian,
        //         'kode_produk' => $produk->kode_produk,
        //         'jumlah' => 1, 
        //     ]);
        // }

        return response()->json('Data berhasil disimpan', 200);
    }


    // public function update(Request $request, $id)
    // {
    //     $detail = PembelianDetail::find($id);
    //     $detail->jumlah = $request->jumlah;
    //     $detail->update();
    // }

    public function update(Request $request, $id)
    {
        $cartItem = DB::table('cart_pembelian')->where('id_cart_pembelian', $id)->first();

        if (!$cartItem) {
            return response()->json('Data tidak ditemukan', 404);
        }

        DB::table('cart_pembelian')
        ->where('id_cart_pembelian', $id)
            ->update(['jumlah' => $request->jumlah]);

        return response()->json('Jumlah berhasil diupdate', 200);
    }

    public function updateUOM(Request $request, $id)
{
    // Cari data cart berdasarkan id
    $cartItem = DB::table('cart_pembelian')->where('id_cart_pembelian', $id)->first();

    if (!$cartItem) {
        return response()->json('Data tidak ditemukan', 404);
    }

    $validUOMs = ['pieces', 'dus', 'roll']; 
    if (!in_array($request->uom, $validUOMs)) {
        return response()->json('UOM tidak valid', 400);
    }

    // Konversi UOM ke jumlah stok
    $konversiUOM = [
        'pieces' => 1,
        'dus'    => 50,  // 1 dus = 50 pieces
        'roll'   => 100,  // 1 roll = 10 pieces
    ];

    // Ambil data produk
    $produk = Produk::where('kode_produk', $cartItem->kode_produk)->first();
    if (!$produk) {
        return response()->json('Produk tidak ditemukan', 404);
    }

    // Hitung jumlah baru dan subtotal
    $jumlahBaru = $konversiUOM[$request->uom];
    $hargaBaru = $produk->detailProduk->harga_beli_produk * $jumlahBaru;

    // Update di database
    DB::table('cart_pembelian')->where('id_cart_pembelian', $id)->update([
        'uom' => $request->uom,
        'jumlah' => $jumlahBaru,
    ]);

    return response()->json([
        'jumlah' => $jumlahBaru,
        'harga' => 'Rp. ' . format_uang($hargaBaru),
        'subtotal' => 'Rp. ' . format_uang($hargaBaru),
    ], 200);
}


    public function destroy($id)
    {
        // $detail = PembelianDetail::find($id);
        // $detail->delete();

        $cartItem = CartPembelian::find($id);


        $cartItem->delete();

        return response(null, 204);
    }

    public function loadForm($total)
    {
        $bayar = $total;
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah')
        ];

        return response()->json($data);
    }
}