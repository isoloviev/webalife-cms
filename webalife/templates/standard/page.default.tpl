    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>{$Page.PageTitle}{$Page.SiteName}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="description" content="{$Page.Description}">
    <meta name="keywords" content="{$Page.Keywords}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="/files/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="/files/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/files/css/style.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <style type="text/css">
        body {
            padding-top: 20px;
            padding-bottom: 40px;
        }

            /* Custom container */
        .container-narrow {
            margin: 0 auto;
            max-width: 700px;
        }
        .container-narrow > hr {
            margin: 30px 0;
        }

            /* Main marketing message and sign up button */
        .jumbotron {
            margin: 60px 0;
            text-align: center;
        }
        .jumbotron h1 {
            font-size: 72px;
            line-height: 1;
        }
        .jumbotron .btn {
            font-size: 21px;
            padding: 14px 24px;
        }

            /* Supporting marketing content */
        .marketing {
            margin: 60px 0;
        }
        .marketing p + h4 {
            margin-top: 28px;
        }
    </style>
</head>

<body>

<div class="container-narrow">

    <div class="masthead">
        <ul class="nav nav-pills pull-right">
            <li {if $Page.PAGE_PATH eq "/"}class="active"{/if}><a href="/">Главная</a></li>
            {WL_MainBotMenu level=1}
            {section name=q loop=$BOTMENU}
                <li {if $BOTMENU[q].Active}class="active"{/if}><a href="{$BOTMENU[q].Link}">{$BOTMENU[q].Text}</a></li>
            {/section}
        </ul>
        <h3 class="muted">{$smarty.session.CMS_NAME}</h3>
    </div>

    <hr>

    <div class="jumbotron">
        <h1>Super awesome marketing speak!</h1>
        <p class="lead">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
    </div>

    <hr>

    {WL_Content}

    {if $Page.PAGE_PATH eq "/"}

        {include file="news/preview.tpl" align="left"}

    {/if}

    <hr>

    <div class="footer">
        <p>&copy; {$Page.Copyright}</p>
    </div>

</div> <!-- /container -->

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/files/bootstrap/js/jquery.js"></script>
<script src="/files/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
