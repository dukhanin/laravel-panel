<?php
$errors = $form->fieldErrors($field['key']);
$id     = 'file-' . mt_rand(1, 1000);

$value = $form->inputValue($field['key']);

if ( ! is_array($value)) {
    $value = [ $value ];
}

$value   = array_map('intval', $value);
$files   = \App\File\File::findMany($value);
$resizes = isset( $resizes ) ? (array) $resizes : [ ];
?>

<div class="panel-file form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10 fileupload-container">
        {!! html_tag(
            'input#' . $id,
            array_except($field, ['key', 'type', 'label']),
            [
                'type' => 'file',
                'multiple' => true,
                'name' => $form->htmlInputName($field['key']) . '[]',
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
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.file.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.imageEditor.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.inputFiles.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/cropper.min.js') }}"></script>

<script type="text/javascript">
    $(function () {
        var input = $('#{!! $id !!}');

        var inputFiles = input.inputFiles({
            resizes: {!! json_encode($resizes) !!}

        });
        inputFiles.init();

        @foreach($files as $file)
            inputFiles.addFile(new panel.file({!! $file !!}));
        @endforeach


    });
    // ]]>
</script>
@endpush