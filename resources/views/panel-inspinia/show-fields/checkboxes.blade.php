<?php
$value = $show->value($field['key']);

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

<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="row">

            @foreach(collect($options)->chunk(ceil(count($options) / $cols)) as $colsBlock)

                <div class="col-md-{{ $colSize }}">
                    @foreach($colsBlock as $optionKey => $optionLabel)
                        <div class="value @if(!($checked = in_array($optionKey, $value))) inactive @endif">
                            <i title="{{ $checked ? 'Отмечено' : 'Не отмечено' }}" data-toggle="tooltip"
                                class="fa fa-lg {{ $checked ? 'fa-check-square-o' : 'fa-square-o' }}"></i>

                            {{ $optionLabel }}
                        </div>
                    @endforeach
                </div>

            @endforeach
        </div>
    </div>
</div>