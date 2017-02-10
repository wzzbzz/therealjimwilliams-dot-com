<?php 

include "nativex.php";

$text = "This is a Test";

$nativex = new NativeX();
echo "\n";
echo $nativex->bandit($text)."\n";
echo $nativex->banditx($text)."\n";
