@extends($form->config('layout'))

@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.form.css') }}"/>
@endpush

@section('content')
    <div class="ibox float-e-margins panel-form">
        @if ( $form->label() )
            <div class="ibox-title">
                <h5>{{ $form->label() }}</h5>
            </div>
        @endif

        <div class="ibox-content">
            <form method="{{ $form->method() }}" action="{{ $form->submitUrl() }}" class="form-horizontal">

                @if ($form->isFailure())
                    <div class="form-group">
                        <div class="col-sm-10 text-danger">
                            <i class="fa fa-warning"></i> @lang( $form->config('labels.validation-failed') )
                        </div>
                    </div>
                @endif


                @foreach ($form->fields() as $field)
                    @include($form->fieldView($field), array_merge([
                        'form'  => $form,
                        'field' => $field
                    ], $field))
                @endforeach

                <div class="hr-line-dashed"></div>

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
    </div>
@endsection