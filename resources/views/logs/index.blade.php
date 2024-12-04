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
            <div class="box-header with-border">
            </div>
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
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->log_id }}</td>
                            <td>{{ $log->log_time }}</td>
                            <td>{{ $log->log_target }}</td>
                            <td>{{ $log->log_description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div class="pagination">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
