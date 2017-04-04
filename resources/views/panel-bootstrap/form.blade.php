@extends($form->config('layout'))

@include('panel::panel-bootstrap.init')

@section('content')
    <div class="panel panel-form">
        @if($form->label())
            <h4>{{ $form->label() }}</h4>
        @endif

        <form method="{{ $form->method() }}" action="{{ $form->submitUrl() }}" class="form-horizontal">

            @if (count($form->errors()) > 0)
                <div class="form-group">
                    <div class="col-sm-10 text-danger">
                        <i class="fa fa-warning"></i> @lang( $form->config('labels.validation-failed') )
                    </div>
                </div>
            @endif

            @foreach ($form->fields()->resolved() as $field)
                @include($form->fieldView($field), array_merge([
                    'form'  => $form,
                    'field' => $field
                ], $field))
            @endforeach

            <div class="form-group">
                <div class="col-lg-10 text-right">
                    @foreach ($form->buttons()->resolved() as $buttonKey => $button)
                        @if ($button['type'] == 'submit')
                            @continue
                        @endif

                        {!! html_tag('a.btn', $button ) !!}
                    @endforeach


                    @foreach ($form->buttons()->resolved() as $buttonKey => $button)
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