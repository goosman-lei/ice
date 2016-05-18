<html>
<head>
    <title>Homepage - Ice Framework (PHP Web Development Framework)</title>
    <script type="text/javascript" src="/static/jquery-2.2.3.js"></script>
    <script type="text/javascript">
{block name=script}{/block}
    </script>
    <style type="text/css">
body {
    margin: 0px auto;
    width: 1024px;
    background-color: #E8E8E8;
}
.head {
    height: 80px;
    border-bottom: 1px solid #A0A0A0;
    position: relative;
}
.head .navigation {
    padding: 0px 20px;
    height: 30px;
    line-height: 30px;
    bottom: 0px;
    position: absolute;
    background-color: #E0E0E0;
}
.head .navigation a {
    cursor: point;
    display: block;
    width: 80px;
    height: 30px;
    text-align: center;
    font-size: 12px;
    color: gray;
    text-decoration: none;
}
.head .navigation a:hover {
    font-size: 18px;
    background-color: #E0E0E0;
}
.body {
    height: 100%;
}
.body-left {
    width: 220px;
    height: 100%;
    border-right: 1px solid #A0A0A0;
}
{block name=style}{/block}
    </style>
</head>
<body>
    <div class="head">
        <div class="navigation">
        {foreach $rootNavs as $nav}
        <a href="{$nav.url}">{$nav.name}</a>
        {/foreach}
        </div>
    </div>
    <div class="body">
        <div class="body-left">
        </div>
        <div class="body-right">
        {block name=body}{/block}
        </div>
    </div>
    <div class="foot">
    </div>
</body>
</html>

