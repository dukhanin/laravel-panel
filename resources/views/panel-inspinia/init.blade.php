@push('styles')
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/cropper.min.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/datepicker3.css') }}"/>

<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.form.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.list.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.tree.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.inputFiles.css') }}"/>
<link rel="stylesheet" href="{{ URL::asset('assets/panel-inspinia/css/panel.imageEditor.css') }}"/>
@endpush

@push('scripts')
<script src="{{ URL::asset('assets/panel-inspinia/js/jquery-ui.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/jquery.multisortable.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/cropper.min.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/bootstrap-datepicker.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/bootstrap-datepicker.ru.js') }}"></script>
<script src="{{ URL::asset('assets/tinymce/tinymce.jquery.min.js') }}"></script>
<script src="{{ URL::asset('assets/tinymce/jquery.tinymce.min.js') }}"></script>


<script src="{{ URL::asset('assets/panel-inspinia/js/panel.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.list.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.tree.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.file.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.imageEditor.js') }}"></script>
<script src="{{ URL::asset('assets/panel-inspinia/js/panel.inputFiles.js') }}"></script>

<script>
    $(function () {
        $.fn.datepicker.defaults = $.extend(true, $.fn.datepicker.defaults, {
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            calendarWeeks: true,
            autoclose: true,
            language: '{{ config('app.locale') }}'
        });

        panel.uploadUrl = '{{ route('panel.upload.form') }}';

        panel.trans = {!! json_encode( trans('panel') ) !!};
    });
</script>
@endpush