<?php
global $checkboxesIndex;

$value = $form->inputValue($field['key']);
$errors = $form->fieldErrors($field['key']);
$disabled = isset($disabled) ? $disabled : false;
$state = isset($state) ? $state : false;

if (! isset($options) || ! is_array($options) && ! $options instanceof Illuminate\Support\Collection) {
    $options = [];
}

if (isset($cols)) {
    $cols = intval($cols);
    $colSize = intval(isset($colSize) ? $colSize : floor(10 / $cols));
} elseif (count($options) < 8) {
    $cols = 1;
    $colSize = 12;
} elseif (count($options) < 20) {
    $cols = 2;
    $colSize = 4;
} else {
    $cols = 3;
    $colSize = 3;
}

if ($value instanceof Illuminate\Database\Eloquent\Relations\Relation) {
    $value = $value->get();
}

if ($value instanceof Illuminate\Database\Eloquent\Collection) {
    $value = $value->modelKeys();
}

$value = (array) $value;
?>


<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>
    <div class="col-lg-10">

        <div class="row">

            @foreach(collect($options)->chunk(ceil(count($options) / $cols)) as $colsBlock)

                <div class="col-md-{{ $colSize }}">
                    @foreach($colsBlock as $optionKey => $optionLabel)
                        <div class="checkbox @if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif">
                            <input type="checkbox"
                                   id="checkbox-{{ ++$checkboxesIndex }}"
                                   name="{{  $form->htmlInputName($field['key']) }}[]"
                                   value="{{ $optionKey }}"
                                   @if( $disabled ) disabled="" @endif
                                   @if( in_array($optionKey, $value) ) checked @endif
                            >
                            <label for="checkbox-{{ $checkboxesIndex }}"> <i></i> {{ $optionLabel }}</label>
                        </div>
                    @endforeach
                </div>

            @endforeach

        </div>

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