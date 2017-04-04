<?php
$nullTitle = isset($nullTitle) ? $nullTitle : trans('panel.labels.choose');

if (! isset($options) || ! is_array($options) && ! $options instanceof Illuminate\Support\Collection) {
    $options = [];
}

$value = $form->inputValue($field['key']);
$nullTitleSelected = in_array($form->inputValue($field['key']), array(NULL, ''), true);
?>

<div class="form-group">
    <label class="control-label" for="order_id">
        {{ $label }}
    </label>

    {!! html_tag_open(
        'select.form-control',
        array_except($field, ['key', 'type', 'label', 'nullTitle']),
        ['name' => $form->htmlInputName($field['key'])]
    ) !!}

    @if($nullTitle !== false)
        <option value="">{{ $nullTitle }}</option>
    @endif

    @foreach($options as $optionKey => $optionLabel)
        <option
                value="{{ $optionKey }}"
                @if(! $nullTitleSelected && $value == $optionKey) selected @endif
        >{{ preg_replace('/\s{2}/', '&nbsp;&nbsp;', $optionLabel) }}</option>
    @endforeach

    {!! html_tag_close('select') !!}
</div>

