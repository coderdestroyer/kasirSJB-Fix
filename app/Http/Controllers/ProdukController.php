<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\DetailProduk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Contracts\Support\ValidatedData;
use PDF;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

        return view('produk.index', compact('kategori'));
    }

    public function data()
    {
        $produk = Produk::join('detail_produk', 'produk.kode_produk', '=', 'detail_produk.kode_produk')
        ->leftJoin('kategori', 'kategori.id_kategori', '=', 'produk.id_kategori')
        ->select(
            'produk.kode_produk',
            'produk.nama_produk',
            'produk.harga_jual',
            'detail_produk.stok_produk',
            'detail_produk.merk',
            'detail_produk.harga_beli_produk',
            'kategori.nama_kategori'
        )->get();

        return datatables()
        ->of($produk)
        ->addIndexColumn()
        ->addColumn('select_all', function ($produk) {
            return '
                <input type="checkbox" name="kode_produk[]" value="'. $produk->kode_produk .'">
            ';
        })
        ->addColumn('kode_produk', function ($produk) {
            return '<span class="label label-success">PRD00'. $produk->kode_produk .'</span>';
        })
        ->addColumn('harga_beli', function ($produk) {
            return format_uang($produk->harga_beli_produk);
        })
        ->addColumn('harga_jual', function ($produk) {
            return format_uang($produk->harga_jual);
        })
        ->addColumn('stok', function ($produk) {
            return format_uang($produk->stok_produk);
        })
        ->addColumn('aksi', function ($produk) {
            return '
            <div class="btn-group">
                <button type="button" onclick="editForm(`'. route('produk.update', $produk->kode_produk) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button type="button" onclick="deleteData(`'. route('produk.destroy', $produk->kode_produk) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
            </div>
            ';
        })
        ->rawColumns(['aksi', 'kode_produk', 'select_all'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // @dd($request->all());
        $validatedData = $request->validate([
            'nama_produk' => 'required',
            'harga_jual' => 'required|numeric',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'stok_produk' => 'required|numeric',
            'merk' => 'nullable|string',
            'harga_beli_produk' => 'required|numeric',
        ]);
    
        DB::statement('CALL store_produk(?, ?, ?, ?, ?, ?)', [
            $validatedData['nama_produk'],
            $validatedData['harga_jual'],
            $validatedData['id_kategori'],
            $validatedData['stok_produk'],
            $validatedData['merk'] ?? null,
            $validatedData['harga_beli_produk']
        ]);
    
        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk = Produk::where('kode_produk', $id)->first();
        $detailproduk = DetailProduk::where('kode_produk', $id)->first();
    
        foreach ($detailproduk->getAttributes() as $key => $value) {
            $produk->setAttribute($key, $value);
        }
    
        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::where('kode_produk', $id)->first();
        if (!$produk) {
            return response()->json(['message' => 'Produk not found'], 404);
        }

        $detailProduk = DetailProduk::where('kode_produk', $id)->first();
        if (!$detailProduk) {
            return response()->json(['message' => 'Detail Produk not found'], 404);
        }

        $produk->update($request->only(['nama_produk', 'harga_jual', 'id_kategori']));

        $detailProduk->update($request->only(['stok_produk', 'merk', 'harga_beli_produk']));

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::where('kode_produk', $id)->first();
        $produk->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            $produk = Produk::where('kode_produk', $id)->first();
            $produk->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->kode_produk as $id) {
            $produk = Produk::where('kode_produk', $id)->first();
            $dataproduk[] = $produk;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }
}