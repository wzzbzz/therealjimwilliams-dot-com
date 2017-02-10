<?php

class rant extends command{
    
    public function _execute($args){
        
        $db = _db();
        $logged = $db->insert(array('table'=>'visitors','keyvals'=>array("domain"=>'rant', "ip"=>$_SERVER['REMOTE_ADDR'],'stamp'=>date('Y-m-d H:i:s',time()))));
        
        $info = "<div style='width:500px;'>
        <p>
        PRISM was not only a revelation of the US Governments desire to be present in all places of discourse, thus creating an impossibility of revolt,  but also a display of the complicity and unstrustworthiness of the major information / privacy vendors.<br><br>
        As you should not be surprised that the government is keyword scanning your emails, also do not be surprised if they have not installed remote control devices in your computers via Apple and Windows OS updates.  There are programs that report 'marked' files and report them to home base when networks are open.  The fact that the first nightmare is true implies that the nature of the beast is as such.  <br> <b>How do I know this?</b> <br> Because the question isn't CAN they do it, but would they.  Of course they CAn, you just need Apple or Windows or any software maker to strike a deal with you.  But would they?  Well as we now know, and everyone admits, they would.  So if they can, and they would, It's pretty safe to say they did.
    
      </p>
      <hr>
      <p>
        Is anyone else disturbed when Facebook shows you an advertisement under the shell that someone you know likes a product?<br><br>My friend Pam Walsh has been deceased for quite some time, but Facebook keeps telling me she likes <b>TRIDENT&trade;</b>
        </p>
          </div>
        ";

$info = "rants disabled.";
        $result['action'] = "OUTPUT";
        $result['data'] = $info;

        return $result;
        
    }
}

?>
