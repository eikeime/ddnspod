<?php
$mlink = new Mongo();
$mlog = $mlink -> ddns -> log;

//$mlog -> drop();
$mlog -> insert(array('updatetime' => new MongoDate(time() - 7 * 24 * 60 * 60 - 60), 'ip' => '192.168.1.1'));
$mlog -> deleteIndex('ttl');
$mlog -> ensureIndex('updatetime', array('expireAfterSeconds' => 7 * 24 * 60 * 60, 'name' => 'ttl'));
//$mlog -> remove(array('_id' => new MongoId('需要移除的key')));

$mlink -> close();
?>