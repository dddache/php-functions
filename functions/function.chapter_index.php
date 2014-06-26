<?php
/**
 * 转换数字为中文数字,可以支持到亿万级数字
 * 10001 => 一万零一
 * @param type $index
 * @return type
 * @author xilei
 */
function chapter_index($index){
   static $_chapterIndexes = array('零','一','二','三','四','五','六','七','八','九','十','百','千','万','亿');
   $result = array();
   $nums = str_split(strval($index), 1);
   krsort($nums,SORT_NUMERIC);
   $i = 0; $lastv = -1; $mi = 1;$gi = 0;
   $maxIndexes = array();
   foreach($nums as $v){
      if($gi==4 * $mi){
          //添加 万,亿
          $end = end($result);
          if(empty($maxIndexes))$maxIndexes = array_slice($_chapterIndexes,13);
          if(in_array($end, $maxIndexes)){
              array_pop($result);
          }
          $result[] = $maxIndexes[$mi-1]; 
          $i=0;$mi++;
       }
       if($i!=0 && $v!=0){
           $result[] = $_chapterIndexes[9+$i];
       }
       if($lastv!=0 || $v!=0){
           $result[] = $_chapterIndexes[$v];
       }
       $i++;$lastv = $v;$gi++;
   }
   krsort($result,SORT_NUMERIC);
   $count = count($result);
   //十零 => 十
   if($count!=1 && $result[0] == $_chapterIndexes[0]){
       array_pop($result);
   }
   //一十 => 十
   if($index>=10 && $index<100 && $result[$count-1] == $_chapterIndexes[1]){
       array_shift($result);
   }
   return implode($result);
}