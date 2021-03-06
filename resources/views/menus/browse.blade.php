@extends('iwan::master')

@section('page_title', __('iwan::generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="iwan-list-add"></i> {{ $dataType->display_name_plural }}
        @can('add',app($dataType->model_name))
            <a href="{{ route('iwan.'.$dataType->slug.'.create') }}" class="btn btn-success">
                <i class="iwan-plus"></i> {{ __('iwan::generic.add_new') }}
            </a>
        @endcan
    </h1>
@stop

@section('content')
    @include('iwan::menus.partial.notice')

    <div class="page-content container-fluid">
        @include('iwan::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table id="dataTable" class="table table-hover">
                            <thead>
                            <tr>
                                @foreach($dataType->browseRows as $rows)
                                <th>{{ $rows->display_name }}</th>
                                @endforeach
                                <th class="actions text-right">{{ __('iwan::generic.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($dataTypeContent as $data)
                                <tr>
                                    @foreach($dataType->browseRows as $row)
                                    <td>
                                        @if($row->type == 'image')
                                            <img src="@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Iwan::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                                        @else
                                            {{ $data->{$row->field} }}
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="no-sort no-click bread-actions">
                                        @can('delete', $data)
                                            <div class="btn btn-sm btn-danger pull-right delete" data-id="{{ $data->{$data->getKeyName()} }}">
                                                <i class="iwan-trash"></i> {{ __('iwan::generic.delete') }}
                                            </div>
                                        @endcan
                                        @can('edit', $data)
                                            <a href="{{ route('iwan.'.$dataType->slug.'.edit', $data->{$data->getKeyName()}) }}" class="btn btn-sm btn-primary pull-right edit">
                                                <i class="iwan-edit"></i> {{ __('iwan::generic.edit') }}
                                            </a>
                                        @endcan
                                        @can('edit', $data)
                                            <a href="{{ route('iwan.'.$dataType->slug.'.builder', $data->{$data->getKeyName()}) }}" class="btn btn-sm btn-success pull-right">
                                                <i class="iwan-list"></i> {{ __('iwan::generic.builder') }}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('iwan::generic.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="iwan-trash"></i> {{ __('iwan::generic.delete_question') }} {{ $dataType->display_name_singular }}?
                    </h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field("DELETE") }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('iwan::generic.delete_this_confirm') }} {{ $dataType->display_name_singular }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('iwan::generic.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <!-- DataTables -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable({
                "order": [],
                "language": {!! json_encode(__('iwan::datatable'), true) !!},
                "columnDefs": [{"targets": -1, "searchable":  false, "orderable": false}]
                @if(config('dashboard.data_tables.responsive')), responsive: true @endif
            });
        });

        $('td').on('click', '.delete', function (e) {
            $('#delete_form')[0].action = '{{ route('iwan.'.$dataType->slug.'.destroy', ['menu' => '__menu']) }}'.replace('__menu', $(this).data('id'));

            $('#delete_modal').modal('show');
        });
    </script>
@stop
