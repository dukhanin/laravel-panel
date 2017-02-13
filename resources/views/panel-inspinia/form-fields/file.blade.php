<?php
$errors = $form->fieldErrors($field['key']);
$id = 'file-' . mt_rand(1, 1000);

$value = $form->inputValue($field['key']);
$value = intval($value);
$file = \App\File\File::find($value);
?>

<div class="panel-file form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10 control-label">
        {{ $label }}
    </label>

    <div class="col-lg-10 fileupload-container">
        {!! HTML::renderTag(
            'input#' . $id,
            array_except($field, ['key', 'type', 'label']),
            [
                'type' => 'file',
                'name' => $form->htmlInputName($field['key']),
            ]
        ) !!}

        @if ( ! empty( $errors ) )
            <div class="error-text">
                @foreach($errors as $error)
                    <span class="help-block m-b-none">
                        {{ $error }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.files.css') }}">
@endpush

@push('scripts')
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.files.js') }}"></script>

<script type="text/javascript">
    $(function () {
        var input = $('#{!! $id !!}');

        input.panelFiles();

        @if($file)
            input.panelFiles().addFile( {!! $file !!} );
        @endif
    });
    // ]]>
</script>
@endpush