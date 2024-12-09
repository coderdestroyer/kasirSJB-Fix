<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode</title>
    <style>
        .text-center {
            text-align: center;
        }
        td {
            border: 1px solid #333;
            padding: 10px;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            @foreach ($dataproduk as $produk)
                <div style="display: inline-block; text-align: center; margin: 10px; border: 1px solid #000; padding: 10px;">
                    <p>{{ $produk->nama_produk }} - Rp. {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    <p>
                    {!! (new Picqer\Barcode\BarcodeGeneratorHTML())->getBarcode($produk->kode_produk, Picqer\Barcode\BarcodeGeneratorHTML::TYPE_CODE_128) !!}
                    </p>
                    <p>{{ $produk->kode_produk }}</p>
                </div>
            @endforeach
        </tr>
    </table>
</body>
</html>
