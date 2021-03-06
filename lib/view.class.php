<?php

class View{
    private $template;
    public static function addStuff($content,$path){
        $nav=static::makeNav($path);
        $title=static::makeTitle($path);
        $type=$path->getType();
        switch($type){
        case 0:
            //DIRECTORY
            $viewType='';
            $text='<ul class="fm'.$viewType.'">';
            if(count($path->getPath())>0){
                //Display .. if not root dir
                $link=wiki::makeLink($path,count($path->getPath())-1);
                $text.='<li class="jump"><a href="'.$link.'">..</a></li>';
            }
            for($i=0;$i<count($content);$i++){
                $obj=$content[$i];
                $tp=$obj->getTypeString();
                if($obj->hasListStyle()){
                    $text.='<li class="'.$tp.'">'.$obj->getListStyle().'</li>';
                }else{
                    $text.='<li class="'.$tp.'"><span></span><a href="'.wiki::makeLink($obj).'">'.$obj->getName().((SHOW_EXTENSIONS)?$obj->getFileType():'').'</a></li>';
                }
            }
            $text.='</ul>';
            break;
        case 1:
            //FILE
            $ext = $path->getFileType();
            $pathString=$path->getTypeString();
            if($path->hasDetails()){
                if($pathString!=='unknown'){
                    if($path->usesCache()){
                        $fullPath=$path->getFullString(true);
                        if(Cache::wasCached($fullPath,$path->getCacheTimeout())){
                            #wurde gecached
                            ob_start();
                            include(Cache::getCachedFile($fullPath));
                            $text=ob_get_contents();
                            ob_end_clean();
                        }else{
                            $text=$path->getDetailCode();
                            Cache::cacheFile($fullPath,$text);
                        }
                    }else{
                        $text=$path->getDetailCode();
                        
                    }
                    $text.=$path->getExtension()->getDownloadElement($path);
                }else{
                    $text=$content;
                }
            }else{
                $text="<h1>You aren't allowed to view this file</h1>";
            }
            break;
        }
        return static::parseView($nav,$title,$text);
    }
    public static function loadTemplate(){
        include(LIB.'page.php');
        return $template;
    }	
    private static function makeTitle($path){
        switch($path->getType()){
        case 0:
            $pathArr=$path->getPath();
            $pathCount=count($pathArr)-1;
            if($pathCount>=0){
                $title=$pathArr[$pathCount];
            }else{
                $title="home";
            }
            break;
        case 1:
            $title=$path->getName();
            break;
        }
        return $title;
    }
    private static function makeNav($string){
        if(count($string)>0){
            $belowMax=true;
            $pathArray=array();
            $path=$string->getPath();
            $pathCount=count($path);
            $links='<li><a href="'.ROOT_FILE.'">home</a></li>';
            if($pathCount<MAX_NAV)$i=0;
            else{
                $i=$pathCount-MAX_NAV;
                $belowMax=false;
                $links.='<li><a>...</a></li>';
            }
            for($i;$i<$pathCount;$i++){
                $fullName=$path[$i];
                $name=$fullName;
                if(strlen($name)>SHORTEN_NAV){
                    $name=substr($fullName, 0,SHORTEN_NAV-3)."...";
                }
                $links.='<li><a title="'.$fullName.'" href="'.wiki::makeLink($string,$i+1).'">'.$name."</a></li>";
            }
            if(strlen($string->getName())>0)$links.='<li class="unknown"><a>'.$string->getName()."</a></li>\n";
            return $links;
        }else die("No path string");
    }
    public static function parseView($nav,$title,$content){
        $template=static::loadTemplate();
        $template=str_replace('%%NAVIGATION%%',$nav,$template);
        $template=str_replace('%%TEXT%%',$content,$template);
        $template=str_replace('%%PAGETITLE%%',$title,$template);
        $template=str_replace('%%DOCTITLE%%',DOC_TITLE,$template);
        $template=str_replace('%%STYLE%%',STYLE,$template);
        $template=str_replace('%%SCRIPT%%',SCRIPT,$template);
        $template=str_replace('%%THEME%%',THEME,$template);
        $template=str_replace('%%HEAD%%',Layout::getBakedHead(),$template);
        return $template;
    }
}
?>
