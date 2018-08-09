<!DOCTYPE html>
<html>
<head>
    <title>文档导航</title>
</head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="http://www.oneniceapp.com/js/lib/bootstrap-3.3/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="http://www.oneniceapp.com/js/lib/jquery-easyui-1.4.1/themes/metro-gray/easyui.css"/>
<script type="text/javascript" src='http://www.oneniceapp.com/js/lib/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='http://www.oneniceapp.com/js/lib/bootstrap-3.3/js/bootstrap.min.js'></script>
<script type="text/javascript" src='http://www.oneniceapp.com/js/lib/jquery-easyui-1.4.1/jquery.easyui.min.js'></script>
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
    .navbar-default .navbar-nav>li>a{
        color:#333;
    }
    pre{ overflow: hidden; }
</style>
<?php
    $dir = __DIR__ ;
    $trees = recurDir($dir);
    
?>
<script>
     var treeData = <?php echo  json_encode(outData($trees)); ?> ;
     $(function(){
         $('#tt').tree({
            data:treeData,
            lines:true,
            formatter:function(node){
                        var s = node.text;
                        if (node.children){
                            s += '&nbsp;<span style=\'color:blue\'>(' + node.children.length + ')</span>';
                        }
                        return s;
            },
            onClick:function(node){
                if(node.children ==undefined){

                    var text =  node.text;
                    var path =  text.split(".");
                    var urlArr =  path[0].split("-");
                    urlArr.pop();
                    window.location.href  =  urlArr.join("/")+"/"+text;
                }
            }
         });
     });
</script>
<body>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-6">
          <ul class="nav navbar-nav" style='margin-left:50px'>
             <li><a style='font-size:20px;color:#333'>ice文档</a></li>
          </ul>
        </div>
    </div>
</nav>
    <div class="container bs-docs-container" style='margin-top:60px;'>
        <div class="row">
            <div class="col-md-12">
                <div class="easyui-panel" style="padding:5px">
                     <ul id='tt'></ul>
                </div>

<?php
/**
 递归输出文档
*/
function outData($trees){
    $str ="";
    $treeList =[];
    foreach ($trees as $key=>$groupTree){
      if(is_array($groupTree) && !empty($groupTree)){
         $one = array();
         $one['id'] = mt_rand(1,50000);
         $one['text'] = $key;
         $one['children'] = outData($groupTree);
      }else{
         $one = array();
         $one['id'] = mt_rand(1,50000);
         $one['text'] = $groupTree;
      }
      $treeList[] = $one;
   }

   return $treeList;
}

/**
  递归获取目录  
*/
function recurDir($pathName) {
    $result = array();
    $temp = array();
    if( !is_dir($pathName) || !is_readable($pathName) ){
        return null;
    }
    $allFiles = scandir($pathName);
    foreach($allFiles as $fileName){
        if( in_array($fileName, array('.', '..','index.php')) ) continue;
        $fullName = $pathName . '/' . $fileName;
        if( is_dir($fullName) ){
            $result[$fileName] = recurDir($fullName);
        }else{
            $temp[] = $fileName;
        }
    }
    if($temp){
        foreach( $temp as $f ){
            $result[] = $f;
        }
    }
    return $result;
}

   
?>
            </div>
             
        </div>
    </div>
</body>
</html>
