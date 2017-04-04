@if(!$form->fields()->isEmpty())
    <div class="ibox panel-list-filter @if(!$form->isSubmit()) collapsed @endif">
        <div class="ibox-title">
            <div class="ibox-tools">
                <h5><a class="collapse-link">@lang($form->config('labels.filter'))</a></h5>

                <a class="collapse-link">
                    <i class="fa fa-chevron-down"></i>
                </a>
            </div>
        </div>
        <form method="{{ $form->method() }}" action="{{ $form->submitUrl() }}"
              class="ibox-content m-b-sm border-bottom">
            @foreach (collect($form->fields()->resolved())->chunk(3) as $chunk)
                <div class="row">

                    @foreach ($chunk as $field)
                        <div class="col-sm-4">
                            @include($form->fieldView($field), array_merge([
                                'form'  => $form,
                                'field' => $field
                            ], $field))
                        </div>
                    @endforeach

                </div>
            @endforeach

            <div class="row text-right">
                <div class="col-md-12">
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
@endif