{!! html_tag(
    'input.form-control',
    array_except($field, ['key', 'type', 'label']),
    [
        'type' => 'hidden',
        'name' => $form->htmlInputName($field['key']),
        'value' => $form->inputValue($field['key'])
    ]
) !!}