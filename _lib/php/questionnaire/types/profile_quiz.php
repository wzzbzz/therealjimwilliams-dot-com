<?php


class profile_quiz extends questionnaire{

	private $answers = array();

	public function __construct($xmldoc){
		return parent::__construct($xmldoc);
	}

	public function load_xml($xml){
		if(! is_a($xml, "SimpleXMLElement"))
                        return false;
        parent::load_xml($xml);
		foreach($xml->profiles->profile as $profile_xml){
			$profile = new profile($profile_xml);
			$this->add_profile($profile);
		}
		foreach($xml->questions->question as $question_xml){
			
			$question = new question($question_xml);
			$this->add_question($question);
		}

		if (count($_POST)){
			foreach($_POST as $question=>$answer){
				$qnum = intval(str_replace("question_","",$question));
				$this->answers[$qnum] = $answer;
			}
		}
	}


	public function score($answers){

		$tally = array();

		foreach($answers as $idx=>$answer){

			$reply = $this->get_question($idx)->get_reply($answer);
			$question = $this->get_question($idx);
			$correlations = $reply->get_correlations();
			foreach($reply->get_correlations() as $correlation){
				$tally[$correlation->id()]++;
			}
		}
		$profiles = array_keys($tally, max($tally));

		// pick a random for now
		$profile =  $profiles[rand(0,count($profiles)-1)];

		return $profile;
		
	}

	public function render(){
		?>
		<div class="contain-yourself">
			<?php echo $this->render_ad();?>
			<?php
			if (count($this->answers)){;
				$profile = $this->get_profile( $this->score($this->answers) );
			?>
			<div id="results-data" data-name = "I'm <?php echo htmlspecialchars($profile->name());?>!" data-picture="<?php echo $profile->image();?>" data-description = "<?php echo htmlspecialchars(strip_tags($profile->description()));?>" data-caption="<?php echo $this->title();?>"></div>
				<h2 style="text-align:center;">You're <?php echo $profile->name()?>!</h2>

				<img style="margin:auto;display:block;" src="<?php echo $profile->image();?>" width="480">
				<p><?php echo $profile->description();?></p>
				<div id="socialwrap" class="clearfix">
					<div style="float:left;"><a href=''>Try again?</a></div>
					<div style="float:right;"?><?php $this->render_social();?></div>
				</div>
				<div>READ ABOUT MORE CULTS AND THEIR LEADERS IN THIS BOOK:</div>
				<?php echo $this->render_ad();?>
			<?php
			}
			else{
			?>
			<img src="<?php echo $this->prompt()->image();?>" width="50%" style="margin:auto;display:block;" />
			<p><?php echo $this->prompt()->text();?></p>
			<div id="titlebar">
			<h2 style="font-size:24px;"><?php echo $this->title();?></h2>
			<h3 style="text-align:left;font-size:12px;margin-top:-25px;">by <?php echo $this->author();?></h3>
			</div>
			<div id="form-contain">
				<form id="the-form" action = "" method = "post">
					<?php foreach($this->get_questions() as $i=>$question):?>
					<div class="question">
						<div class="prompt"><span><?php echo $i+1;?>. </span><span><?php echo $question->prompt()->text();?></span></div>
						<?php foreach($question->get_replies() as $j=>$reply):?>
							<div class="reply"><input type="radio" name="question_<?php echo $i;?>" value="<?php echo $j;?>"  <?php if ($i==0):?>checked<?php endif;?>?><span><?php echo $reply->prompt()->text();?></span></div>
						<?php endforeach;?>
					</div>
					<?php endforeach;?>
					<div style="text-align:center;"><a id="submit" href="">SUBMIT</a></div>
				</form>
				<?php } ?>
			</div> <!--form-contain-->
		</div>
		<?
	}

	public function render_head(){
		parent::render_head();
	?>
		<meta property="og:url" content="<?php echo $this->url();?>" />
		<meta property="og:title" content="<?php echo $this->title();?>" />
		<meta property="og:description" content="<?php echo $this->prompt()->text();?>" />
		<meta property="og:image" content="<?php echo $this->prompt()->image();?>" />
<?php
	}
	
	public function render_ad(){
		$str = '
		<div class="ad banner">
			<div id="ad-image"><a href="'.$this->ad()->url().'" target="_blank"><img src="' . $this->ad()->prompt()->image() . '"/></a></div>
		</div>';
		return $str;
	}

}


class profile{
	private $id;
	private $name;
	private $description;
	private $image;

	public function __construct($args = null){
		if (is_a($args,"SimpleXMLElement")){
            $this->id = $args->id->__toString();
            $this->name = $args->name->__toString();
			$this->description = $args->description->__toString();
			$this->image = $args->image->__toString();
        }
	}
	
	public function id($id = null){
		if ($id==null)
			return $this->id;
		else
			$this->id = $id;
	}

	public function name($name = null){
		if ($name == null)
			return $this->name;
		else
			$this->name = $name;
	}

	public function description($desc = null){
		if ($desc == null)
			return $this->description;
		else
			$this->description = $desc;
	}

	public function image($image = null){
		if ($image == null)
			return $this->image;
		else
			$this->image = $image;
	}
	
}

class reply{
	private $prompt;
	private $correlations; 

	public function __construct($args){
		if (is_a($args,"SimpleXMLElement")){
            $this->prompt = new prompt($args->prompt);
             foreach ($args->correlations->correlation as $correlation_xml){
            	$this->correlations[] = new correlation($correlation_xml);
            }
        }
	}

	public function prompt($prompt = null){
        if(null == $prompt)
            return $this->prompt;
        else
            $this->prompt = $prompt;
    }

	public function add_correlation($correlation){
		$this->correlations[] = $correlation;
	}

	public function get_correlations(){
		return $this->correlations;
	}
}

class correlation{
	private $id;

	public function __construct($args){
		if (is_a($args,"SimpleXMLElement")){
			$this->id = $args->__toString();
        }
	
	}

	public function id($id=null){
		if ($null == $id)
			return $this->id;
		else
			$this->id = $id;
	}
	
}