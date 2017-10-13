<?php
$options = isset($options) ? $options : null;

if ($options instanceof Illuminate\Support\Collection) {
    $options = $options->toArray();
}

if (! isset($options) || ! is_array($options)) {
    $options = [];
}

$value = $show->value($field['key']);

if ($value instanceof Illuminate\Database\Eloquent\Relations\Relation) {
    $value = $value->get();
}

if ($value instanceof Illuminate\Database\Eloquent\Collection) {
    $value = $value->modelKeys();
}

$value = (array) $value;

$nullTitle = isset($nullTitle) ? $nullTitle : trans('panel.labels.none');
$nullTitleSelected = count($value) == 1 && array_intersect($value, array(NULL, ''));
?>

<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value">
            @foreach(array_only($options, $value) as $option)
                {{ $option }}@if (!$loop->last), @endif
            @endforeach
        </div>
    </div>
</div>