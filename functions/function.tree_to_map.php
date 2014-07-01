<?php
/**
 *  创建 根节点 与 叶子节点map
 *  $kmap = Yii::app()->cache->get('kmap');
 *  if(empty($kmap)){
 *    $connection = Yii::app()->db;
 *    $sql = "SELECT id,pid FROM {{nodes}}";
 *    $command = $connection->createCommand($sql);
 *    $dataReader = $command->query();
 *    $kmap = tree_to_map($dataReader);
 *    Yii::app()->cache->set('kmap', $kmap,24*3600*30);
 *  }
 *  //do search for $kmap ...
 */
function tree_to_map($records){
    $rows = array();
    //struct 子节点 => 父节点
    foreach($records as $row){
        $rows[$row['id']] = $row['pid'];
    }
    $map = array();
    while(!empty($rows)){
        foreach($rows as $key=>$value){
            if(!in_array($key,$rows)){
                if(!isset($map[$value])) $map[$value] = array();
                if(!empty($map[$key])){
                    $map[$value] = array_merge($map[$value],$map[$key]);
                }else{
                    $map[$value][] = $key;
                }
                unset($rows[$key]);
            }
        }
    }
    foreach($map[0] as $v){
        $map[$v]=array($v);
    }
    unset($map[0]);
    return $map;
}