@php
    $errors = $form->fieldErrors($field['key']);
    $id = 'select-ajax-'.mt_rand(1, 1000);
    $values = $form->inputValue($field['key']) ?? [];
    $values = is_scalar($values) ? (array)$values : $values;
    $options = ($options ?? []);
@endphp

<div id="{{ $id }}" class="panel-file form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        <div class="selects-container">

        </div>

        <a href="#" class="btn btn-sm btn-default select-add"> <i
                    class="{{ $iconAdd ?? 'fa fa-plus' }}"></i> {{ $titleAdd ?? 'Добавить еще' }}</a>

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
        var index = 0,
            container = $('#{{ $id }} .selects-container'),
            add = $('#{{ $id }} .select-add');

        function addSelect(value) {
            var id = '{{ $id }}-' + (++index);

            container.append('<div class="m-b-sm row">' +
                '<div class="col-sm-8">' +
                '<select class="form-control" name="{{$form->htmlInputName($field['key'])}}[]" id="' + id + '"></select>' +
                '</div>' +
                '<div class="col-sm-4">' +
                '<a href="#" id="' + id + '-delete" class="btn btn-sm btn-default"> <i class="fa fa-times"></i> @lang('panel.actions.delete')</a>' +
                '</div>' +
                '</div>');

            var select2 = $('#' + id).select2({
                ajax: {
                    url: '{{ $url ?? '' }}',
                    dataType: 'json',
                },
                language: '{{ $language ?? app()->getLocale() }}',
                minimumInputLength: {{ $minimumInputLength ?? 0 }},
                placeholder: '{{ $placeholder ?? 'Выбрать...' }}'
            });

            if (value) {
                select2.append(new Option(value.text, value.id)).trigger('change');
            }

            $('#' + id + '-delete').click(function (e) {
                $(this).closest('.row').remove();
                e.preventDefault();
            });
        }

        add.click(function (e) {
            addSelect();

            e.preventDefault();
        });

        $(container).sortable();

        @foreach($values as $value)
            @if($value instanceof \Illuminate\Database\Eloquent\Model)
                addSelect({
            id: '{{ $id = $value->getKey() }}',
            text: '{{ array_get($options, $id) ?? $value->getAttribute('name') ?? $id }}'
        });
        @else
            addSelect({id: '{{ $value = strval($value) }}', text: '{{ array_get($options, $value) ?? $value }}'});
        @endif
        @endforeach
    });
</script>
@endpush