<html>
    <head>
        <title>App Name - @yield('title')</title>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<style>

tr td:nth-child(2) {
  background-color: white;
}
</style>

    </head>
    <body>

        @section('sidebar')
            Layout sidebar.
        @show

        <div class="container">
            <div class="mx-auto">
                @if ( Session::has('flash_message') )
                    <div class="alert {{ Session::get('flash_type') }}">
                        <h3>{{ Session::get('flash_message') }}</h3>
                    </div>
                @endif
            </div>
            
        </div>
 
        <div class="container">
       
            @yield('content')
            
            
        </div>
    </body>
</html>