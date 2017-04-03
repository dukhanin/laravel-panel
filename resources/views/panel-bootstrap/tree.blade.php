@extends($panel->config('layout'))

@include('panel::panel-bootstrap.init')

@push('scripts')
<script>
    $(function () {
        var panelTree = new panel.tree('#{{ $panelId = str_random() }}');
        panelTree.init();
    });
</script>
@endpush


@section('content')
    <div class="panel-list panel-tree" id="{{ $panelId }}">
        <form method="post" action="{{ $panel->url() }}" class="panel-list-form">

            <h4>{{ $panel->label() }}</h4>

            <div class="panel-list-tools">
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
                                    url="{!! $panel->urlTo('groupMoveTo', 'dummyMoveTo') !!}">

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
                                    url="{!! $panel->urlTo('showList', ['category' => 'dummyCategory'], ['!page', '!category']) !!}">
                                @foreach($panel->categories() as $categoryKey=>$category)
                                    <option value="{{$categoryKey}}"
                                            @if($categoryKey == $panel->category()) selected @endif>{{$category}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <table class="table panel-list-table">
                @if( $panel->isEmpty())
                    <tbody class="panel-list-empty">
                    <tr>
                        <td colspan="99" class="inactive">
                            @lang( $panel->config('labels.list-empty') )
                        </td>
                    </tr>
                    </tbody>
                @else
                    <thead>
                    <tr>
                        @if(count($panel->groupActions()) > 0 || count($panel->moveToOptions()) > 0)
                            <th class="panel-list-checkbox">
                                <input type="checkbox" />
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

                        <th colspan="99">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($panel->rows() as $row)
                        {!! html_tag_open('tr', $row) !!}

                        @if(count($panel->groupActions()) > 0 || count($panel->moveToOptions()) > 0)
                            <td class="panel-list-checkbox">
                                <input type="checkbox" name="group[]" value="{{$row['model']->getKey()}}" />
                            </td>
                        @endif

                        @if($panel->isSortEnabled())
                            <td class="sort">
                                <div class="btn-group">
                                    <a href="{!! $panel->urlTo('sortUp', $row['model']) !!}"
                                       class="btn btn-xs"
                                       data-toggle="tooltip"
                                       data-placement="auto"
                                       title="@lang( $panel->config('labels.sort-up') )"><i class="fa fa-angle-up"></i></a>
                                    <a href="{!! $panel->urlTo('sortDown', $row['model']) !!}"
                                       class="btn btn-xs"
                                       data-toggle="tooltip"
                                       data-placement="auto"
                                       title="@lang( $panel->config('labels.sort-down') )"><i class="fa fa-angle-down"></i></a>
                                </div>
                            </td>
                        @endif


                        @foreach ($panel->columns() as $column)
                            {!! html_tag('td.panel-list-data-cell', array_except($column, 'label'), array_get($row, "cells.{$column['key']}")) !!}
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

            {{ csrf_field() }}
        </form>
    </div>
@endsection