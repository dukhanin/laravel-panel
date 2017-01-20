@extends($decorator->getLayout())

@push('styles')
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.tree.css') }}"/>
@endpush

@push('scripts')
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery.multisortable.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.list.js') }}"></script>

    <script>
        $(function () {
            var panelList = new panel.list('.panel-list');
            panelList.init();

            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
        });
    </script>
@endpush

@section('content')
    <div class="panel-list panel-tree">
        <form method="post" action="{{ $decorator->getUrl() }}" class="panel-list-form">

            <div class="mail-box-header">
                <h2>
                    {{ $decorator->getLabel() }}
                </h2>

                <div class="panel-list-tools mail-tools m-t-md">
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

            </div>

            <div class="mail-box">

                <table class="table table-hover table-mail panel-list-table">

                    @if( $decorator->isEmpty())
                        <tbody class="panel-list-empty">
                        <tr>
                            <td colspan="99">
                                <h2>@lang('panel.labels.list-empty')</h2>
                            </td>
                        </tr>
                        </tbody>
                    @else
                        <thead>
                        <tr>
                            @if(0 && $decorator->isSortEnabled())
                                <th class="panel-list-sort-handler">&nbsp;</th>
                            @endif

                            @if(count($decorator->getGroupActions()) > 0 || count($decorator->getMoveTo()) > 0)
                                <th class="check-mail panel-list-checkbox">
                                    <input type="checkbox" class="i-checks"/>
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
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($decorator->getRows() as $rowKey => $row)
                            {!! html_tag_open('tr', array_except($row, ['cells', 'model']), ['key' => $rowKey]) !!}

                            @if(0 && $decorator->isSortEnabled())
                                <td class="panel-list-sort-handler">
                                    &nbsp;
                                </td>
                            @endif

                            @if(count($decorator->getGroupActions()) > 0 || count($decorator->getMoveTo()) > 0)
                                <td class="check-mail panel-list-checkbox">
                                    <input type="checkbox" class="i-checks" name="group[]" value="{{$rowKey}}"/>
                                </td>
                            @endif

                            @if($decorator->isSortEnabled())
                                <td class="panel-list-sort">
                                    <div class="btn-group">
                                        <a href="{!! urlbuilder($decorator->getUrl())->append(['sortUp', $row['model']->getKey()]) !!}"
                                           class="btn btn-xs btn-white"
                                           data-toggle="tooltip"
                                           data-placement="auto"
                                           title="@lang('panel.labels.sort-up')"><i class="fa fa-angle-up"></i></a>
                                        <a href="{!! urlbuilder($decorator->getUrl())->append(['sortDown', $row['model']->getKey()]) !!}"
                                           class="btn btn-xs btn-white"
                                           data-toggle="tooltip"
                                           data-placement="auto"
                                           title="@lang('panel.labels.sort-down')"><i class="fa fa-angle-down"></i></a>
                                    </div>
                                </td>
                            @endif


                            @foreach ($decorator->getColumns() as $column)
                                {!! html_tag('td.mail-subject.panel-list-data-cell', $column, [ 'label' => false ], array_get($row, "cells.{$column['key']}")) !!}
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

            </div>

            {{ csrf_field() }}
        </form>
    </div>
@endsection