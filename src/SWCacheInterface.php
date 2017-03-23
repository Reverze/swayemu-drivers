<?php

interface SWCacheInterface
{
    public function get($cachePath);
    public function set($cachePath, $data);

}

?>