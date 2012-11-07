<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Webalife.CMS &mdash; {$WorkSpaceTitle}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="/files/bootstrap/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
           padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
        html,
        body {
            height: 100%;
            /* The html and body elements cannot have any padding or margin. */
        }

            /* Wrapper for page content to push down footer */
        #wrap {
            min-height: 100%;
            height: auto !important;
            height: 100%;
            /* Negative indent footer by it's height */
            margin: 0 auto -60px;
        }

            /* Set the fixed height of the footer here */
        #push,
        #footer {
            height: 60px;
        }
        #footer {
            background-color: #f5f5f5;
        }

            /* Lastly, apply responsive CSS fixes as necessary */
        @media (max-width: 767px) {
            #footer {
                margin-left: -20px;
                margin-right: -20px;
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        .container .credit {
            margin: 20px 0;
        }

    </style>
    <link href="/files/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script src="/files/bootstrap/js/jquery.js"></script>
    <script src="/files/bootstrap/js/bootstrap.min.js"></script>
    <script src="/files/js/admin.js"></script>
    <script src="/files/js/jquery.cookie-1.1.min.js"></script>
    <script src="/files/js/cal2.js"></script>
    <script src="/files/js/cal_conf2.js"></script>

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
            {WL_AdminMenu}
            <ul class="nav">
            {section name=q loop=$AdminMenu}
                <li class="dropdown">
                    <a class="dropdown-toggle"
                       data-toggle="dropdown"
                       href="#">{$AdminMenu[q].TITLE}<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                    {section name=w loop=$AdminMenu[q].SMNU}
                        <li><a href="/webalife/admin/m/{$AdminMenu[q].SMNU[w].PATH_TO}">{$AdminMenu[q].SMNU[w].TITLE}</a></li>
                    {/section}
                    </ul>
                </li>
            {/section}
            </ul>
            <div class="btn-group pull-right" data-toggle="buttons-radio">
                <button id="btn-lng-rus" type="button" class="btn btn-small">Рус</button>
                <button id="btn-lng-eng" type="button" class="btn btn-small">Eng</button>
            </div>
        </div>
    </div>
</div>

<div id="wrap">
    <div class="container">
        <h3>{$WorkSpaceTitle}</h3>
        {WL_AdminContent}
    </div>
    <!-- /container -->
    <div id="push"></div>
</div>

<div id="footer">
    <div class="container">
        <p class="muted credit">&copy; Webalife.CMS.</p>
    </div>
</div>

</body>
</html>
