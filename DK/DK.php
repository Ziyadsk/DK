<?php

include("translator.php");
include("checker.php");

class DK {

    public function __construct($file_or_dir_name,$destination) {
        if(is_dir($file_or_dir_name)){
            
        }else{
            Translator::translate($file_or_dir_name,$destination);
        }
        
        
    }
}



?>