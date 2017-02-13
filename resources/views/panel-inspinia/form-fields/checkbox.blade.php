<?php
global $checkboxesIndex;

$errors = $form->fieldErrors($field['key']);
$state    = isset($state) ? $state : false;
$icon     = isset($icon) ? $icon : false;
?>

<div class="checkbox @if( ! empty($errors) )  has-error @endif">
    <input type="hidden" name="{{ $form->htmlInputName($field['key']) }}" value="0"/>

    <input type="checkbox"
           id="checkbox-{{ ++$checkboxesIndex }}"
           name="{{  $form->htmlInputName($field['key']) }}"
           value="1"
           @if( $form->inputValue($field['key']) ) checked @endif
            />

    <label class="@if( $state ) text-{{ $state }} checkbox-{{ $state }} @endif" for="checkbox-{{ $checkboxesIndex }}">
        <i class="{{ $icon }}"></i> {{ $label }}
    </label>

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