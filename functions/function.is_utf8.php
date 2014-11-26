<?php
function is_utf8($s){
  return (bool)preg_match('//u',$s);
}

