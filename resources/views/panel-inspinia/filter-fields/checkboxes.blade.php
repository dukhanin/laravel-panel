<?php
global $checkboxesIndex;

$value = $form->inputValue($field['key']);
$state = isset($state) ? $state : false;

if (!isset($options) || !is_array($options) && !$options instanceof Illuminate\Support\Collection) {
    $options = [];
}

if ($value instanceof Illuminate\Database\Eloquent\Relations\Relation) {
    $value = $value->get();
}

if ($value instanceof Illuminate\Database\Eloquent\Collection) {
    $value = $value->modelKeys();
}

if (!is_array($value)) {
    $value = [];
}

?>


<div class="form-group">
    @foreach($options as $optionKey => $optionLabel)
        <div class="checkbox @if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif">
            <input type="checkbox"
                   id="checkbox-{{ ++$checkboxesIndex }}"
                   name="{{  $form->htmlInputName($field['key']) }}[]"
                   value="{{ $optionKey }}"
                   @if( in_array($optionKey, $value) ) checked @endif
            >
            <label for="checkbox-{{ $checkboxesIndex }}"> <i></i> {{ $optionLabel }}</label>
        </div>
    @endforeach
</div>
