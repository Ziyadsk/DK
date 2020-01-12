<?php 
include("php.php");
class Translator {

        public function __construct(){}
        
        static private function replace_DK_KEYWORDS($keyword) {
            switch($keyword){
                case 'fn':
                    return "function";
                break ; 
                case 'pub':
                    return "public";
                break; 
                default:
                    return $keyword;
            }
        
        }

        public static function translate($file_name,$destination) { 
            $file_name_path = $file_name ; 
            // the rendered file 
            $processed_file = [];
    
            $multiline_comments_array = [];

            $file = file_get_contents($file_name);
            $multiline_comments_regex = "/\/\*.*\*\//s";
            preg_match_all($multiline_comments_regex,$file,$multiline_comments);
            $multiline_comments_array = explode("\n",implode("",$multiline_comments[0]));
            
            print("Translating : $file_name\n");
            $line_number = 0 ; 
            $file = explode("\n",$file);
           
            // walk the file per line
            foreach($file as $k => &$line){
                $line_number++ ;
                if(in_array($line,$multiline_comments_array)){
                    $line .= "\n";
                }
                else {
                    ltrim($line);
                    if(self::process_class_exp($line)){
                       self::process_interface_exp($file[$k+1]) ;
                    }
                    self::process_for_loops($line); 
                    self::add_sc($line);
                    self::process_variables($line);
                    
                   
                }
                
                array_push($processed_file,trim($line) . "\n");
            }

        array_unshift($processed_file,"<?php" . PHP_EOL);
        array_push($processed_file,"?>"); 
        
        $processed_file = implode("",$processed_file);
            
        $file_name_path = mb_substr($file_name_path,0,-3);
        $file_name_path = @end(explode("/",$file_name_path));
        print($destination);
        if($destination){
            $php_file = fopen("$destination" . "/" . "$file_name_path.php", 'w');
        }else {
            $php_file = fopen("$file_name_path.php", 'w');
        }
     
        fwrite($php_file,$processed_file);
        fclose($php_file);
        
        }
        
        static private function process_class_exp(&$line) {
             // check for class declaration
             $class_regex = "/class.*/";

             preg_match($class_regex,$line,$class_found);
             if($class_found) {
                if(strpos($class_found[0],":")){
                     $line = str_replace(":","extends",$line);
                 }
                 if(strpos($class_found[0],"[") 
                     && strpos($class_found[0],"]")) {
                     
                    $to_evaluate = explode("[",$class_found[0])[1];
                    $to_evaluate = explode("]",$to_evaluate)[0];
                    $line = preg_replace("/\]/","",$line);
                    $line = preg_replace("/\[/","",$line);
                    $line = str_replace($to_evaluate,"implements $to_evaluate", $line);
                 }
                return true; 
            }
            else {
                return false ;
            }
            
        }

        static private function process_interface_exp(&$line){
             // check for class declaration
             $interface_regex = "/\[.*\]/";
             preg_match($interface_regex,$line,$interface_found);
             if($interface_found) {
                if(strpos($interface_found[0],"[") !== false  
                     && strpos($interface_found[0],"]") !== false ) {

                    $to_evaluate = explode("[",$interface_found[0])[1];
                    $to_evaluate = explode("]",$to_evaluate)[0];
                    $line = preg_replace("/\]/","",$line);
                    $line = preg_replace("/\[/","",$line);
                    $line = str_replace($to_evaluate,"implements $to_evaluate", $line);
                }
            }
        }
        static public function process_for_loops(&$line){
            $loop_regex = '/for\(.+\)/' ;
            $range_loop_regex = '/for\s*\(.*\{.*\}.*\)/';
            
            $replaced_string = "";
            $evaluated_expression = "";

            preg_match($loop_regex,$line,$for_loop_exp);
            preg_match($range_loop_regex,$line,$range_loop_exp);

             // ranges
             if($range_loop_exp) {
                 
                $replaced_string = trim(explode(")",$range_loop_exp[0])[0]);
                
                $range_string = "[" ;

                $to_evaluate = trim(explode(")",$range_loop_exp[0])[0]);
                $to_evaluate = explode("for",$to_evaluate);
                $to_evaluate = $to_evaluate[1];
                $to_evaluate = ltrim($to_evaluate,"(");
                $list = trim(explode("in",$to_evaluate)[1]);
                $element = trim(explode("in",$to_evaluate)[0]);
                $list = ltrim($list,"{");
                $list = rtrim($list,"}");
                $list = explode("..",$list);
                
                for($i=$list[0];$i<=$list[1];$i++){
                    $range_string .= $i . ","; 
                }
                $range_string = rtrim($range_string,",");
                $range_string .= "]";
                
                    $evaluated_expression = "foreach($range_string as $element";
                

            }
            $line = str_replace($replaced_string,$evaluated_expression,$line);

            if($for_loop_exp) {
                $replaced_string = explode(")",$for_loop_exp[0])[0];
                $to_evaluate = explode("for",$for_loop_exp[0]);
                $to_evaluate = $to_evaluate[1];
                $to_evaluate = explode(")",$to_evaluate);
                $to_evaluate = $to_evaluate[0];
                $to_evaluate = ltrim($to_evaluate,"(");
                $list = explode("in",$to_evaluate)[1];
                $element = explode("in",$to_evaluate)[0];
                print_r($to_evaluate);
                    $evaluated_expression = "foreach($list as $$element";
                    $evaluated_expression = "foreach($list as $$element";
                

                $line = str_replace($replaced_string,$evaluated_expression ,$line);
            }

            $list_comp_regex = '/\[\s*.+\s+for\s+\w+\s+in\s+.+\s*\]/'; 
            preg_match($list_comp_regex,$line,$list_comp);
                   if($list_comp){
                    $l = "" ;
                    // $varibale is the the new array 
                    $variable = explode("=",$line)[0];
                    $variable = trim($variable);
                   
                    $to_evaluate = $list_comp[0];
                    $list = @end(explode("in",$to_evaluate)) ; 
                    // $list is the targeted array
                    $list = rtrim($list,"]");
                    // result wanted 
                    $result_wanted = explode("for",$to_evaluate)[0] ; 
                    $result_wanted = ltrim($result_wanted,"[");
                    print($result_wanted);
                    // element targeted
                    $element_of_array = trim(explode("in",explode("for",$to_evaluate)[1])[0]);
                  
                    // $to_evaluate = '' . "$variable" .  "=[];\n".
                    // 'foreach('. $list .' as l) {'.
                    // 'tmp = '. "'$result_wanted';" .
                    // 'tmp = str_replace(' ."$element_of_array".',l,'."'$result_wanted'".'); '.
                    // "array_push($variable,tmp);" . 'tmp = \'\' ' . '}'; 

                    // print("LIST COMP +> " . $to_evaluate . PHP_EOL);
                    // $line = str_replace($line,$to_evaluate,$line);
              
         
            }
        }
               
      
    
