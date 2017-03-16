@push('styles')
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.form.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.list.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.tree.css') }}"/>
@endpush


@push('scripts')
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/jquery.multisortable.js') }}"></script>

    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.list.js') }}"></script>
    <script src="{{ URL::asset('assets/panel-inspinia/js/panel.tree.js') }}"></script>

    <script>
        $(function () {
            panel.uploadUrl = '{{ action('Panel\\PanelUploadController@upload') }}';

            panel.trans = {!! json_encode( trans('panel') ) !!};
        });
    </script>
@endpush