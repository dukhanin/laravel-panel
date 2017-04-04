@extends($form->config('layout'))

@include('panel::panel-inspinia.init')

@section('content')
    <div class="wrapper wrapper-content wrapper-panel animated fadeInRight">
        <div class="ibox float-e-margins panel-form">
            @if ( $form->label() )
                <div class="ibox-title">
                    <h5>{{ $form->label() }}</h5>
                </div>
            @endif

            <div class="ibox-content">
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

                    <div class="hr-line-dashed"></div>

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
        </div>
    </div>
@endsection