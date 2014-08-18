<?php
$__counter=0;
function xcopy($src, $trg, $oper){
    global $__counter;
    if(!is_dir($trg))
        mkdir($trg);
    if($oper=="exact"){
        if($direc=@opendir($trg)){
            while(($file=readdir($direc))!==false){
                if(is_file($trg."/".$file)){
                    if(!file_exists($src."/".$file)){
                        @unlink($trg."/".$file);
                    }
                }
            }
        }
        @closedir($direc);
    }
    if($direc=@opendir($src)){
        while(($file=readdir($direc))!==false){
            if($file!="." && $file!=".."){
                if(is_file($src."/".$file)){
                    $copia=false;
                    if(!file_exists($trg."/".$file))
                        $copia=true;
                    elseif(filemtime($src."/".$file)!=filemtime($trg."/".$file))
                        $copia=true;
                    elseif(filesize($src."/".$file)!=filesize($trg."/".$file))
                        $copia=true;
                    else
                        $copia=false;
                
                    if($copia){
                        $__counter+=1;
                        if($oper!="counter"){
                            print str_repeat("X", 1000);
                            flush();
                            copy($src."/".$file, $trg."/".$file);
                            chmod ($trg."/".$file, 0755);
                            touch($trg."/".$file,filemtime($src."/".$file));
                        }
                    }
                }
                elseif(is_dir($src."/".$file)){
                    xcopy($src."/".$file, $trg."/".$file, $oper);
                }
            }
        }
    }
    @closedir($direc);
    return $__counter;
}
?>