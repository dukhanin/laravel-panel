<?php
$options = isset($options) ? $options : null;

if ($options instanceof Illuminate\Support\Collection) {
    $options = $options->toArray();
}

if (! isset($options) || ! is_array($options)) {
    $options = [];
}

$value = (array) $form->inputValue($field['key']);
$errors = $form->fieldErrors($field['key']);
$nullTitle = isset($nullTitle) ? $nullTitle : trans('panel.labels.choose');
$nullTitleSelected = count($value) == 1 && array_intersect($value, array(NULL, ''));
?>

<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        {!! html_tag_open(
            'select.form-control',
            array_except($field, ['key', 'type', 'label', 'nullTitle']),
            ['name' => $form->htmlInputName($field['key']) . (array_get($field, 'multiple') ? '[]' : '')]
        ) !!}

        @if($nullTitle !== false)
            <option value="">{{ $nullTitle }}</option>
        @endif

        @foreach($options as $optionKey => $optionLabel)
            <option
                    value="{{ $optionKey }}"
                    @if(! $nullTitleSelected && in_array($optionKey, $value)) selected @endif
            >{!! preg_replace('/\s{2}/', '&nbsp;&nbsp;', e($optionLabel)) !!}</option>
        @endforeach
        {!! html_tag_close('select') !!}

        @if ( ! empty( $errors ) )
            <div class="error-text">
                @foreach($errors as $error)
                    <span class="help-block">
                        {{ $error }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>