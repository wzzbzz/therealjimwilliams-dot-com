<?php
global $doc_root;
_nativex();

class todd_NativeX extends NativeX{

    function __construct()
    {
        diebug(get_cwd());
        parent::__construct();
    }
    
    
}