<html>
<head>
    <title>Homepage - Ice Framework (PHP Web Development Framework)</title>
    <script type="text/javascript" src="/static/jquery-2.2.3.js"></script>
    <script type="text/javascript">
$(function() {
});
{block name=script}{/block}
    </script>
    <style type="text/css">
.body {
    margin: 0px auto;
    width: 1024px;
    background-color: #FBFBFB;
    font-size: 12px;
    font-family: Sans-Serif;
}
.body .left {
    padding-right: 0.5em;
    margin: 0.5em 0.5em 0.5em 0px;
    border-right: 3px solid black;
    text-align: right;
    width: 18em;
    float: left;
}
.body .left .title {
    font-size: 2em;
}
.body .left .menus {
    font-size: 1.5em;
}
.body .left .menus li {
    list-style: none;
    margin-top: 5px;
}
.body .right {
    margin: 0em 4em 0em 2em;
    font-size: 2em;
}
{block name=style}{/block}
    </style>
</head>
<body>
    <div class="body">
        <div class="left">
            <p class="title">Ice Framework</p>
            <ul class="menus">
                <li><a href="/">About</a></li>
                <li><a href="https://github.com/goosman-lei/ice/archive/master.zip">Download</a></li>
                <li><a href="https://github.com/goosman-lei/ice">SourceCode</a></li>
                <li><a href="https://github.com/goosman-lei/ice/blob/master/README.md">ManualPage</a></li>
            </ul>
        </div>
        <div class="right">{block name=body}
{$body_content}
        {/block}</div>
    </div>
</body>
</html>

