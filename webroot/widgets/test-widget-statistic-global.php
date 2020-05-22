<!DOCTYPE html>
<html lang="de">

    <head>
        <meta charset="utf-8" />
        <title>Voransicht Statistik Global</title>
        <style>
           iframe {
               float: left;
               margin-right: 5px;
               margin-bottom: 5px;
               width: 400px;
               height: 750px;
               border: 1px solid gray;
               padding: 5px;
           }
        </style>
    </head>

    <body>
        <iframe src="/widgets/statistics-global?defaultDataSource=reparatur-initiativen"></iframe>
        <iframe src="/widgets/statistics-global"></iframe>
        <iframe src="/widgets/statistics-global?borderColorOk=rgb(14,113,184)&backgroundColorOk=rgba(14,113,184,0.6)&borderColorNotOk=rgb(181,24,33)&backgroundColorNotOk=rgba(181,24,33,0.6)"></iframe>
        <iframe style="height:550px;" src="/widgets/statistics-global?showDonutChart=0"></iframe>
    </body>

</html>
