@extends($panel->config('layout'))

@push('styles')
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.list.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.tree.css') }}"/>
@endpush

@push('scripts')
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery.multisortable.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.list.js') }}"></script>

    <script>
        $(function () {
            var panelList = new panel.list('#{{ $panelId = str_random() }}');
            panelList.init();
        });
    </script>
@endpush

@section('content')
    <div class="panel-list panel-tree" id="{{ $panelId }}">
        <form method="post" action="{{ $panel->url() }}" class="panel-list-form">

            <div class="mail-box-header">
                <h2>
                    {{ $panel->label() }}
                </h2>

                <div class="panel-list-tools mail-tools m-t-md">
                    <div class="row">
                        <div class="col-sm-9 m-b-xs">
                            @foreach ($panel->actions() as $action)
                                {!! $panel->renderAction($action, '.panel-list-action') !!}
                            @endforeach

                            @foreach ($panel->groupActions() as $actionKey => $action)
                                {!! $panel->renderGroupAction($action, 'button.panel-list-group-action', [ 'type' => 'submit' ]) !!}
                            @endforeach

                            @if( count($panel->moveToOptions()) > 0 )
                                <select class="panel-list-move-to-select input-sm form-control"
                                        confirm="@lang( $panel->config('confirm.move-to') )"
                                        url="{{ urlbuilder($panel->getUrl())->append('groupMoveTo/dummyMoveTo') }}">

                                    <option value="">@lang( $panel->config('labels.move-to') )</option>

                                    @foreach($panel->moveToOptions() as $key => $label)
                                        <option value="{{$key}}">&nbsp;&nbsp;&nbsp;&nbsp;{{$label}}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        @if( count($panel->categories()) > 0 )
                            <div class="col-sm-3">
                                <select class="panel-list-categories-select input-sm form-control input-s-sm inline"
                                        url="{{ urlbuilder($panel->url(['!pages', '!categories']))->query([
                                                        'category' => 'dummyCategory'
                                                    ]) }}">
                                    @foreach($panel->getCategories() as $categoryKey=>$category)
                                        <option value="{{$categoryKey}}"
                                                @if($categoryKey == $panel->category) selected @endif>{{$category}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

            <div class="mail-box">

                <table class="table table-hover table-mail panel-list-table">

                    @if( $panel->isEmpty())
                        <tbody class="panel-list-empty">
                        <tr>
                            <td colspan="99">
                                <h2>@lang( $panel->config('labels.list-empty') )</h2>
                            </td>
                        </tr>
                        </tbody>
                    @else
                        <thead>
                        <tr>
                            @if(0 && $panel->isSortEnabled())
                                <th class="panel-list-sort-handler">&nbsp;</th>
                            @endif

                            @if(count($panel->groupActions()) > 0 || count($panel->moveToOptions()) > 0)
                                <th class="check-mail panel-list-checkbox">
                                    <div class="checkbox">
                                        <input type="checkbox" />
                                        <label></label>
                                    </div>
                                </th>
                            @endif

                            @if($panel->isSortEnabled())
                                <th></th>
                            @endif

                            @foreach ($panel->columns() as $column)
                                <th>
                                    {!! $panel->renderColumnHead($column) !!}
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($panel->rows() as $row)
                            {!! html_tag_open('tr', array_except($row, ['cells', 'model']), ['key' => $row['model']->getKey()]) !!}

                            @if(0 && $panel->isSortEnabled())
                                <td class="panel-list-sort-handler">
                                    &nbsp;
                                </td>
                            @endif

                            @if(count($panel->groupActions()) > 0 || count($panel->moveToOptions()) > 0)
                                <td class="check-mail panel-list-checkbox">
                                    <div class="checkbox">
                                        <input type="checkbox" name="group[]" value="{{$row['model']->getKey()}}"/>
                                        <label></label>
                                    </div>
                                </td>
                            @endif

                            @if($panel->isSortEnabled())
                                <td class="panel-list-sort">
                                    <div class="btn-group">
                                        <a href="{!! urlbuilder($panel->getUrl())->append(['sortUp', $row['model']->getKey()]) !!}"
                                           class="btn btn-xs btn-white"
                                           data-toggle="tooltip"
                                           data-placement="auto"
                                           title="@lang( $panel->config('labels.sort-up') )"><i class="fa fa-angle-up"></i></a>
                                        <a href="{!! urlbuilder($panel->getUrl())->append(['sortDown', $row['model']->getKey()]) !!}"
                                           class="btn btn-xs btn-white"
                                           data-toggle="tooltip"
                                           data-placement="auto"
                                           title="@lang( $panel->config('labels.sort-down') )"><i class="fa fa-angle-down"></i></a>
                                    </div>
                                </td>
                            @endif


                            @foreach ($panel->columns() as $column)
                                {!! html_tag('td.mail-subject.panel-list-data-cell', array_except($column, 'label'), [ 'label' => false ], array_get($row, "cells.{$column['key']}")) !!}
                            @endforeach

                            @foreach ($panel->modelActions()->resolvedForModel($row['model']) as $action)
                                <td class="panel-list-model-action">
                                    {!! $panel->renderModelAction($action, $row['model']) !!}
                                </td>
                            @endforeach
                            {!! html_tag_close('tr') !!}
                        @endforeach
                        </tbody>
                    @endif
                </table>

                @if($panel->paginator())
                    <div class="pull-right">
                        {!! $panel->paginator()->render() !!}
                    </div>
                @endif

            </div>

            {{ csrf_field() }}
        </form>
    </div>
@endsection