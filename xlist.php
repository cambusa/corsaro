<?php
$list=array();
if($direc=@opendir(".")){
    while(($file=readdir($direc))!==false){
        if($file!="." && $file!=".." && $file!="_master"){
            if(is_dir("./".$file)){
                $list[]=$file;
            }
        }
    }
}
@closedir($direc);
print implode("|", $list);
?>