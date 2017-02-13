<?php
$errors = $form->fieldErrors($field['key']);
$id     = 'wysiwyg-' . mt_rand(1, 1000);

$config = config('wysiwyg.default');
$config['images_upload_url'] = urlbuilder($config['images_upload_url'])->query([ 'directory' => $form->getUploadDirectory() ])->compile();
$config['filemanager_subfolder'] = $form->getUploadDirectory();
?>

<div class="form-group @if( ! empty($errors) )  has-error @endif">
    <label class="col-lg-10">
        {{ $label }}
    </label>

    <div class="col-lg-10">
        {!! html_tag(
            'textarea.form-control#' . $id,
            array_except($field, ['key', 'type', 'label']),
            [
                'attributes.name' => $form->htmlInputName($field['key']),
                'attributes.style' => 'height: 400px',
                'content' => e($form->inputValue($field['key']))
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

@push('scripts')
<script src="{{ URL::asset('du/tinymce/tinymce.jquery.min.js') }}"></script>
<script src="{{ URL::asset('du/tinymce/jquery.tinymce.min.js') }}"></script>

<script type="text/javascript">
    $(function(){
        tinymce.init(
                $.extend(
                        {!! json_encode($config)  !!},
                        {
                            selector: '#{!! $id !!}',
                            setup : function (editor) {

                                editor.on('FullscreenStateChanged', function(e){
                                    if( editor.plugins.fullscreen.isFullscreen() ) {
                                        $("body").addClass("mini-navbar");
                                    }
                                });
                            }
                        }
                )
        );
    });


</script>
@endpush