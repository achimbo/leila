
<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="leila.css"  type="text/css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
<script src="jquery/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

    <meta charset="utf-8"/>

</head>
<body>
<div class="container">
    {include 'nav.tpl'}

    <h1>{t name={{$name}}}hello dear %1{/t}</h1>
    {t}test{/t}
    XXXX Français
    {html_table loop=$data}
</div>
</body>
</html>