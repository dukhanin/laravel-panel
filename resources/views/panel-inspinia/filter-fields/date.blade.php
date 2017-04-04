<?php
use Carbon\Carbon;

global $dateIndex;

$value = $form->inputValue($field['key']);

$format = isset($format) ? $format : $form->config('date.format', 'Y-m-d');

try {
    if (is_null($value)) {
        $date = null;
    } elseif (is_numeric($value)) {
        $date = Carbon::createFromTimestamp(intval($value));
    } else {
        $date = Carbon::parse($value);
    }
} catch (Exception $e) {
    $date = null;
}

$value = $date ? $date->format($format) : '';
$raw_value = $date ? $date->format('Y-m-d') : '';

$id = 'date-'.(++$dateIndex);
?>

<div class="form-group">
    <label class="control-label" for="order_id">
        {{ $label }}
    </label>

    <div class="input-group date" id="{{ $id }}">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

        {!! html_tag(
            'input.form-control',
            array_except($field, ['key', 'type', 'label']),
            [
                'type' => 'text',
                'value' => $value
            ]
        ) !!}
    </div>

    {!! html_tag(
        'input.form-control',
        array_except($field, ['key', 'type', 'label']),
        [
            'type' => 'hidden',
            'id' => $id . '-output',
            'name' => $form->htmlInputName($field['key']),
            'value' => $raw_value
        ]
    ) !!}
</div>


@push('scripts')
<script>
    $(function () {
        $('#{{ $id }}').datepicker({
            format: '{{ datepicker_format($format) }}'
        }).on('clearDate changeDate', function (e) {
            console.log(1);
            $('#{{ $id }}-output').val(e.date === undefined ? '' : e.date.getFullYear() + '-' + (e.date.getMonth() + 1) + '-' + e.date.getDate());
        });
    })
</script>
@endpush