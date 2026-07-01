<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') — MR. CHICKEN POS</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="is-{{ auth()->user()->role }}">

<div id="app" class="visible" style="display:flex;">

    @include('layouts.partials.sidebar')

    <main class="main">

        @include('layouts.partials.topbar')

        <div class="content">
            @yield('content')
        </div>

    </main>

</div>

@include('layouts.partials.toast')

@yield('scripts')

</body>
</html>
