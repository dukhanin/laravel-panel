@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.form.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.list.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.tree.css') }}"/>
@endpush


@push('scripts')
<script src="{{ URL::asset('assets/panel-bootstrap/js/jquery-ui.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/jquery.multisortable.js') }}"></script>

<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.list.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.tree.js') }}"></script>

<script>
    $(function () {
        panel.uploadUrl = '{{ action('Panel\\PanelUploadController@upload') }}';

        panel.trans = {!! json_encode( trans('panel') ) !!};
    });
</script>
@endpush