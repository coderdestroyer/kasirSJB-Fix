<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Kecil</title>

    <style>
        * {
            font-family: "consolas", sans-serif;
        }
        p {
            display: block;
            margin: 3px;
            font-size: 10pt;
        }
        table td {
            font-size: 9pt;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            @page {
                margin: 0;
                size: 75mm 140mm; /* Ukuran kertas diperlebar agar cukup untuk semua konten */
            }
            html, body {
                width: 75mm;
                height: 140mm; /* Sesuaikan tinggi agar seluruh konten dapat muat */
                margin: 0;
                padding: 0;
                overflow: hidden; /* Mencegah konten terpotong */
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="btn-print" style="position: absolute; right: 1rem; top: 1rem;" onclick="window.print()">Print</button>
    <div class="text-center">
        <h3 style="margin-bottom: 5px;">{{ strtoupper($setting->nama_perusahaan) }}</h3>
        <p>{{ strtoupper($setting->alamat) }}</p>
    </div>
    <br>
    <div>
        <p style="float: left;">{{ date('d-m-Y') }}</p>
        <p style="float: right">{{ strtoupper(auth()->user()->name) }}</p>
    </div>
    <div style="clear: both;"></div>
    <p>No: {{ tambah_nol_didepan($penjualan->nomor_invoice, 10) }}</p>
    <p class="text-center">===================================</p>

    <table width="100%" style="border: 0;">
    @foreach ($detail as $item)
        @if ($item->produk)
        <tr>
            <td colspan="3">{{ $item->produk->nama_produk }}</td>
        </tr>
        <tr>
            <td>{{ $item->jumlah }} x {{ format_uang($item->harga_jual_produk) }}</td>
            <td></td>
            <td class="text-right">{{ format_uang($item->jumlah * $item->harga_jual_produk) }}</td>
        </tr>
        @else
        <tr>
            <td colspan="3">Produk tidak ditemukan</td>
        </tr>
        @endif
    @endforeach
    </table>

    <p class="text-center">-----------------------------------</p>
    <table width="100%" style="border: 0;">
        <tr>
            <td>Total Harga:</td>
            <td class="text-right">{{ format_uang($penjualan->total_harga) }}</td>
        </tr>
        <tr>
            <td>Total Item:</td>
            <td class="text-right">{{ $penjualan->total_item }}</td>
        </tr>
        <tr>
            <td>Total Bayar:</td>
            <td class="text-right">{{ format_uang($penjualan->bayar) }}</td>
        </tr>
        <tr>
            <td>Diterima:</td>
            <td class="text-right">{{ format_uang($penjualan->diterima) }}</td>
        </tr>
    </table>

    <p class="text-center">===================================</p>
    <p class="text-center">-- TERIMA KASIH --</p>

    <script>
        let body = document.body;
        let html = document.documentElement;
        let height = Math.max(
            body.scrollHeight, body.offsetHeight,
            html.clientHeight, html.scrollHeight, html.offsetHeight
        );

        document.cookie = "innerHeight=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "innerHeight=" + ((height + 50) * 0.264583);
    </script>
</body>
</html>
