<?php
require_once(BASEPATH."/_classes/commander/command.php");

class fap extends command{
    
    
    public function _execute($args){
        $result = array();
        $result['action'] = "OUTPUT";
        if (count($args)==0)
        {
            $data = "TO WHOM WOULD YOU LIKE TO FAP?";
        }
        else{
            $girl = array_shift($args);
            $handle = fopen(BASEPATH."/files/$girl.txt", 'r');
            $data = "<div class='smalltext'>";
            if ($handle) {
                while (($buffer = fgets($handle)) !== false) {
                    $data .= str_replace("\n", "<br>", str_replace(" ","&nbsp;",$buffer));
                }
                if (!feof($handle)) {
                    echo "Error: unexpected fgets() fail\n";
                }
                fclose($handle);
            }
            $data .= "</div>";
        }
        $result['data']=$data;
        return $result;
        }
}
?>