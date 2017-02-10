<?php

function filterMS($str){
    
    $str = str_replace(chr(226).chr(128).chr(152),"'",$str);
    $str = str_replace(chr(226).chr(128).chr(153),"'",$str);
    $str = str_replace(chr(226).chr(128).chr(147),"'", $str);
    $str = str_replace(chr(130), ',', $str);    // baseline single quote
    $str = str_replace(chr(131), 'NLG', $str);  // florin
    $str = str_replace(chr(132), '"', $str);    // baseline double quote
    $str = str_replace(chr(133), '...', $str);  // ellipsis
    $str = str_replace(chr(134), '**', $str);   // dagger (a second footnote)
    $str = str_replace(chr(135), '***', $str);  // double dagger (a third footnote)
    $str = str_replace(chr(136), '^', $str);    // circumflex accent
    $str = str_replace(chr(137), 'o/oo', $str); // permile
    $str = str_replace(chr(138), 'Sh', $str);   // S Hacek
    $str = str_replace(chr(139), '<', $str);    // left single guillemet
    $str = str_replace(chr(140), 'OE', $str);   // OE ligature
    $str = str_replace(chr(145), "'", $str);    // left single quote
    $str = str_replace(chr(146), "'", $str);    // right single quote
    $str = str_replace(chr(147), '"', $str);    // left double quote
    $str = str_replace(chr(148), '"', $str);    // right double quote
    $str = str_replace(chr(149), '-', $str);    // bullet
    $str = str_replace(chr(150), '-', $str);    // endash
    $str = str_replace(chr(151), '--', $str);   // emdash
    $str = str_replace(chr(152), '~', $str);    // tilde accent
    $str = str_replace(chr(153), '&trade;', $str); // trademark ligature
    $str = str_replace(chr(154), 'sh', $str);   // s Hacek
    $str = str_replace(chr(155), '>', $str);    // right single guillemet
    $str = str_replace(chr(156), 'oe', $str);   // oe ligature
    $str = str_replace(chr(159), 'Y', $str);    // Y Dieresis
    $str = str_replace(chr(160), '&nbsp;', $str);    // UTF-8 fail
    $str = str_replace(chr(248),'&#248;', $str);    // UTF-8 fail
    $str = str_replace(chr(225),'&#225;', $str);    // UTF-8 fail
    $str = str_replace(chr(194).chr(174),'&reg;', $str);
    $str = str_replace(chr(194), "&reg;",$str);
    //$str = str_replace(chr(195),'&#74;', $str);
    $str = str_replace(chr(228),'&#228;', $str);
    $str = str_replace(chr(229),'&#229;', $str);
    $str = str_replace(chr(186),'&#186;', $str);
    $str = str_replace(chr(231),'&#231;', $str);
    $str = str_replace(chr(233),'&#233;', $str);
    $str = str_replace(chr(234),"&#234;", $str);
    $str = str_replace(chr(226).chr(128)."(TM)","'", $str);
    $str = str_replace(chr(226).chr(128)."oe",'"', $str);
    $str = str_replace(chr(194).chr(160),'', $str);
    $str = str_replace(chr(195),'', $str);
    $str = str_replace(chr(176),'&#186;', $str);
    $str = str_replace(chr(189),'&#189;', $str);
    $str = str_replace(chr(237),'&#237;', $str);
    $str = str_replace(chr(241),'&#241;',$str);
    $str = str_replace(chr(169),'&copy;',$str);
    $str = str_replace(chr(183),'&#183;',$str);

    return(stripslashes($str));
}

