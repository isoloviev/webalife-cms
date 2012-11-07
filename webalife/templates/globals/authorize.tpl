<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Webalife.CMS / Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="/files/bootstrap/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
        form {
            width: 400px;
            margin: 15% auto 0;
        }
    </style>
    <link href="/files/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="/">Webalife.CMS</a>
            <!--/.nav-collapse -->
        </div>
    </div>
</div>

<div class="container">
    {if $validationFailed}
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            Имя пользователя или пароль указаны не верно!
        </div>
    {/if}
    <form class="form-horizontal" action="authorize.php" method="post">
        <div class="control-group">
            <label class="control-label" for="inputEmail">Имя пользователя</label>
            <div class="controls">
                <input type="text" id="inputEmail" name="login" placeholder="Administrator" value="Administrator">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputPassword">Пароль</label>
            <div class="controls">
                <input type="password" id="inputPassword" name="pswrd" placeholder="Пароль">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox"> Запомнить меня
                </label>
                <button type="submit" class="btn">Войти</button>
            </div>
        </div>
        <input type="hidden" name="site" value="1">
        <input type="hidden" name="ref" value="{$smarty.get.ref}">
    </form>
</div> <!-- /container -->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/files/bootstrap/js/jquery.js"></script>
<script src="/files/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
