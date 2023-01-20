<!doctype html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        /* extract from A Modern CSS Reset start */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }
        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }
        /* extract from A Modern CSS Reset end */

        body {
            font-family: IPAPMincho, serif;
            font-size: 1em;
            line-height: 1.5;
            min-height: 100vh;
            width: 100%;
        }
        .gothic {
            font-family: IPAGothic, sans-serif;
        }
        .sheet {
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: avoid;
        }
        .no-use {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0eWxlPSJ3aWR0aDoxMDAlO2hlaWdodDoxMDAlOyI+PGxpbmUgeDE9IjEwMCUiIHkxPSIwJSIgeDI9IjAlIiB5Mj0iMTAwJSIgc3R5bGU9InN0cm9rZTogIzAwMDAwMDtzdHJva2Utd2lkdGg6IDE7Ij48L2xpbmU+PC9zdmc+');
            background-repeat: no-repeat;
        }
        .full-width {
            width: 100%;
        }
        .font-small {
            font-size: 0.8rem;
        }
        .font-x-small {
            font-size: 0.7rem;
        }
        .font-xx-small {
            font-size: 0.6rem;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 0.8rem;
        }
        .flex {
            display: flex;
        }
        .flex.column {
            flex-direction: column;
        }
        .flex.align-baseline {
            align-items: baseline;
        }
        .flex.align-center {
            align-items: center;
        }
        .flex.align-end {
            align-items: flex-end;
        }
        .flex.justify-around {
            justify-content: space-around;
        }
        .flex.justify-between {
            justify-content: space-between;
        }
        .flex.justify-center {
            justify-content: center;
        }
        .flex.justify-end {
            justify-content: flex-end;
        }
        .flex > .flexible {
            -webkit-flex: 1;
            flex: 1;
        }
        .thick-border {
            border: solid 2px #000;
        }
        .thick-border-top {
            border-top: solid 2px #000 !important;
        }
        .thick-border-right {
            border-right: solid 2px #000 !important;
        }
        .thick-border-bottom {
            border-bottom: solid 2px #000 !important;
        }
        .thick-border-left {
            border-left: solid 2px #000 !important;
        }
        .thin-border-bottom {
            border-bottom: solid 1px #000 !important;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-nowrap {
            white-space: nowrap;
        }
        tr:not(:last-of-type) {
            border-bottom: solid 1px #000;
        }
        </style>
        @stack('css')
        <title>@yield('title')</title>
    </head>
    <body>
        @yield('content')
    </body>
</html>
