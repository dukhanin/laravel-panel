<!DOCTYPE html>
<html lang="en">
<head>

    <title>dukhanin/laravel-panel sample</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')

</head>

<body>


<div class="container">
    @section('heading')
        @if( !empty($header) )
            <h1 style="margin: 40px 0 40px 0;">{{ $header }}</h1>
        @endif
    @show

    <ul class="nav nav-tabs" role="tablist">
        <li @if(Route::getCurrentRoute()->getName() === 'panel-sample.products')class="active"@endif>
            <a href="{{ route('panel-sample.products') }}">Products</a>
        </li>

        <li @if(Route::getCurrentRoute()->getName() === 'panel-sample.sections')class="active"@endif>
            <a href="{{ route('panel-sample.sections') }}">Sections</a>
        </li>
        <li class="pull-right">
            <select onchange="document.location=this.value;">
                <option selected value="?">Bootstrap theme</option>
                <option value="?inspinia=1">Inspinia theme</option>
            </select>
        </li>
    </ul>

    <div class="panel panel-default"
         style="border-top: 0; border-top-left-radius: 0; border-top-right-radius: 0;">
        <div class="panel-body">
            @yield('content')

        </div>
    </div>

</div>

@stack('scripts')

</body>

</html>