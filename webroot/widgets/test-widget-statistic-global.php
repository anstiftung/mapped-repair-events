<!DOCTYPE html>
<html lang="de">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <title>Voransicht Statistik Global</title>
        <style>
           iframe {
               float: left;
               margin-right: 5px;
               margin-bottom: 5px;
               width: 400px;
               height: 780px;
               border: 1px solid gray;
               padding: 5px;
           }
        </style>
    </head>

    <body>
        <iframe src="/widgets/statistics-global?defaultDataSource=platform"></iframe>
        <iframe src="/widgets/statistics-global"></iframe>
        <iframe src="/widgets/statistics-global?city=Hamburg"></iframe>
        <iframe src="/widgets/statistics-global?province=Bayern"></iframe>
        <iframe src="/widgets/statistics-global?borderColorOk=rgb(14,113,184)&backgroundColorOk=rgba(14,113,184,0.6)&borderColorNotOk=rgb(181,24,33)&backgroundColorNotOk=rgba(181,24,33,0.6)"></iframe>
        <iframe style="height:550px;" src="/widgets/statistics-global?showDonutChart=0"></iframe>
    </body>

</html>
