<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="skype_toolbar" content="skype_toolbar_parser_compatible">

    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="fragment" content="!">

    <title>{{ $title }}</title>

    <link rel="shortcut icon" href="/assets/images/favicon.ico" />
    @foreach($assets['css'] as $css)
        <link href="{{ frontend()->path($css) }}" rel="stylesheet"/>
    @endforeach
</head>
<body>
<div id="app" data-component="{{ $name }}" data-props="{{ json_encode($props) }}" data-layout="{{ json_encode($layout) }}" data-env="{{ json_encode($env) }}" data-routes="{{ json_encode($routes) }}" data-title="{{ $title }}"></div>
@foreach($assets['js'] as $js)
    <script src="{{ frontend()->path($js) }}"></script>
@endforeach
</body>
</html>
