<?php
use Jenssegers\Date\Date;

global $dateIndex;

$value = $form->inputValue($field['key']);
$errors = $form->fieldErrors($field['key']);
$id = 'date-'.(++$dateIndex);
$format = isset($format) ? $format : $form->config('date.format', 'Y-m-d');
$formatLabels = __("panel.formats");
$now = isset($now) ? $now : true;

try {
    if (is_null($value)) {
        $date = null;
    } elseif (is_numeric($value)) {
        $date = Date::createFromTimestamp(intval($value));
    } else {
        $date = Date::parse($value);
    }
} catch (Exception $e) {
    $date = null;
}

if (empty($date) && ! empty($now) ) {
    $date = Date::now();
}

$value = $date ? $date->format($format) : '';
$rawValue = $date ? $date->format('Y-m-d') : '';
?>

<div class="form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        <div class="input-group date" id="{{ $id }}">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            {!! html_tag(
                'input.form-control',
                ['placeholder' => isset($formatLabels[$format]) ? $formatLabels[$format] : $format],
                array_except($field, ['key', 'type', 'label']),
                [
                    'type' => 'text',
                    'value' => $value,
                ]
            ) !!}
        </div>

        <input id="{{ $id }}-output"
               type="hidden"
               value="{{$rawValue}}"
               name="{{$form->htmlInputName($field['key'])}}"/>

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

@push('scripts')
<script>
    $(function () {
        var dateInput = $('#{{ $id }}'),
            outputInput = $('#{{ $id }}-output');

        function setRawDatetime() {
            var date = dateInput.datepicker('getDate'),
                y = date.getFullYear(),
                m = date.getMonth() + 1,
                d = date.getDate(),
                raw = '';

            if (y > 0 && m > 0 && d > 0) {
                raw = y + '-' + m + '-' + d;
            }

            outputInput.val(raw);
        }

        dateInput
            .datepicker({!! json_encode(['format' => datepicker_format($format)] + (empty($datepicker) ? [] : (array)$datepicker)) !!})
            .on('clearDate changeDate', setRawDatetime);

        dateInput.closest('form').on('submit', setRawDatetime);
    });
</script>
@endpush