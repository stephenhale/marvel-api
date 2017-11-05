<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marvel Viewer</title>
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
</head>
<body>
    <div id="wrapper">
        <div id="header">
            <h1>Marvel Viewer</h1>
        </div>
        @yield('content')
    </div>
</body>
</html>
