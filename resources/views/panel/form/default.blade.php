@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/css/panel.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/css/panel.form.css') }}"/>
@endpush

<div class="ibox float-e-margins du-form">
    @if ( ! empty( $title ))
        <div class="ibox-title">
            <h5>{{ $title }}</h5>
        </div>
    @endif

    <div class="ibox-content">
        <form method="{{ $form->getMethod() }}" action="{{ $form->getSubmitUrl() }}" class="form-horizontal">

            @if ($form->isFailure())
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-danger">
                        <i class="fa fa-warning"></i> @lang('panel.labels.validation-failed')
                    </div>
                </div>
            @endif


            @foreach ($form->getFields() as $field)
                @include($form->getFieldView($field), array_merge([
                    'form'  => $form,
                    'field' => $field
                ], $field))
            @endforeach

            <div class="hr-line-dashed"></div>

            <div class="form-group">
                <div class="col-lg-10 text-right">
                    @foreach ($form->getButtons() as $buttonKey => $button)
                        @if ($button['type'] == 'submit')
                            @continue
                        @endif

                        {!! html_tag('a.btn', $button ) !!}
                    @endforeach


                    @foreach ($form->getButtons() as $buttonKey => $button)
                        @if ($button['type'] != 'submit')
                            @continue
                        @endif

                        {!! html_tag('button.btn', $button, [
                            'attributes.type' => array_get($button, 'type'),
                            'attributes.name' => array_get($button, 'name'),
                            'attributes.value' => 1
                        ] ) !!}
                    @endforeach
                </div>
            </div>

            {{ csrf_field() }}

        </form>
    </div>
</div>