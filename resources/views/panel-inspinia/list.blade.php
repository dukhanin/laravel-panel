@extends($panel->config('layout'))

@include('panel::panel-inspinia.init')

@push('scripts')
<script>
    $(function () {
        var panelList = new panel.list('#{{ $panelId = str_random() }}');
        panelList.init();
    });
</script>
@endpush

@section('content')

    <div class="wrapper wrapper-content wrapper-panel animated fadeInRight">
        @if($panel->filter())
            {!! $panel->filter()->view() !!}
        @endif

        <div class="panel-list" id="{{ $panelId }}">
            <form method="post" action="{{ $panel->url() }}" class="panel-list-form">

                <div class="mail-box-header">
                    <h2>
                        {{ $panel->label() }}
                    </h2>

                    <div class="panel-list-tools mail-tools m-t-md">
                        <div class="row">
                            <div class="col-sm-9 m-b-xs">

                                @foreach ($panel->actions()->resolved() as $action)
                                    {!! $panel->renderAction($action, '.panel-list-action') !!}
                                @endforeach

                                @foreach ($panel->groupActions()->resolved() as $actionKey => $action)
                                    {!! $panel->renderGroupAction($action, 'button.panel-list-group-action', [ 'type' => 'submit' ]) !!}
                                @endforeach

                                @if( count($panel->moveToOptions()) > 0 )
                                    <select class="panel-list-move-to-select input-sm form-control"
                                            confirm="@lang( $panel->config('confirm.move-to') )"
                                            url="{!! $panel->urlTo('groupMoveTo', 'dummyMoveTo') !!}">

                                        <option value="">@lang( $panel->config('labels.move-to') )</option>

                                        @foreach($panel->moveToOptions() as $key => $label)
                                            <option value="{{$key}}">
                                                &nbsp;&nbsp;&nbsp;&nbsp;{{preg_replace('/\s{2}/', '&nbsp;&nbsp;', $label)}}</option>
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
                                                    @if($categoryKey == $panel->category()) selected @endif>{{preg_replace('/\s{2}/', '&nbsp;&nbsp;', $category)}}</option>
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
                                @if($panel->isSortEnabled())
                                    <th class="panel-list-sort-handler">&nbsp;</th>
                                @endif

                                @if(count($panel->groupActions()) > 0 || count($panel->moveToOptions()) > 0)
                                    <th class="check-mail panel-list-checkbox">
                                        <div class="checkbox">
                                            <input type="checkbox"/>
                                            <label></label>
                                        </div>
                                    </th>
                                @endif

                                @if($panel->isSortEnabled())
                                    <th></th>
                                @endif

                                @foreach ($panel->columns()->resolved() as $key => $column)
                                    <th {!! html_tag_attr( array_only($column, ['width', 'class', 'style'])) !!}>
                                        {!! $panel->renderColumnHead($column) !!}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($panel->rows() as $row)
                                {!! html_tag_open('tr', array_except($row, ['cells', 'model']), ['key' => $row['model']->getKey()]) !!}

                                @if($panel->isSortEnabled())
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
                                            <a href="{!! $panel->urlTo('sortUp', $row['model']) !!}"
                                               class="btn btn-xs btn-white"
                                               data-toggle="tooltip"
                                               data-placement="auto"
                                               title="@lang( $panel->config('labels.sort-up') )"><i
                                                        class="fa fa-angle-up"></i></a>
                                            <a href="{!! $panel->urlTo('sortDown', $row['model']) !!}"
                                               class="btn btn-xs btn-white"
                                               data-toggle="tooltip"
                                               data-placement="auto"
                                               title="@lang( $panel->config('labels.sort-down') )"><i
                                                        class="fa fa-angle-down"></i></a>
                                        </div>
                                    </td>
                                @endif


                                @foreach ($panel->columns()->resolved() as $column)
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
                </div>

                @if($panel->paginator())
                    <div class="text-right">
                        {!! $panel->paginator()->render() !!}
                    </div>
                @endif

                {{ csrf_field() }}
            </form>
        </div>
    </div>
@endsection