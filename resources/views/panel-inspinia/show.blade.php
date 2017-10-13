@extends($show->config('layout'))

@include('panel::panel-inspinia.init')

@push('scripts')
<script>
    $(function () {
        var panelShow = new panel.show('#{{ $panelId = str_random() }}');
        panelShow.init();
    });
</script>
@endpush

@section('content')
    <div class="wrapper wrapper-content wrapper-panel animated fadeInRight">
        <div id="{{ $panelId }}" class="ibox float-e-margins panel-show panel-{{ kebab_case( class_basename($show) ) }}">
            @if ( $show->label() )
                <div class="ibox-title">
                    <h5>{{ $show->label() }}</h5>
                </div>
            @endif

            <div class="ibox-content">
                <fieldset class="form-horizontal">

                    @if( $show->fields()->count() > 6 )
                        <div class="form-group panel-buttons">
                            <div class="col-lg-12 text-right">
                                @foreach ($show->buttons()->resolved() as $buttonKey => $button)
                                    @if (!in_array($button['key'], ['cancel', 'back']))
                                        @continue
                                    @endif

                                    {!! html_tag('a.btn', $button, [
                                    'class' => 'pull-left'
                                    ] ) !!}
                                @endforeach

                                @foreach ($show->buttons()->resolved() as $buttonKey => $button)
                                    @if (in_array($button['key'], ['cancel', 'back']))
                                        @continue
                                    @endif

                                    {!! html_tag('button.btn', $button, [
                                        'type' => array_get($button, 'type'),
                                        'name' => array_get($button, 'name'),
                                        'value' => 1
                                    ] ) !!}
                                @endforeach
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                    @endif

                    @foreach ($show->fields()->resolved() as $field)
                        @include($show->fieldView($field), array_merge([
                            'showing'  => $show,
                            'field' => $field
                        ], $field))

                        <div class="hr-line-dashed"></div>

                    @endforeach

                    <div class="form-group">
                        <div class="col-lg-12 text-right panel-buttons">
                            @foreach ($show->buttons()->resolved() as $buttonKey => $button)
                                @if (!in_array($button['key'], ['cancel', 'back']))
                                    @continue
                                @endif

                                {!! html_tag('a.btn', $button, [
                                'class' => 'pull-left'
                                ] ) !!}
                            @endforeach

                            @foreach ($show->buttons()->resolved() as $buttonKey => $button)
                                @if (in_array($button['key'], ['cancel', 'back']))
                                    @continue
                                @endif

                                {!! html_tag('button.btn', $button, [
                                    'type' => array_get($button, 'type'),
                                    'name' => array_get($button, 'name'),
                                    'value' => 1
                                ] ) !!}
                            @endforeach
                        </div>
                    </div>

                    {{ csrf_field() }}

                </fieldset>
            </div>
        </div>
    </div>
@endsection

{{ $show->pushAssets() }}