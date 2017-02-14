@extends($form->config('layout'))

@push('styles')
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.form.css') }}"/>
@endpush

@push
<script>
    $(function(){

    })
</script>
@endpush

@section('content')
    <div class="panel panel-form">
        @if($form->label())
            <h4>{{ $form->label() }}</h4>
        @endif

        <form method="{{ $form->method() }}" action="{{ $form->submitUrl() }}" class="form-horizontal">

            @if ($form->isFailure())
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-danger">
                        <i class="fa fa-warning"></i> @lang( $panel->config('labels.validation-failed') )
                    </div>
                </div>
            @endif

            @foreach ($form->fields() as $field)
                @include($form->fieldView($field), array_merge([
                    'form'  => $form,
                    'field' => $field
                ], $field))
            @endforeach

            <div class="form-group">
                <div class="col-lg-10 text-right">
                    @foreach ($form->buttons() as $buttonKey => $button)
                        @if ($button['type'] == 'submit')
                            @continue
                        @endif

                        {!! html_tag('a.btn', $button ) !!}
                    @endforeach


                    @foreach ($form->buttons() as $buttonKey => $button)
                        @if ($button['type'] != 'submit')
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

        </form>
    </div>
@endsection