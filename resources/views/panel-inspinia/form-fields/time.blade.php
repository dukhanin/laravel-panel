<?php
use Jenssegers\Date\Date;

global $dateIndex;

$value = $form->inputValue($field['key']);
$errors = $form->fieldErrors($field['key']);
$id = 'date-'.(++$dateIndex);
$format = 'H:i';
$formatLabels = __("panel.formats");
$now = isset($now) ? $now : true;

try {
    if (is_null($value)) {
        $time = null;
    } elseif (is_numeric($value)) {
        $time = Date::createFromTimestamp(intval($value));
    } else {
        $time = Date::parse($value);
    }
} catch (Exception $e) {
    $time = null;
}

if (empty($time) && ! empty($now) && $form->model() && ! $form->model()->exists) {
    $time = Date::now();
}

$value = $time ? $time->format($format) : '';
$value = $value === '00:00' ? '' : $value;
$rawValue = $time ? $time->format('H:i:00') : '';
?>

<div class="form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        <div class="input-group date" id="{{ $id }}">
            <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
            {!! html_tag(
                'input.form-control',
                [
                   'data-mask'=>preg_replace('#[^\s:]+#', '99', $format),
                   'data-placeholder'=>'_',
                   'placeholder' => isset($formatLabels[$format]) ? $formatLabels[$format] : $format,
                ],
                array_except($field, ['key', 'type', 'label']),
                [
                    'id' => $id . '-time',
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
        var timeInput = $('#{{ $id }}-time'),
            outputInput = $('#{{ $id }}-output');

        function setRawDatetime() {
            var time = timeInput.val(),
                raw = '';

            if (time && (time = time.split(':')) && time.length == 2) {
                var h = parseInt(time[0]),
                    i = parseInt(time[1]);

                if (h >= 0 && h <= 23 && i >= 0 && i <= 59) {
                    raw = ' ' + (h < 10 ? '0' + h : h) + ':' + (i < 10 ? '0' + i : i) + ':00';
                } else {
                    timeInput.val('');
                }
            }

            outputInput.val(raw);
        }

        timeInput
            .on('changed blur', setRawDatetime);

        timeInput.closest('form').on('submit', setRawDatetime);
    });
</script>
@endpush