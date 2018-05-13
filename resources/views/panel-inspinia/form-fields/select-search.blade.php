@php
    $errors = $form->fieldErrors($field['key']);
    $value = $form->inputValue($field['key']);
    $options = $options ?? [];

    if($value instanceof \Illuminate\Database\Eloquent\Model) {
        $valueText = array_get($options, $value->getKey()) ?? $value->getAttribute('name') ?? $value->getKey();
        $value = $value->getKey();
    } else {
        $value = strval($value);
        $valueText = array_get($options, $value) ?? $value;
    }
@endphp

<div class="panel-file form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        <div class="row">
            <div class="col-sm-8">
                <select class="form-control" id="{{ $id = 'select-ajax-'.mt_rand(1, 1000) }}"
                        name="{{$form->htmlInputName($field['key'])}}">

                    @if($value)
                        <option value="{{ $value }}" selected>{{ $valueText }}</option>
                    @endif
                </select>
            </div>
            <div class="col-sm-4">
                <a href="#" id="{{ $id }}-reset" class="btn btn-sm btn-default"> <i class="fa fa-times"></i>
                    Сбросить</a>
            </div>
        </div>

        @if ( ! empty( $errors ) )
            <div class="error-text">
                @foreach($errors as $error)
                    <span class="help-block m-b-none">
                        {{ $error }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    $(function () {
        var select = $('#{{ $id }}'),
            reset = $('#{{ $id }}-reset');

        select.select2({
            ajax: {
                url: '{{ $url ?? '' }}',
                dataType: 'json',
            },
            language: '{{ $language ?? app()->getLocale() }}',
            minimumInputLength: {{ $minimumInputLength ?? 0 }},
            placeholder: '{{ $placeholder ?? 'Выбрать...' }}'
        });

        reset.click(function (e) {
            select.val(null).trigger('change');

            e.preventDefault();
        });
    });
</script>
@endpush