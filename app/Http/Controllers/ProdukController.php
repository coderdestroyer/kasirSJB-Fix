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
    // Validasi input dari pengguna
    $validatedData = $request->validate([
        'nama_produk' => 'required|string|max:255',
        'harga_jual' => 'required|numeric|min:0',
        'id_kategori' => 'required|exists:kategori,id_kategori',
        'stok_produk' => 'required|integer|min:0',
        'merk' => 'nullable|string|max:255',
        'harga_beli_produk' => 'required|numeric|min:0',
    ]);

    try {
        DB::statement('CALL store_produk(?, ?, ?, ?, ?, ?)', [
            $validatedData['nama_produk'],
            $validatedData['harga_jual'],
            $validatedData['id_kategori'],
            $validatedData['stok_produk'],
            $validatedData['merk'] ?? null,
            $validatedData['harga_beli_produk'],
        ]);

        // Kembalikan respons berhasil
        return response()->json(['message' => 'Data berhasil disimpan'], 200);
    } catch (\Exception $e) {
        // Tangani exception jika ada error
        return response()->json([
            'message' => 'Terjadi kesalahan saat menyimpan data',
            'error' => $e->getMessage()
        ], 500);
    }
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
        // Validasi input data
    $request->validate([
        'nama_produk' => 'required|string|max:255',
        'id_kategori' => 'required|integer',
        'harga_jual' => 'required|numeric',
        'stok_produk' => 'required|integer',
        'merk' => 'required|string|max:100',
        'harga_beli_produk' => 'required|numeric',
    ]);

    // Cari produk berdasarkan kode_produk
    $produk = Produk::where('kode_produk', $id)->first();
    if (!$produk) {
        return response()->json(['message' => 'Produk not found'], 404);
    }

    // Panggil prosedur tersimpan
    try {
        DB::statement('CALL update_produk(?, ?, ?, ?, ?, ?, ?)', [
            $produk->kode_produk, // Ambil id_produk dari produk
            $request->input('nama_produk'),
            $request->input('id_kategori'),
            $request->input('harga_beli_produk'),
            $request->input('harga_jual'),
            $request->input('merk'),
            $request->input('stok_produk'),
        ]);

        return response()->json(['message' => 'Data berhasil diperbarui'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating data', 'error' => $e->getMessage()], 500);
    }      
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
