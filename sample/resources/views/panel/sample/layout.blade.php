<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

    <script src="{{ URL::asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/bootstrap.min.js') }}"></script>
</head>

<body>


<div class="container">
    <h1>Panel Sample</h1>

    <ul class="nav nav-tabs" role="tablist">
        <li @if(Route::getCurrentRoute()->getName() === 'panel-sample.products')class="active"@endif>
            <a href="{{ route('panel-sample.products') }}">Products (PanelList sample)</a>
        </li>
        
        <li @if(Route::getCurrentRoute()->getName() === 'panel-sample.sections')class="active"@endif>
            <a href="{{ route('panel-sample.sections') }}">Sections (PanelTree sample)</a>
        </li>
    </ul>

    @yield('content')
</div>

</body>

</html>