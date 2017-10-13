<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value @if( !($value = $show->value($field['key']))) inactive @endif">
            <i title="{{ $value ? 'Отмечено' : 'Не отмечено' }}" data-toggle="tooltip"
               class="fa fa-lg {{ $value ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
        </div>
    </div>
</div>