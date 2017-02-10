<?php

class about extends command{
    
    public function _execute($args){
        
        $db = _db();
        $logged = $db->insert(array('table'=>'visitors','keyvals'=>array("domain"=>'rant', "ip"=>$_SERVER['REMOTE_ADDR'],'stamp'=>date('Y-m-d H:i:s',time()))));
        
        $info = "***I've read alot more personal opinions on this topic than any other.  Many many people have said these things way better, funnier, and more concisely than I have.***        
        The more i think of it, the more this story stinks like pure garbage.  Like all the worst
        stories there should be no story at all.  <br><br>In a world where this commercial
        runs on prime time:<a href='http://www.youtube.com/watch?v=Z7fz0jpuLkM' target='_blank'> http://www.youtube.com/watch?v=Z7fz0jpuLkM</a>,
        the meatball shop in nyc's LES (deeeeelicious by the way.  get them over the mashed potatoes)
        waitress says \"Would you like me to wrap your balls\" when you ask for a doggie bag, and in general
        innuendo and sexual inference is heavily engrained in the daily parlance of the common man, to view such joking that these two participated in as
        anything offensive or sexist at all is ludicrous.  Or to even infer from this that people in tech are somehow more base, crude, or perverted than the average person is crazy<br><br>However,
        Aria Richard's opinion is <span style='color:green'>>>>valid</span>.
        Villifying her for this is completely wrong and I can't participate in a culture that limits free expression.<br><br>
        The real villain here is the individual that fired an employee based off of a tweet, reacting in <span style='color:red'>cowardice</span> and fear of the media shitstorm brewing, and which first fired the coders for
        an unsubstantiated report<br>
        And then also the company firing Aria Richards responding in kind, falling to the pressure of the anonymous roving hordes of DOS attakcers (of Reavers from Firefly, they remind me) to take the tech side....what on earth is going on here?<br><br>
        This is not a complete thought. <br><br>
        However that Google Plus Guy with the Long Hair's post was ridiculous.<br>
        
        <br><br>Academically, as a study in the exponentially exacerbating effects of a net-based media, this is as perfect a studay as you can get.<br><br>
        for example, from now on, these people have become entwined - for the foreseeable future, as:<br><br>
        <img src='donglebros.png'><br><br>
        <img src='aria.png' /><br>
        <br>I'd be interested to talk to them in a year and see how their lives were impacted by all of this.
        
          <br>
          <br>
          I wonder if someone could get them together in the same room to confront each other.<br><br>
        My old screed is still here, and can be decoded on <a href='http://www.cryptstack.com' target='_blank'>crypstack.com</a> with #dongle<br>
        ";
        $screed = "<p>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</p>";
        
        $result['action'] = "OUTPUT";
        $result['data'] = var_dump($args);//$info.$screed;
        return $result;
        
    }
}

?>