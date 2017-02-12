@extends($decorator->getLayout())

@push('styles')
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.list.css') }}"/>
@endpush

@push('scripts')
    <script src="{{ URL::asset('assets/panel-bootstrap/js/panel.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-bootstrap/js/panel.list.js') }}"></script>

    <script>
        $(function() {
            var panelList = new panel.list('#{{ $panelId = str_random() }}');
            panelList.init();
        });
    </script>
@endpush

@section('content')
    <div class="panel panel-list" id="{{ $panelId }}">
        <form method="post" action="{{ $decorator->getUrl() }}" class="panel-list-form">

            <h4>{{ $decorator->getLabel() }}</h4>

            <div class="panel-list-tools">
                <div class="row">
                    <div class="col-sm-9 m-b-xs">
                        @foreach ($decorator->getActions() as $action)
                            {!! $decorator->renderAction($action, '.panel-list-action') !!}
                        @endforeach

                        @foreach ($decorator->getGroupActions() as $actionKey => $action)
                            {!! $decorator->renderGroupAction($action, 'button.panel-list-group-action', [ 'attributes.type' => 'submit' ]) !!}
                        @endforeach

                        @if( count($decorator->getMoveTo()) > 0 )
                            <select class="panel-list-move-to-select input-sm form-control"
                                    data-confirm=""
                                    data-url="{{ urlbuilder($decorator->getUrl())->append('groupMoveTo/dummyMoveTo') }}">

                                <option value="">@lang('panel.labels.move-to')</option>

                                @foreach($decorator->getMoveTo() as $key => $label)
                                    <option value="{{$key}}">&nbsp;&nbsp;&nbsp;&nbsp;{{$label}}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    @if( count($decorator->getCategories()) > 0 )
                        <div class="col-sm-3">
                            <select class="panel-list-categories-select input-sm form-control input-s-sm inline"
                                    data-url="{{ urlbuilder($decorator->getUrl(['!pages', '!categories']))->query([
                                                    $decorator->getRequestAttributeName('category') => 'dummyCategory'
                                                ]) }}">
                                @foreach($decorator->getCategories() as $categoryKey=>$category)
                                    <option value="{{$categoryKey}}"
                                            @if($categoryKey == $decorator->category) selected @endif>{{$category}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <table class="table panel-list-table">
                @if( $decorator->isEmpty())
                    <tbody class="panel-list-empty">
                    <tr>
                        <td colspan="99" class="inactive">
                            @lang('panel.labels.list-empty')
                        </td>
                    </tr>
                    </tbody>
                @else
                    <thead>
                    <tr>
                        @if(count($decorator->getGroupActions()) > 0 || count($decorator->getMoveTo()) > 0)
                            <th class="panel-list-checkbox">
                                <input type="checkbox" />
                            </th>
                        @endif

                        @if($decorator->isSortEnabled())
                            <th></th>
                        @endif

                        @foreach ($decorator->getColumns() as $column)
                            <th>
                                {!! $decorator->renderColumnHead($column) !!}
                            </th>
                        @endforeach

                        <th colspan="99">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($decorator->getRows() as $rowKey => $row)
                        {!! html_tag_open('tr', $row) !!}

                        @if(count($decorator->getGroupActions()) > 0 || count($decorator->getMoveTo()) > 0)
                            <td class="panel-list-checkbox">
                                <input type="checkbox" name="group[]" value="{{$rowKey}}" />
                            </td>
                        @endif

                        @if($decorator->isSortEnabled())
                            <td class="panel-list-sort">
                                <div class="btn-group">
                                    <a href="{!! urlbuilder($decorator->getUrl())->append(['sortUp', $row['model']->getKey()]) !!}"
                                       class="btn btn-default btn-xs"
                                       data-toggle="tooltip"
                                       data-placement="auto"
                                       title="@lang('panel.labels.sort-up')"><i class="fa fa-angle-up"></i></a>
                                    <a href="{!! urlbuilder($decorator->getUrl())->append(['sortDown', $row['model']->getKey()]) !!}"
                                       class="btn btn-default btn-xs"
                                       data-toggle="tooltip"
                                       data-placement="auto"
                                       title="@lang('panel.labels.sort-down')"><i class="fa fa-angle-down"></i></a>
                                </div>
                            </td>
                        @endif


                        @foreach ($decorator->getColumns() as $column)
                            {!! html_tag('td.panel-list-data-cell', $column, array_get($row, "cells.{$column['key']}")) !!}
                        @endforeach

                        @foreach ($decorator->getModelActions($row['model']) as $action)
                            <td class="panel-list-model-action">
                                {!! $decorator->renderModelAction($action, $row['model']) !!}
                            </td>
                        @endforeach
                        {!! html_tag_close('tr') !!}
                    @endforeach
                    </tbody>
                @endif
            </table>

            @if($decorator->getPaginator())
                <div class="pull-right">
                    {!! $decorator->getPaginator()->render() !!}
                </div>
            @endif

            {{ csrf_field() }}
        </form>
    </div>
@endsection