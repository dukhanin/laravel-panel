<?php
$errors = $form->fieldErrors($field['key']);
$id     = 'file-' . mt_rand(1, 1000);

$value = $form->inputValue($field['key']);
$value = intval($value);

$file    = \Dukhanin\Panel\Files\File::find($value);
$resizes = isset($resizes) ? (array) $resizes : [];
?>

<div class="panel-file form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10 control-label">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        {!! html_tag('input',
            array_except($field, ['key', 'type', 'label']),
            [
                'id' => $id,
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
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.files.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/cropper.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ URL::asset('assets/panel-inspinia/js/cropper.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.file.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.imageEditor.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.inputFiles.js') }}"></script>

<script type="text/javascript">
    $(function () {
        var inputFiles = new panel.inputFiles('#{!! $id !!}', {
            resizes: {!! json_encode($resizes) !!}
        });

        inputFiles.init();

        @if($file)
            inputFiles.addFile(new panel.file({!! $file !!}));
        @endif
    });
</script>
@endpush