<?php
$value = $show->value($field['key']);

try {
    $date = Date::parse($value);
} catch (Exception $e) {
}
?>

<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value">
            {{ $date ?? $value }}
        </div>
    </div>
</div>