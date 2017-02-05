<?php

/* validate start */
//check email avalible
function check_email($str){
    if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $str)){
        return false;
    }
    return true;
}
function check_color($c){
    if(preg_match('/^\#([0-9A-F]{2}[0-9A-F]{2}[0-9A-F]{2}|[0-9A-F]{3})$/i', $c)){
        return true;
    }
    return false;
}
/* validate end */