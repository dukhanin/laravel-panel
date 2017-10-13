<?php
// @todo @refactoring зарефачить на один механизм даты/время: фильтры, формы

use Jenssegers\Date\Date;

global $dateIndex;

$value = $form->inputValue($field['key']);

$format = isset($format) ? $format : $form->config('date.format', 'Y-m-d');
$formatLabels = __("panel.formats");

try {
    if (is_null($value)) {
        $datetime = null;
    } elseif (is_numeric($value)) {
        $datetime = Date::createFromTimestamp(intval($value));
    } else {
        $datetime = Date::parse($value);
    }
} catch (Exception $e) {
    $datetime = null;
}

$dateValue = $datetime ? $datetime->format($format) : '';

$timeValue = $datetime ? $datetime->format('H:i') : '';
$timeValue = $timeValue === '00:00' ? '' : $timeValue;

$rawValue = $datetime ? $datetime->format('Y-m-d H:i:00') : '';
?>

<div class="form-group">
    <label class="control-label" style="clear: both;">
        {{ $label }}
    </label>

    <div class="clearfix">
        <div class="input-group date col-sm-7" id="{{ $id = 'date-'.(++$dateIndex) }}" style="float: left; margin-right: -1px;">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            {!! html_tag(
                'input.form-control',
                ['placeholder' => isset($formatLabels[$format]) ? $formatLabels[$format] : $format],
                array_except($field, ['key', 'type', 'label']),
                [
                    'type' => 'text',
                    'value' => $dateValue,
                ]
            ) !!}
        </div>

        <div class="input-group time col-sm-5">
            <span class="input-group-addon"><span class="fa fa-clock-o"></span></span>

            <input id="{{ $id }}-time"
                   class="form-control"
                   data-mask="{{preg_replace('#[^\s:]#', '9', 'hh:mm')}}"
                   data-placeholder="_"
                   placeholder="@lang("panel.formats.hh:mm")"
                   type="text"
                   value="{{$timeValue}}"
            />
        </div>
    </div>

    <input id="{{ $id }}-output"
           type="hidden"
           value="{{$rawValue}}"
           name="{{$form->htmlInputName($field['key'])}}"/>
</div>

@push('scripts')
<script>
    $(function () {
        var dateInput = $('#{{ $id }}'),
            timeInput = $('#{{ $id }}-time'),
            outputInput = $('#{{ $id }}-output');

        function setRawDatetime() {
            var date = dateInput.datepicker('getDate'),
                time = timeInput.val(),
                y = date.getFullYear(),
                m = date.getMonth() + 1,
                d = date.getDate(),
                raw = '';

            if (y > 0 && m > 0 && d > 0) {
                raw = y + '-' + m + '-' + d;
            }

            if (time && raw && (time = time.split(':')) && time.length == 2) {
                var h = parseInt(time[0]),
                    i = parseInt(time[1]);

                if (h >= 0 && h <= 23 && i >= 0 && i <= 59) {
                    raw += ' ' + (h < 10 ? '0' + h : h) + ':' + (i < 10 ? '0' + i : i) + ':00';
                } else {
                    timeInput.val('');
                }
            }

            outputInput.val(raw);
        }

        dateInput
            .datepicker({!! json_encode(['format' => datepicker_format($format)] + (empty($datepicker) ? [] : (array)$datepicker)) !!})
            .on('clearDate changeDate', setRawDatetime);

        timeInput
            .on('changed blur', setRawDatetime);

        dateInput.closest('form').on('submit', setRawDatetime);
    });
</script>
@endpush