<!DOCTYPE html>
<html>
<head>
    <title>{%$render.desc%}</title>
</head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="http://www.oneniceapp.com/js/lib/bootstrap-3.3/css/bootstrap.min.css"/>
<script type="text/javascript" src='http://www.oneniceapp.com/js/lib/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='http://www.oneniceapp.com/js/lib/bootstrap-3.3/js/bootstrap.min.js'></script>
<style type="text/css">
    body {
        position: relative;
    }
    .method_box{
        padding:10px;
        border:1px solid #eee;
    }
    .version_info{
        text-align: right;
    }
    #navbar-example2{
        overflow:auto;
        width:14%;
    }
    .bs-docs-container{ position: relative; }
    .left_pos{
        position: absolute;
        top: 0px;
        left: 0px;
        width: 280px;
        overflow: auto;
    }
    .right_pos{
        position: absolute;
        top: 0px;
        left: 280px;
        overflow: auto;
    }
    pre{ overflow: hidden; }
</style>
<script type="text/javascript">
    $(function(){
        var width = $(window).width();
        var height = $(window).height();
        curentWidth = width- 280- 40;
        curentHeight = height-70;
        $('.left_pos').css({
            'height': curentHeight
        });
        $('.right_pos').css({
            'width':curentWidth,
            'height': curentHeight
        });
        $(".filter").keyup(function(){
              $(".bs-docs-sidenav li")
                .hide()
                .filter(":contains('"+( $(this).val() )+"')")
                .show();
        }).keyup();
    });
</script>
<body>
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <h1>{%$render.name%}</h1>
            </div>
            <div class="navbar-collapse collapse" style="text-align:right">
                <p style='padding:0px;color:#563d7c;padding-top:15px'>{%$render.desc%}</p>
                <ul class='nav navbar-nav flowrig' style="float:right">
                    {%foreach from = $render.tags  key=tkey item =tag %}        
                         {%if $tag.tag =='author' %}
                             <li>{%$tag.tag%}:</li>
                             <li><a style='color:#428bca;padding:0px;' href='mailto:{%$tag.mail%}'>{%$tag.desc%}</a></li>
                         {%/if%}
                    {%/foreach%}
                </ul>
            </div>
        </div>
    </div>
    <div class="container bs-docs-container" style="margin-top:70px">
        <div class="left_pos">
            <div style="padding: 10px 0px;">搜索：<input type="text" class="form-control filter" style="width:80%;display:inline-block"></div>
            <nav id='navbar-example2' style="width:100%">
                <ul class="nav bs-docs-sidenav">
                {%foreach from=$render.methods key=mkey  item =method %}
                    <li><a href='#{%$method.name%}'>{%$method.name%}</a></li>
                    <ul>
                        <li><a href='#{%$method.name%}_param'>参数</a></li>   
                        <li><a href='#{%$method.name%}_return'>返回值</a></li>  
                        <li><a href='#{%$method.name%}_error'>错误号</a></li>        
                    </ul>
                {%/foreach%}
                </ul>
            </nav>
        </div>
        <div class="right_pos" style="border-left:3px solid #eee; padding-left:10px;">
            <div  data-spy="scroll" data-target="#navbar-example2">
                {%foreach from= $render.methods  key=mkey item= method%}
                    <div class="bs-docs-section">
                        <h2 id='{%$method.name%}'>{%$method.name%}</h2>
                        <div class='alert alert-info' style='margin-bottom:0px; padding:7px'>描述:{%$method.desc%}</div>
                        <div class='method_box'>
                            <table class="table table-condensed table-bordered">
                                <tr>
                                    <td style="text-align:right"><a name ='{%$method.name%}_param'>参数</a></td>
                                    <td>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='param' %}
                                                <div>{%$mtag.type%} {%$mtag.name%} {%$mtag.desc%}</div>
                                            {%/if%} 
                                        {%/foreach%}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:right"><a name ='{%$method.name%}_error'>错误号</a></td>
                                    <td>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='error' %}
                                                <div>{%$mtag.errno%}  {%$mtag.desc%}</div>
                                            {%/if%} 
                                    {%/foreach%}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:right"><a name ='{%$method.name%}_return'>返回值</a></td>
                                    <td>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='return' %}
                                                <div>返回类型：{%$mtag.type%}</div>
                                                <div>返回值：</div>
                                                <pre>{%$mtag.desc%}</pre>
                                            {%/if%} 
                                        {%/foreach%}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                {%/foreach %}
            </div>
        </div>
    </div>
</body>
</html>
