<?php

class questionnaire{
	private $title;
	private $author;
	private $types;
	private $prompt;
	private $profiles;
	private $questions;
	private $url;
	private $ad;
	private $db;

	public function __construct($args = null){
		$this->title = "";
		$this->url = "";
		$this->types = array();
		$this->prompt = new prompt();
		$this->profiles = array();
		$this->questions = array();
		if(is_a($args,"SimpleXMLElement"))
			$this->load_xml($args);
		else
			die("no");
		
	}

	public function xml_create($xmldoc){
		if(!is_a($xmldoc,"SimpleXMLElement")){
			return false;
		}
		$class = $xmldoc->class->__toString();
		require_once(QUESTIONNAIRE_BASEPATH . "types/".$class.".php");
		return new $class($xmldoc);
	}

	public function title($title = null){
		if (null==$title)
			return $this->title;
		else
			$this->title = $title;
	}

	public function url($url = null){
		if (null==$url)
			return $this->url;
		else
			$this->url = $url;
	}

	public function author($author = null){
		if (null==$author)
			return $this->author;
		else
			$this->author = $author;
	}

	public function get_types(){
		return $this->types;
	}

	public function set_types($types){
		$this->types = $types;
	}

	public function add_type($type){
		$this->types[] = $type;
	}
	
	public function add_profile($profile){
		$this->profiles[$profile->id()] = $profile;
	}

	public function get_profiles(){
		return $this->profiles;
	}

	public function get_profile($id){
		return $this->profiles[$id];
	}

	public function add_question($question){
		$this->questions[] = $question;
	}

	public function get_questions(){
		return $this->questions;
	}

	public function get_question($idx = 0){
		return $this->questions[$idx];
	}

	public function load_xml($xml){
		if(! is_a($xml, "SimpleXMLElement"))
                        return false;
		
	    $this->url($xml->url->__toString());
		$this->title(filterMS($xml->title->__toString()));
		$this->author(filterMS($xml->author->__toString()));
		$this->prompt = new prompt($xml->prompt);
		$this->ad = new ad($xml->ad);
		$this->db = new db($xml->db);
	}

	public function prompt($prompt = null){
		if(null == $prompt)
			return $this->prompt;
		else
			$this->prompt = $prompt;
	}
	
	public function render_social(){
		?>
		<div>
			<a class="share-btn">Share</a>
		</div>
		<?
	}
	
	public function render_meta(){
		?>
		<meta property="og:title" content="<?php echo $this->title(); ?>" />
		<?
	}
	
	public function render_head(){
		?>
		<title><?php echo $this->title();?></title>
		<?
	}


	public function render_ad(){
		$str = '
		<div class="ad banner">
			<div id="ad-copy">' . $this->ad()->prompt()->text() .'</div>
			<div id="ad-image"><img width = "480" src="' . $this->ad()->prompt()->image() . '" /></div>
		</div>';
		return $str;
	}

	public function insert_ad($str){
		$pos = strrpos($str, " ",-1*strlen($str)/2);
		$pre = substr($str,0,$pos);
		$post = substr($str,$pos,strlen($str));
	    $ad = '<div style="float:right;width:150px;">';
	    $ad .=  $this->render_ad();
	    $ad .= "</div>";
	    return $pre.$ad.$post;

	}

	public function ad(){
		return $this->ad;
	}

}

class prompt{

	private $text;
	private $image;

	public function __construct($args = null){
		if (is_a($args,"SimpleXMLElement")){


			$this->text = filterMS($args->text->__toString());
			$this->image = filterMS($args->image->__toString());
		}
	}

	public function text($text = null){
		if (null==$text)
			return $this->text;
		else
			$this->text = $text;
	}

	public function image($image=null){
		if (null == $image)
			return $this->image;
		else
			$this->image = $image;
	}

}


class question{
	private $prompt;
	private $replies;

	public function __construct($args = null){
		if (is_a($args,"SimpleXMLElement")){
            $this->prompt = new prompt($args->prompt);
            foreach ($args->replies->reply as $reply_xml){
            	$this->replies[] = new reply($reply_xml);
            }
        }
	}

	public function prompt($prompt = null){
		if(null == $prompt)
			return $this->prompt;
		else
			$this->prompt = $prompt;
	}

	public function add_reply($reply){
		$this->replies[] = $reply;
	}

	public function get_replies(){
		return $this->replies;
	}

	public function get_reply($idx=0){

		return $this->replies[$idx];
	}
}

class ad{
	private $prompt;
	private $url;

	public function __construct($args = null){
		if (is_a($args,"SimpleXMLElement")){
            $this->prompt = new prompt($args->prompt);
            $this->url = $args->url;
        }
	}

	public function url($url=null){
		if ($url){
			$this->url = $url;
			return $url;
		}
		else{
			return $this->url;
		}
	}

	public function prompt(){
		return $this->prompt;
	}
}


class db{
	public $host;
	public $database;
	public $user;
	public $pass;

	public function __construct($args){
		if (is_a($args,"SimpleXMLElement")){
			return;
            $this->host = new $args->host;
            $this->database= $args->database;
            $this->user=$args->user;
            $this->pass=$args->pass;
        }
	}

	private function connect(){

	}
}