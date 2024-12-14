<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota PDF</title>

    <style>
        table td {
            font-size: 14px;
        }
        table.data td,
        table.data th {
            border: 1px solid #ccc;
            padding: 5px;
        }
        table.data {
            border-collapse: collapse;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <h3>{{ strtoupper($setting->nama_perusahaan) }}</h3>
        <p>{{ $setting->alamat }}</p>
    </div>

    <table width="100%">
        <tr>
            <td>Tanggal</td>
            <td>: {{ tanggal_indonesia($penjualan->tanggal_penjualan) }}</td>
        </tr>
        <tr>
            <td>Nomor Invoice</td>
            <td>: {{ tambah_nol_didepan($penjualan->nomor_invoice, 10) }}</td>
        </tr>
    </table>

    <br>
    <table class="data" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail as $key => $item)
                <tr>
                    <td class="text-center">{{ $key+1 }}</td>
                    <td>{{ $item->produk->nama_produk ?? 'Produk tidak ditemukan' }}</td>
                    <td class="text-right">{{ format_uang($item->harga_jual_produk) }}</td>
                    <td class="text-right">{{ $item->jumlah }}</td>
                    <td class="text-right">{{ format_uang($item->jumlah * $item->harga_jual_produk) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><b>Total Harga</b></td>
                <td class="text-right"><b>{{ format_uang($penjualan->total_harga) }}</b></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><b>Total Bayar</b></td>
                <td class="text-right"><b>{{ format_uang($penjualan->bayar) }}</b></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><b>Diterima</b></td>
                <td class="text-right"><b>{{ format_uang($penjualan->diterima) }}</b></td>
            </tr>
        </tfoot>
    </table>

    <div class="text-center">
        <p>-- Terima Kasih Telah Berbelanja --</p>
        <p>Kasir: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
