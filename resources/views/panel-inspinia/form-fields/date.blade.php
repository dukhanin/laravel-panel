<?php
use Carbon\Carbon;

global $dateIndex;

$value = $form->inputValue($field['key']);

$format = isset($format) ? $format : $form->config('date.format', 'Y-m-d');

try {
    if( is_null($value)) {
        $value = '';
    } elseif( is_numeric($value) ) {
        $value = Carbon::createFromTimestamp( intval($value) )->format($format);
    } else {
        $value = Carbon::parse($value)->format($format);
    }
} catch( Exception $e ) {
    $value = null;
}

$errors = $form->fieldErrors($field['key']);
$id = 'date-' . (++$dateIndex);
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
                array_except($field, ['key', 'type', 'label']),
                [
                    'type' => 'text',
                    'name' => $form->htmlInputName($field['key']),
                    'value' => $form->inputValue($field['key'])
                ]
            ) !!}
        </div>

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
        $('#{{ $id }}').datepicker({});
    })
</script>
@endpush