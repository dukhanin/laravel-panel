<?php
global $checkboxesIndex;

$state = isset($state) ? $state : false;
$icon = isset($icon) ? $icon : false;
?>

<div class="form-group">
    <div class="checkbox">
        <input type="checkbox"
               id="checkbox-{{ ++$checkboxesIndex }}"
               name="{{  $form->htmlInputName($field['key']) }}"
               value="1"
               @if( $form->inputValue($field['key']) ) checked @endif
        />

        <label class="@if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif"
               for="checkbox-{{ $checkboxesIndex }}">
            <i class="{{ $icon }}"></i> {{ $label }}
        </label>

    </div>
</div>