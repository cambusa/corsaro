<?php 
/****************************************************************************
* Name:            ryupolad.php                                             *
* Project:         Cambusa/ryUpload                                         *
* Version:         1.00                                                     *
* Description:     File uploader                                            *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");

if(isset($_POST["env"]))
    $env_name=strtolower($_POST["env"]);
elseif(isset($_GET["env"]))
    $env_name=strtolower($_GET["env"]);
else
    $env_name="";

if($env_name!=""){
    if(is_file($path_databases."_environs/".$env_name.".php")){
        include($path_databases."_environs/".$env_name.".php");
        $allowedExtensions=explode("|",$env_extensions);
        $uploader=new qqFileUploader($allowedExtensions, $env_maxsize);
        $result=$uploader->handleUpload($env_strconn);
    }
    else{
        $result=array('error'=>'Environ '.$env_name.' is not defined.');
    }
}
else{
    $result=array('error'=>'No folder specified.');
}

print htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    
class qqUploadedFileXhr {
    /**
    * Save the file to the specified path
    * @return boolean TRUE on success
    */
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){
            return false;
        }
        
        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }
}

/**
* Handle file uploads via regular form post (uses the $_FILES array)
*/
class qqUploadedFileForm {
    /**
    * Save the file to the specified path
    * @return boolean TRUE on success
    */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    private $uploadName;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false;
        }
    }
    
    public function getUploadName(){
    if( isset( $this->uploadName ) )
    return $this->uploadName;
    }
    
    public function getName(){
    if ($this->file)
    return $this->file->getName();
    }
    
    private function checkServerSettings(){
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }
    
    /**
    * Returns array('success'=>true) or array('error'=>'error message')
    */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        global $env_baseurl;
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = @$pathinfo['extension']; // hide notices if extension is empty

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        $ext = ($ext == '') ? $ext : '.' . $ext;
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        $this->uploadName = $filename . $ext;

        if ($this->file->save($uploadDirectory . $filename . $ext)){
            return array('success'=>true,'url'=>$env_baseurl.$filename.$ext);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }
}
?>