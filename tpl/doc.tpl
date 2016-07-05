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
    <div class="container bs-docs-container">
        <div>
            <h1>{%$render.name%}</h1>
            <p>{%$render.desc%}</p>
            <div class='version_info'>
            {%foreach from = $render.tags  key=tkey item =tag %}        
                 <span>{%$tag.tag%}</span>
                 <span>{%$tag.desc%}</span>
            {%/foreach%}
            </div>
        </div>    
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
            <div class="col-md-10">
                <div  data-spy="scroll" data-target="#navbar-example2">
                    {%foreach from= $render.methods  key=mkey item= method%}
                        <div class="bs-docs-section">
                            <h2 id='{%$method.name%}'>{%$method.name%}</h2>
                            <div class='method_box'>
                                <h3 id ='{%$method.name%}_param'>参数</h2>
                                <div class='highlight'>
                                    <pre>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='param' %}
                                                <div>type:{%$mtag.type%}</div>
                                                <div>name:{%$mtag.name%}</div>
                                                <div>desc:{%$mtag.desc%}</div>
                                            {%/if%} 
                                        {%/foreach%}
                                    </pre>
                                </div>
                                <h3 id ='{%$method.name%}_return'>返回值</h2>
                                <div class='highlight'>
                                    <pre>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='return' %}
                                                <div>type:{%$mtag.type%}</div>
                                                <div>name:{%$mtag.name%}</div>
                                                <div>desc:{%$mtag.desc%}</div>
                                            {%/if%} 
                                        {%/foreach%}
                                    </pre>
                                </div>
                                <h3 id ='{%$method.name%}_error'>错误号</h2>
                                <div class='highlight'>
                                    <pre>
                                        {%foreach from=$method.tags key=key item=mtag%}
                                            {%if $mtag.tag =='error' %}
                                                <div>name:{%$mtag.errno%}</div>
                                                <div>desc:{%$mtag.desc%}</div>
                                            {%/if%} 
                                        {%/foreach%}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    {%/foreach %}
                </div>
            </div>
        </div>
    </div>
</body>
</html>