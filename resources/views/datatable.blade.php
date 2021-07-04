@extends('layouts.backend')

@section('css_before')
    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables/buttons-bs4/buttons.bootstrap4.min.css') }}">

   
@endsection

@section('js_after')
<script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/buttons/buttons.print.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/buttons/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/buttons/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/buttons/buttons.colVis.min.js') }}"></script>
<script>
        $(document).ready(function(){
            var fields = @json($fields);
            var columns = [];
            for (let index = 0; index < fields.length; index++) {
                const element = fields[index];
                columns.push({
                    data: element
                });
            }
            // DataTable
            $('.js-dataTable-buttons').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 20, 50], [10, 20, 50]],
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ url('/'.$tab_en.'/get-datasets/'.$datasets) }}",
                columns: columns,
                buttons: [
                    { extend: 'copy', className: 'btn btn-sm btn-primary' },
                    { extend: 'csv', className: 'btn btn-sm btn-primary' },
                    { extend: 'print', className: 'btn btn-sm btn-primary' },
                    {
                        text: 'Reload data',
                        className: 'btn btn-sm btn-primary reload-data'
                    }
                ],
                dom: "<'row'<'col-sm-12'<'text-center bg-body-light py-2 mb-2'B>>>" +
                    "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
        });
        $(document).on('click', '.reload-data', function(){
            Dashmix.loader('show', 'bg-gd-dusk');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ url('/'.$tab_en.'/reload-data/'.$datasets) }}",
                method: 'post',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(result){
                    Dashmix.loader('hide');
                    window.location="/'.$tab_en.'/datasets/{{$datasets}}";
                },
                error: function(err) {
                    console.log(err);
                    Dashmix.loader('hide');
                    window.location="/'.$tab_en.'/datasets/{{$datasets}}";
                }
            });
        });
    </script>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill font-size-h2 font-w400 mt-2 mb-0 mb-sm-2">{{ $table_name }}</h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">{{ $tab_name }}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $table_name }}</li>
                    </ol>
                </nav>
            </div>
       </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <!-- Dynamic Table with Export Buttons -->
        <div class="block block-rounded block-bordered">
            <div class="block-header block-header-default">
                <h3 class="block-title"><a href="{{ $link }}" target="_blank">link here</a></h3>
            </div>
            <div class="block-content block-content-full">
                <!-- DataTables init on table by adding .js-dataTable-buttons class, functionality is initialized in js/pages/tables_datatables.js -->
                <table class="table table-bordered table-striped table-vcenter table-responsive js-dataTable-buttons">
                    <thead>
                        <tr>
                            @foreach($fields as $key => $field)
                                <th class="text-center" style="width: 80px;">{{$field}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END Dynamic Table with Export Buttons -->
    </div>
    <!-- END Page Content -->
@endsection