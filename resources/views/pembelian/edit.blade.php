<!-- Modal Edit Pembayaran -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="modal-edit">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit Pembayaran</h4>
            </div>
            <div class="modal-body">
                <!-- Informasi Pembayaran -->
                <div class="form-group">
                    <label for="jumlah_sudah_dibayar">Jumlah Pembayaran yang Sudah Dibayar</label>
                    <input type="hidden" class="form-control" id="id_pembelian" readonly>
                    <input type="text" class="form-control" id="jumlah_sudah_dibayar" readonly>
                </div>

                <div class="form-group">
                    <label for="total_harga">Total Jumlah yang Harus Dibayar</label>
                    <input type="text" class="form-control" id="total_harga" readonly>
                </div>

                <div class="form-group">
                    <label for="jumlah_kurang">Jumlah yang Kurang</label>
                    <input type="text" class="form-control" id="jumlah_kurang" readonly>
                </div>

                <form id="form-edit-pembayaran" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="jumlah_bayar">Jumlah Pembayaran</label>
                        <input type="number" class="form-control" name="jumlah_bayar" id="jumlah_bayar" min="0" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
