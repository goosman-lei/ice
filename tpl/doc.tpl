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
        height:620px;
        width:14%;
    }
    pre{ overflow: hidden; }
</style>
<body>
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <h1>{%$render.name%}</h1>
            </div>
            <div class="navbar-collapse collapse" style="text-align:right">
                <p>{%$render.desc%}</p>
                <ul class='nav navbar-nav flowrig' style="float:right">
                    {%foreach from = $render.tags  key=tkey item =tag %}        
                         <li>{%$tag.tag%}</li>
                         <li>{%$tag.desc%}</li>
                    {%/foreach%}
                </ul>
            </div>
        </div>
    </div>
    <div class="container bs-docs-container" style="margin-top:75px">
        <div class="row">
            <div class="col-md-2">
                <nav id='navbar-example2' class="bs-docs-sidebarhidden-print hidden-xs hidden-sm affix">
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
            <div class="col-md-10" style="border-left:3px solid #eee; ">
                <div  data-spy="scroll" data-target="#navbar-example2">
                    {%foreach from= $render.methods  key=mkey item= method%}
                        <div class="bs-docs-section">
                            <h2 id='{%$method.name%}'>{%$method.name%}</h2>
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
    </div>
</body>
</html>