<!doctype html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="{{ base_path('public/css/reset.css') }}">
        <style>
        @font-face {
            font-family: GenSenRounded;
            font-style: normal;
            font-weight: normal;
            src: url('{{ resource_path('fonts/GenSenRoundedJP-R-02.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: GenSenRounded;
            font-style: normal;
            font-weight: bold;
            src: url('{{ resource_path('fonts/GenSenMaruGothicJP-Bold.ttf') }}') format('truetype');
        }
        body {
            font-family: GenSenRounded;
            word-wrap:break-word;
        }
        .sheet {
            position: relative;
            width: 297mm;
            height: 205mm;
            margin: 0;
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: avoid;
        }
        </style>
        @stack('css')
        <title>@yield('title')</title>
    </head>
    <body>
        @yield('content')
    </body>
</html>
