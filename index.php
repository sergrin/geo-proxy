<?php

$bar = 'BAR';
//var_dump(apc_cache_info());

//var_dump(apc_store(array('foo')));
//phpinfo();
apc_store('foo', $bar);
var_dump(apc_fetch('foo'));