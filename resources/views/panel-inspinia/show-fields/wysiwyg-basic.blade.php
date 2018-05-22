<div class="form-group">
    <label class="col-sm-2 control-label">{{ $field['label'] }}</label>

    <div class="col-sm-10">
        <div class="value">
            {!! trim($show->value($field['key'])) ? : '&nbsp;'  !!}
        </div>
    </div>
</div>