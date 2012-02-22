<?php

class View{
	private $template;
	public static function addStuff($content,$path){
		$nav=static::makeNav($path);
		$title=static::makeTitle($path);
		$type=$path->getType();
		switch($type){
			case 0:
				$text='<ul class="fm">';
				for($i=0;$i<count($content);$i++){
					$obj=$content[$i];
					$tp=$obj->getTypeString();
					$text.='<li class="'.$tp.'"><a href="'.wiki::makeLink($obj).'">'.$obj->getName().'</a></li>';
				}
				$text.='</ul>';
				break;
			case 1:
				//Change this line if you want to modify the parser
				$text=Markdown($content);
				$text='<b class="center">'.$path->getName().'</b>'.$text;
				break;
		}
		return static::parseView($nav,$type,$title,$text);
	}
	public static function loadTemplate(){
		include('page.php');
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
				$links.='<li>...</li>';
			}
			for($i;$i<$pathCount;$i++){
				$fullName=$path[$i];
				$name=$fullName;
				if(strlen($name)>SHORTEN_NAV&&!$belowMax){
					$name=substr($fullName, 0,SHORTEN_NAV-3)."...";
				}
				$links.='<li><a title="'.$fullName.'" href="'.wiki::makeLink($string,$i+1).'">'.$name."</a></li>";
			}
			if(strlen($string->getName())>0)$links.='<li class="file">'.$string->getName()."</li>\n";
			return $links;
		}else die("No path string");
	}
	public static function parseView($nav,$type,$title,$content){
		$template=static::loadTemplate();
		if($type==1){
			$template=str_replace('%%NAVIGATION%%',$nav,$template);
			$template=str_replace('%%TEXT%%',$content,$template);
			$template=str_replace('%%PAGETITLE%%',$title,$template);
		}elseif($type==0){
			$template=str_replace('%%NAVIGATION%%',$nav,$template);
			$template=str_replace('%%TEXT%%',$content,$template);
			$template=str_replace('%%PAGETITLE%%',$title,$template);
		}
		return $template;
	}
}
?>