        static public function find_keywords($line){
            // full keywords count
            $KEYWORD_COUNT = 0 ;
            
            $words = explode(" ",$line);
            if(array_intersect(PHP::$KEYWORDS,$words)){
                $KEYWORD_COUNT++ ; 
            }
            
            return  "Found $KEYWORD_COUNT keywords ." . PHP_EOL ; 
        } 
        
        static public function find_symbols($line){
            
            // SYMBOLS_FOUND In the line
            $SYMBOLS_FOUND = [] ; 
            $SYMBOLS_COUNT = 0 ; 
            $Z = [] ; 
            foreach(str_split($line) as $char) {

                if(in_array($char,PHP::$SYMBOLS)){
                    $SYMBOLS_COUNT++ ; 
                    array_push($SYMBOLS_FOUND,$char);
                    $key = array_search($char,PHP::$SYMBOLS);
                    array_push($Z,$key);
                }
            }
            return ["SYMBOLS ($SYMBOLS_COUNT)" , $SYMBOLS_FOUND   , $Z]; 
        
        } 
        
        // find functions    
        static public function process_function(&$line) {
            $regex = "/(?!_|\d)\b\w+[0-9]*\([a-z]*\)?/" ;
            preg_match($regex,$line,$function_found);
            if($function_found){
                $function_found_name = explode("(",$function_found[0])[0];
                if(in_array($function_found_name,PHP::$KEYWORDS)){
                    $function_found_name .= "(KEYWORD)" . PHP_EOL;
                }
          
            }
        }   

     
        // Process semicolons
        static public function add_sc(&$line) {
            
            if(substr(rtrim($line),  -1) == PHP::$SYMBOLS["LEFT_CURLY_BRACE"] || substr(rtrim($line),  -1) == PHP::$SYMBOLS["RIGHT_CURLY_BRACE"] ){
                $line[strlen($line)] = PHP_EOL ;
            }
            else if(self::process_class_exp($line) !== false){}
            elseif(strpos($line,"implements") !== false ){}
            elseif(strlen(trim($line)) == 0 ){}
            else if(trim($line)[0]=='/' && trim($line)[1] == '/'){ $line[strlen($line)] = PHP_EOL ;}
            else {
                $line[strlen($line)] = ";" ;
                $line[strlen($line)+ 1] = PHP_EOL ;
            }
       
        }  
        
        // process the variables 
        static public function process_variables(&$line) {
            
            $regex = "/\b[a-zA-Z_]+\b(?![\w\(+]|::)/" ;
            // check the line for variables
            preg_match_all($regex,$line,$variable_found);

            // check for comments 
            $comments_regex = "/\/\/[a-z-A-Z ]+/";
            preg_match_all($comments_regex,$line,$comments);
            
            if($comments[0]){}
            else if(self::process_class_exp($line) !== false){}
            else if(strpos($line,"implements") !== false ){}
            else if ($variable_found) {
                // $var is every word found on a line
                foreach($variable_found[0] as $var) {
                    // regex to find string
                    preg_match_all("/\".+\"/",$line,$var_in_string);
                    $string_words = []; 
                    foreach($var_in_string[0] as $var_str){
                        if(strpos($var_str,$var)!== false){
                            array_push($string_words,$var);
                        }
                    }
                   // replace keywrods if not inside a string 
                   if(!in_array($var,$string_words)){
                        $line = str_replace($var,self::replace_DK_KEYWORDS($var),$line);
                   }
               

                    // check if not type,keyword or string
                    if(!in_array($var,$string_words) &&
                        !in_array($var,PHP::$KEYWORDS) 
                        && !in_array($var,PHP::$TYPES)){
                                                        
                            if(strpos($line,"$$var")){}
                            else {
                                $line = preg_replace("/\b$var\b/s","$$var",$line) ;
                            }
                        }
                    
                    // delete types
                    else if(in_array($var,PHP::$TYPES) && !in_array($var,$string_words)){
                        $line = str_replace($var,"" ,$line); 
                    }
                   
                }
            }
           

        }
}


?>