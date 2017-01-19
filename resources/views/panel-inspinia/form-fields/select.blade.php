<?
$nullTitle = isset( $nullTitle ) ? $nullTitle : trans('du-panel.labels.choose');

if ( ! isset( $options ) || ! is_array($options) && ! $options instanceof Illuminate\Support\Collection) {
    $options = [ ];
}

$errors            = $form->getFieldErrors($field['key']);
$nullTitleSelected = in_array($form->getInputValue($field['key']), array( NULL, '' ), true);
?>


<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        {!! html_tag_open(
            'select.form-control',
            array_except($field, ['key', 'type', 'label', 'nullTitle']),
            ['attributes.name' => $form->getHtmlInputName($field['key'])]
        ) !!}

        @if($nullTitle !== false)
            <option value="">{{ $nullTitle }}</option>
        @endif

        @foreach($options as $optionKey => $optionLabel)
            <option
                    value="{{ $optionKey }}"
                    @if(! $nullTitleSelected && $form->getInputValue($field['key']) == $optionKey) selected @endif
                    >{{ $optionLabel }}</option>
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