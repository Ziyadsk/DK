<?php 

class Checker {
    public function __construct() {}
    public static function check($file_name) {
        $file = file($file_name);
        foreach($file as &$line){
            // if(strpos("=",$line) !== false ) || strpos(,$line) !== false ) 
            // {
            //         $line_array = explode(" ");
            // }
           
        }
    }

}

?>