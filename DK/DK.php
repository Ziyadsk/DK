<?php

include("translator.php");
include("checker.php");

class DK {

    public function __construct($file_or_dir_name,$destination) {
        Checker::check($file_or_dir_name);
        Translator::translate($file_or_dir_name,$destination);
    }
}

(new DK($argv[1],$argv[2] ?? null))

?>