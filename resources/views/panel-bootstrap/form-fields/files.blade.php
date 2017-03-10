<?php
$errors = $form->fieldErrors($field['key']);
$id     = 'file-' . mt_rand(1, 1000);

$value = $form->inputValue($field['key']);
$value = array_map('intval', is_array($value) ? $value : [ $value ]);

$files     = \Dukhanin\Panel\Files\File::findManyOrdered($value);
$resizes   = isset($resizes) ? (array) $resizes : [];
$directory = isset($directory) ? strval($directory) : null;
?>

<div class="form-group @if( ! empty($errors) ) has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        {!! html_tag('input',
            array_except($field, ['key', 'type', 'label']),
            [
                'id' => $id,
                'type' => 'file',
                'multiple' => true,
                'name' => $form->htmlInputName($field['key']),
            ]
        ) !!}

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

@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.inputFiles.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.imageEditor.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/cropper.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ URL::asset('assets/panel-bootstrap/js/jquery-ui.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/cropper.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.file.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.imageEditor.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.inputFiles.js') }}"></script>


<script type="text/javascript">
    $(function () {
        var inputFiles = new panel.inputFiles('#{!! $id !!}', {
            resizes: {!! json_encode($resizes) !!},
            directory: '{!! $directory !!}'
        });

        inputFiles.init();

        @foreach($files as $file)
            inputFiles.addFile(new panel.file({!! $file !!}));
        @endforeach
    });
</script>
@endpush