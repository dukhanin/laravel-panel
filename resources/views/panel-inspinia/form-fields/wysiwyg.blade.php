<?php
$errors = $form->fieldErrors($field['key']);
$id = 'wysiwyg-'.mt_rand(1, 1000);

$config = config('wysiwyg.default');
$config['images_upload_url'] = urlbuilder($config['images_upload_url'])->query(['directory' => $form->uploadDirectory()])->compile();
$config['filemanager_subfolder'] = $form->uploadDirectory();
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
                'name' => $form->htmlInputName($field['key']),
                'style' => 'height: 400px',
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
<script type="text/javascript">
    $(function () {
        tinymce.init(
            $.extend(
                    {!! json_encode($config)  !!},
                {
                    selector: '#{!! $id !!}',
                    setup: function (editor) {

                        editor.on('FullscreenStateChanged', function (e) {
                            if (editor.plugins.fullscreen.isFullscreen()) {
                                $("body").addClass("mini-navbar");
                            }
                        });
                    },
                    images_upload_handler: function (blobInfo, success, failure) {
                        var formData;

                        formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());

                        $.ajax({
                            url: '{{ $config['images_upload_url'] }}',
                            method: 'post',
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            contentType: false,
                            processData: false,
                            success: function (responseJSON, textStatus, jqXHR) {
                                if (jqXHR.status != 200) {
                                    panel.error(textStatus);
                                    failure(textStatus);
                                    return;
                                }

                                if (!responseJSON || 'string' != typeof responseJSON.location) {
                                    panel.error(jqXHR.responseText);
                                    failure('Invalid JSON');
                                    return;
                                }

                                success(responseJSON.location);
                            },
                            error: function (jqXHR) {
                                panel.error(jqXHR.responseText);
                                failure('Invalid JSON');
                            }
                        });
                    }
                }
            )
        );
    });


</script>
@endpush