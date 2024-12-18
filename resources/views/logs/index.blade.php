@extends('layouts.master')

@section('title')
    Daftar Logs
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Daftar Logs</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Log Id</th>
                            <th>Log Time</th>
                            <th>Log Target</th>
                            <th>Log Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Initialize DataTable
        $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: '{{ route('logs.data') }}',
            columns: [
                { data: 'log_id' },
                { data: 'log_time' },
                { data: 'log_target' },
                { data: 'log_description' }
            ]
        });
    });
</script>
@endpush
    