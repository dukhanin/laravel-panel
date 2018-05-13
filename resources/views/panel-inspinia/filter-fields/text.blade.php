<div class="form-group">
    <label class="control-label" for="order_id">
        {{ $label }}
    </label>
    {!! html_tag(
        'input.form-control',
        array_except($field, ['key', 'type', 'label']),
        [
            'type' => $field['type'],
            'name' => $form->htmlInputName($field['key']),
            'value' => $form->inputValue($field['key'])
        ]
    ) !!}
</div>