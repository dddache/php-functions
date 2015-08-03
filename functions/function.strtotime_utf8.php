<?php
/**
 * 获取某年中某月多少天
 */
function getDaysInMonth($year,$month){
  $days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
     if ($month == 1 &&  (($year%4 == 0 && $year%100 != 0) || $year%400 == 0)){
        return 29;
     }
     return $days[$month];
}

/**
 * 最后截止为月,查询当月的
 * 最后截止为日,查询当日的
 *
 * 可能出现的字符: 2013年7月8日, 2013-7-8,7-8,8,13-7-8,20130708,201378,130708(暂不支持)
 * 年\x{5e74},月\x{6708},日\x{65e5},号\x{53f7}
 *
 *全匹配
 *@author xl
 */
function strtotime_utf8($str){
	if(empty($str) || !preg_match('/\d+/',$str)){
		return false;
	}
	//放在这里合理吗?高可能性用不到
	$time_arr = getdate();
	
	$result = array(
		'instance'=>0,//间隔s
		'start_time'=>0,//起始s
		'text'=>''//文本日期表示
	);
	
	switch(true){
		/**
		 * 默认为当前年,当前月 (查询这一天的)
		 */
		case preg_match('/^(\d{1,2})(\x{65e5}|\x{53f7})?$/u',$str,$res):
			$d = intval($res[1]);
			
			$days = getDaysInMonth($time_arr['year'],$time_arr['mon']-1);
			if($d>$days)$d = $days ;//超出,按最后一天
	
			$time_arr['mday'] = $d;
			$result['instance'] = 24*60*60;
			$result['text'] = "{$d}号";
			unset($d);
			break;
		/**
		 * 年-月 (查询这月的)
		 */
		case preg_match('/^([^0]\d{3})(\x{5e74}|-|\.|,)?(\d{1,2})(\x{6708})?$/u',$str,$res):
			$y = intval($res[1]);
			$m = intval($res[3]);
			
			if($y > $time_arr['year']) $y =  $time_arr['year'];
			if($m > 12) $m = 12;
			$days = getDaysInMonth($time_arr['year'],$m-1);
			
			$time_arr['mday'] = 0;//从第一天开始
			$time_arr['year'] = $y;
			$time_arr['mon']  = $m;
			
			$result['instance'] = $days*24*60*60;
			$result['text'] = "{$y}年{$m}月";
			
			unset($y,$m);
			break;
		/**
		 * 默认为当前年, 月-日(查询这天的)
		 */
		case preg_match('/^(\d{1,2})(\x{6708}|-|\.|,)?(\d{1,2})(\x{65e5}|\x{53f7})?$/u',$str,$res):
			$m = intval($res[1]);
			$d = intval($res[3]);
			if($m>12) $m = 12;
			$days = getDaysInMonth($time_arr['year'],$m-1);
			if($d>$days) $d = $days;
			
			$time_arr['mday'] = $d;
		    $time_arr['mon'] = $m;
			
			$result['instance'] = 24*60*60;
			$result['text'] = "{$m}月{$d}号";
			unset($d,$m);
			break;
		/**
		 * 年-月-日 (查询这一天的)
		 */
		case preg_match('/^([^0]\d{3})(\x{5e74}|-|\.|,)?(\d{1,2})(\x{6708}|-|\.|,)?(\d{1,2})(\x{65e5}|\x{53f7})?$/u',$str,$res):
			$y = intval($res[1]);
			$m = intval($res[3]);
			$d = intval($res[5]);
			if($y > $time_arr['year']) $y =  $time_arr['year'];
			if($m > 12) $m = 12;
			$days = getDaysInMonth($time_arr['year'],$m-1);
			if($d>$days) $d = $days;
			
			$time_arr['mday'] = $d;
		    $time_arr['mon'] = $m;
			$time_arr['year'] = $y;
			$result['instance'] = 24*60*60;
			$result['text'] = "{$y}年{$m}月{$d}号";
			break;
		
		default:return false;
	}
	
	 //var_dump($res);
	 unset($res);
	 //var_dump($time_arr);
	 $result['start_time'] = mktime(0,0,0,$time_arr['mon'],$time_arr['mday'],$time_arr['year']);
	 return $result;
}
