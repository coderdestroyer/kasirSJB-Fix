@extends('layouts.master')

@section('title')
    Daftar Pembelian
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pembelian</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-header with-border">
                    <button onclick="addForm()" class="btn btn-success btn-xs btn-flat"><i class="fa fa-plus-circle"></i>
                        Transaksi Baru</button>
                    @empty(!session('id_pembelian'))
                        <a href="{{ route('pembelian_detail.index') }}" class="btn btn-info btn-xs btn-flat"><i
                                class="fa fa-pencil"></i> Transaksi Aktif</a>
                    @endempty
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-stiped table-bordered table-pembelian">
                        <thead>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @includeIf('pembelian.supplier')
    @includeIf('pembelian.edit')
    @includeIf('pembelian.detail')
@endsection

@push('scripts')
    <script>
        let table, table1;

        $(function() {
            table = $('.table-pembelian').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('pembelian.data') }}',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'tanggal'
                    },
                    {
                        data: 'supplier'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'aksi',
                        searchable: false,
                        sortable: false
                    },
                ]
            });

            $('.table-supplier').DataTable();
            table1 = $('.table-detail').DataTable({
                processing: true,
                bSort: false,
                dom: 'Brt',
                columns: [{
                        data: 'DT_RowIndex',
                        searchable: false,
                        sortable: false
                    },
                    {
                        data: 'nama_produk'
                    },
                    {
                        data: 'harga_beli'
                    },
                    {
                        data: 'jumlah'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'subtotal'
                    },
                ]
            })
        });

        function addForm() {
            $('#modal-supplier').modal('show');
        }

        function showDetail(url) {
            $('#modal-detail').modal('show');

            table1.ajax.url(url);
            table1.ajax.reload();
        }

        function editData(url) {
            $.get(url, function(data) {
                $('#modal-edit').modal('show');
                $('#id_pembelian').val(data.pembelian.id_pembelian);
                $('#jumlah_sudah_dibayar').val('Rp. ' + data.pembelian.jumlah_bayar);
                let totalHarga = 0;
                data.pembelianDetail.forEach(function(item) {
                    totalHarga += item.harga_beli_produk * item.jumlah;
                });
                let jumlahKurang = totalHarga - data.pembelian.jumlah_bayar;
                $('#total_harga').val('Rp. ' + totalHarga);
                $('#jumlah_kurang').val('Rp. ' + jumlahKurang);
            });
        }

        $('#form-edit-pembayaran').submit(function(event) {
            event.preventDefault(); 

            let idPembelian = $('#id_pembelian').val();
            let jumlahBayar = $('#jumlah_bayar').val();

            $.ajax({
                url: `/pembelian/${idPembelian}/update-payment`, 
                method: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'), 
                    jumlah_bayar: jumlahBayar,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Pembayaran berhasil diperbarui');
                        $('#modal-edit').modal('hide');
                        location.reload();
                    } else {
                        alert('Gagal memperbarui pembayaran');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan saat mengirim data');
                }
            });
        });






        function deleteData(url) {
            if (confirm('Yakin ingin menghapus data terpilih?')) {
                $.post(url, {
                        '_token': $('[name=csrf-token]').attr('content'),
                        '_method': 'delete'
                    })
                    .done((response) => {
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menghapus data');
                        return;
                    });
            }
        }
    </script>
@endpush
