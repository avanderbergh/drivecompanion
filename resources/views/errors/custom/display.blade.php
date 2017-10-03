<!DOCTYPE html>
<html>
<head>
    <title>{{$title}}</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            font-size: 24px;
            text-align: center;
            display: inline-block;
        }

        .oops {
            font-size: 72px;
            margin-bottom: 40px;
        }
        .title {
            font-size: 36px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="oops"><img src="/images/error.png"> Oops!</div>
        <div class="title">{{$title}}</div>
        <div class="content">{{$message}}</div>
    </div>
</div>
</body>
</html>
