@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $kategori }}</h3>
                <p>Total Kategori</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
            <a href="{{ route('kategori.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $produk }}</h3>
                <p>Total Produk</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
            <a href="{{ route('produk.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $supplier }}</h3>
                <p>Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <a href="{{ route('supplier.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Grafik Pendapatan {{ tanggal_indonesia($tanggal_awal, false) }} s/d {{ tanggal_indonesia($tanggal_akhir, false) }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <canvas id="salesChart" style="height: 180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card for Best Selling Products -->
<div class="row">
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Produk Terlaris</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Total Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produkTerlaris as $produk)
                            <tr>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->total_terjual }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card for Low Stock Products -->
<div class="col-lg-4 col-md-6 col-xs-12">
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">Produk dengan Stok di Bawah Minimum</h3>
        </div>
        <div class="box-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok</th>
                        <th>Stok Minimum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($minStok as $produk)
                        <tr>
                            <td>{{ $produk->nama_produk }}</td>
                            <td>{{ $produk->stok_produk }}</td>
                            <td>{{ $produk->min_stok }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div> <!-- Close 'box' div -->
</div>


    <!-- Card for Today's Best Selling Products -->
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Produk Terlaris Hari Ini</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Merk</th>
                            <th>Harga Jual</th>
                            <th>Total Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produkTerlarisHarian as $produk)
                            <tr>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->merk }}</td>
                                <td>Rp{{ number_format($produk->harga_jual_produk, 0, ',', '.') }}</td>
                                <td>{{ $produk->total_terjual }}</td>
                                <td>Rp{{ number_format($produk->total_pendapatan, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                <div class="pagination-wrapper text-center">
                </div>
            </div>
        </div>
    </div>
</div>




@endsection

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-2/bower_components/chart.js/Chart.js') }}"></script>
<script>
$(function() {
    // Get context with jQuery - using jQuery's .get() method.
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d');
    // This will get the first returned node in the jQuery collection.
    var salesChart = new Chart(salesChartCanvas);

    var salesChartData = {
        labels: {{ json_encode($data_tanggal) }},
        datasets: [
            {
                label: 'Pendapatan',
                fillColor           : 'rgba(60,141,188,0.9)',
                strokeColor         : 'rgba(60,141,188,0.8)',
                pointColor          : '#3b8bba',
                pointStrokeColor    : 'rgba(60,141,188,1)',
                pointHighlightFill  : '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: {{ json_encode($data_pendapatan) }}
            }
        ]
    };

    var salesChartOptions = {
        pointDot : false,
        responsive : true
    };

    salesChart.Line(salesChartData, salesChartOptions);
});
</script>
@endpush