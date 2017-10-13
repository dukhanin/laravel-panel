<?php
$options = isset($options) ? $options : null;

if ($options instanceof Illuminate\Support\Collection) {
    $options = $options->toArray();
}

if (! isset($options) || ! is_array($options)) {
    $options = [];
}

$value = (array) $form->inputValue($field['key']);
$nullTitle = isset($nullTitle) ? $nullTitle : trans('panel.labels.choose');
$nullTitleSelected = count($value) == 1 && array_intersect($value, array(NULL, ''));
?>

<div class="form-group">
    <label class="control-label" for="order_id">
        {{ $label }}
    </label>

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
        >{{ preg_replace('/\s{2}/', '&nbsp;&nbsp;', $optionLabel) }}</option>
    @endforeach

    {!! html_tag_close('select') !!}
</div>

