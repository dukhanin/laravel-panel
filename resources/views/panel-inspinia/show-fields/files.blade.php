<?php
use Dukhanin\Panel\Files\File;
use Illuminate\Database\Eloquent\Collection;
use Dukhanin\Panel\Files\FileManager;

$files = ($value = $show->value($field['key'])) instanceof Collection ? $value : app(FileManager::class)->findMany($value);
?>
<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value">
            @foreach($files as $file)
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
                    <p>
                        <a href="{{ $file->url() }}" target="_blank">
                            <i class="fa fa-file-o"></i>
                            {{ $file->getBaseName() }}
                        </a>
                    </p>
                @endif
            @endforeach

            &nbsp;
        </div>
    </div>
</div>