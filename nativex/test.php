<?php

include("../bootstrap.php");
include("../_lib/includer.php");
require("../_lib/php/nativex/nativex.php");


$nativex = new NativeX();
$text = "the_quick_brown_fox_jumped_over_the_lazy_dog";
echo $text."\n";
$ciphertext = $nativex->pi_shuffle("the_quick_brown_fox_jumped_over_the_lazy_dog",1);
echo $ciphertext;
$plaintext = $nativex->pi_shuffle($ciphertext,-1);
echo "\n".$plaintext."\n";

$ciphertext = $nativex->banditX("the_quick_brown_fox_jumped_over_the_lazy_cog",1);
echo $ciphertext;
$plaintext = $nativex->banditX($ciphertext,-1,1);
echo "\n".$plaintext."\n";



die;
echo $ciphertext."\n";
$ciphertext = $nativex->banditx("the_quick_brown_fox_jumped_over_the_lazy_cog",1,2);
echo $ciphertext."\n";
die;


$plaintext = $nativex->banditx($ciphertext,-1);

echo $plaintext."\n";
die;



die;
$test = "111112";
echo $test."\n";
$stuff = $nativex->bandit($test);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff);
echo $stuff."\n";

die;
$stuff = $nativex->bandit($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->shell_shock($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->pi_shuffle($stuff,-1);
echo $stuff."\n";
$stuff = $nativex->bandit($stuff,-1);
echo $stuff."\n";
die;
$pi_shuffle = $nativex->pi_shuffle($test);
$bandit = $nativex->bandit($test);
echo $pi_shuffle."\n";
$double = $nativex->bandit($nativex->pi_shuffle($test));
echo $bandit."\n";
echo $double."\n";

$decrypt = $nativex->pi_shuffle($nativex->bandit($double,-1),-1);
echo $decrypt."\n";

