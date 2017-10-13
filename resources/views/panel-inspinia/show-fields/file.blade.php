<?php
use Dukhanin\Panel\Files\File;

$file = ($value = $show->value($field['key'])) instanceof File ? $value : File::find($value);
?>
<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value">
            @if($file)
                @if($file->isImage())
                    <a id="file-{{ $file->getKey() }}"
                       href="{{ $file->url() }}">{!! $file->getResize(['panel_default', 'size' => '150xx150'])->img() !!} </a>

                    @push('scripts')
                    <script>
                        $(function () {
                            $('#file-{{ $file->getKey() }}').click(function (e) {
                                var editor = new panel.imageEditor();

                                var file = new panel.file( {!! $file->toJson() !!} );

                                editor.setFile(file);

                                editor.init();

                                editor.open();

                                e.preventDefault();
                            });
                        });
                    </script>
                    @endpush
                @else
                    <a href="{{ $file->url() }}" target="_blank">
                        <i class="fa fa-file-o"></i>
                        {{ $file->getBaseName() }}
                    </a>
                @endif
            @else
                &nbsp;
            @endif
        </div>
    </div>
</div>