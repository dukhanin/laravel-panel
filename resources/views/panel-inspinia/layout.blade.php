<!DOCTYPE html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dukhanin/laravel-panel sample</title>

    <link rel="stylesheet" href="{{ URL::asset('assets/inspinia/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/inspinia/font-awesome/css/font-awesome.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/inspinia/css/inspinia-animate.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/inspinia/css/inspinia.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('assets/inspinia/css/plugins/sweetalert/sweetalert.css') }}"/>
    <link rel="stylesheet"
          href="{{ URL::asset('assets/inspinia/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')

</head>
<body>
<div id="wrapper">

    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element"> <span>
                            <img alt="image" class="img-circle" src="/assets/inspinia/img/profile_small.jpg"
                                 style="max-width: 48px;"/>
                             </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">Anton
                                        Dukhanin </strong>
                             </span> <span class="text-muted text-xs block">Developer <b
                                            class="caret"></b></span> </span> </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a href="profile.html">Profile</a></li>
                            <li><a href="contacts.html">Contacts</a></li>
                            <li><a href="mailbox.html">Mailbox</a></li>
                            <li class="divider"></li>
                            <li><a href="login.html">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        IN+
                    </div>
                </li>

                <li>
                    <a href="https://antondukhanin.ru" target="_blank"><i class="fa fa-external-link"></i>
                        <span class="nav-label">Anton Dukhanin</span></a>
                </li>
                <li>
                    <a href="https://wrapbootstrap.com/theme/inspinia-responsive-admin-theme-WB0R5L90S" target="_blank"><i
                                class="fa fa-area-chart"></i> <span class="nav-label">Buy INSPINIA</span></a>
                </li>

            </ul>
        </div>
    </nav>

    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">

                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i>
                    </a>

                    <form role="search" class="navbar-form-custom" method="post" action="#">
                        <div class="form-group">
                            <input type="text" placeholder="Search for something..." class="form-control"
                                   name="top-search" id="top-search">
                        </div>
                    </form>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <a href="#">
                            <i class="fa fa-sign-out"></i> Sign out
                        </a>
                    </li>
                </ul>

            </nav>
        </div>

        @section('heading')
            @if( !empty($header) )
                <div class="row wrapper border-bottom white-bg page-heading">
                    <div class="col-lg-9">

                        <h2>
                            {{ $header }}
                        </h2>
                    </div>
                </div>
            @endif
        @show

        <div class="wrapper wrapper-tabs animated fadeInRight">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    @if(app('router')->has('products.showList'))
                        <li @if(str_is('products.*', app('router')->currentRouteName()))class="active"@endif>
                            <a href="{{ route('products.showList') }}?inspinia=1">Products</a>
                        </li>
                    @endif

                    @if(app('router')->has('sections.showList'))
                        <li @if(str_is('sections.*', app('router')->currentRouteName()))class="active"@endif>
                            <a href="{{ route('sections.showList') }}?inspinia=1">Sections</a>
                        </li>
                    @endif

                    <li class="pull-right">
                        <select onchange="document.location=this.value;">
                            <option value="?">Bootstrap theme</option>
                            <option selected value="?inspinia=1">Inspinia theme</option>
                        </select>
                    </li>
                </ul>
            </div>
        </div>

        @section('content')
            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center m-t-lg">
                            <h1>
                                dukhanin/laravel-panel
                            </h1>
                            <small>
                                <p>
                                    Develop, Break, Fix
                                </p>

                                <p>
                                    <a href="https://antondukhanin.ru" target="_blank"
                                       style="color: #676a6c; text-decoration: underline;"

                                    >Anton Dukhanin</a> &copy; {{ date('Y') }}
                                </p>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @show
    </div>
</div>


<script src="{{ URL::asset('assets/inspinia/js/jquery-2.1.1.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/plugins/pace/pace.min.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/plugins/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ URL::asset('assets/inspinia/js/inspinia.js') }}"></script>

<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
</script>

@stack('scripts')

</body>
</html>
