<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{asset('favicon.ico')}}">
    <title>{{env('APP_NAME')}} | Login</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="{{asset('css/simplebar.css')}}">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{asset('css/feather.css')}}">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{asset('css/app-light.css')}}" id="lightTheme" disabled>
    <link rel="stylesheet" href="{{asset('css/app-dark.css')}}" id="darkTheme">
</head>
<body class="dark " style="overflow: hidden">
<div class="wrapper vh-100">
    <div class="row align-items-center h-100">
        <div class="col-lg-6 d-none d-lg-flex">
            <img src="" alt="">
        </div> <!-- ./col -->
        <div class="col-lg-6">
            <div class="w-50 mx-auto">
                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <span class="fe fe-alert-triangle fe-16 mr-2"></span> {{session('error')}}</div>
                @endif
                <form class="mx-auto text-center" action="#" method="POST">
                    @csrf
                    <a class="navbar-brand mx-auto flex-fill text-center" href="#">

                    </a>
                    <h1 class="h6 mb-3">Painel Login</h1>
                    <div class="form-group">
                        <label for="inputEmail" class="sr-only">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required="" autofocus="">
                    </div>
                    <div class="form-group">
                        <label for="inputPassword" class="sr-only">Senha</label>
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Senha" required="">
                    </div>
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Acessar</button>
                </form>
            </div> <!-- .card -->
        </div> <!-- ./col -->
    </div> <!-- .row -->
</div>
<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{asset('js/popper.min.js')}}"></script>
<script src="{{asset('js/moment.min.js')}}"></script>
<script src="{{asset('js/bootstrap.min.js')}}"></script>
<script src="{{asset('js/simplebar.min.js')}}"></script>
<script src="{{asset('js/daterangepicker.js')}}"></script>
<script src="{{asset('js/jquery.stickOnScroll.js')}}"></script>
<script src="{{asset('js/tinycolor-min.js')}}"></script>
<script src="{{asset('js/config.js')}}"></script>
<script src="{{asset('js/apps.js')}}"></script>

</body>
</html>
