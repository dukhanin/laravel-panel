@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/cropper.min.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/datepicker3.css') }}"/>

<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.form.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.list.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.tree.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.inputFiles.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-bootstrap/css/panel.imageEditor.css') }}"/>
@endpush


@push('scripts')
<script src="{{ URL::asset('assets/panel-bootstrap/js/jquery-ui.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/jquery.multisortable.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/cropper.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/bootstrap-datepicker.js') }}"></script>
@if(app()->getLocale()  != 'en')<script src="{{ URL::asset('assets/panel-inspinia/js/bootstrap-datepicker.' . app()->getLocale() . '.js') }}"></script>@endif
<script src="{{ URL::asset('assets/tinymce/tinymce.jquery.min.js') }}"></script>
<script src="{{ URL::asset('assets/tinymce/jquery.tinymce.min.js') }}"></script>

<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.list.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.tree.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.file.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.imageEditor.js') }}"></script>
<script src="{{ URL::asset('assets/panel-bootstrap/js/panel.inputFiles.js') }}"></script>

<script>
    $(function () {
        $.fn.datepicker.defaults = $.extend(true, $.fn.datepicker.defaults, {
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            calendarWeeks: true,
            autoclose: true,
            language: '{{ app()->getLocale() }}'
        });

        panel.trans = {!! json_encode( trans('panel') ) !!};

        @if(app('router')->has('panel.upload.form'))
            panel.uploadUrl = '{{ route('panel.upload.form') }}';
        @endif
    });
</script>
@endpush