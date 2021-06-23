<?php


namespace Siarko\cli\util;


trait Cacheable
{

    private array $_cache = [];
    private bool $_cacheEnabled = true;

    /**
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->_cacheEnabled;
    }

    /**
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled(bool $cacheEnabled): void
    {
        $this->_cacheEnabled = $cacheEnabled;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function cacheExists($key): bool
    {
        if(!$this->isCacheEnabled()){
            return false;
        }
        return array_key_exists($key, $this->_cache);
    }

    /**
     *
     */
    protected function cachePurge(){
        $this->_cache = [];
    }



    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function setCache($key, $value){
        $this->_cache[$key] = $value;
        return $value;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @param bool $setIfNull
     * @return mixed|null
     */
    protected function getCache($key, $defaultValue = null, bool $setIfNull = false){
        if($this->cacheExists($key)){
            return $this->_cache[$key];
        }
        $result = $defaultValue;
        if(is_callable($result)){
            $result = $result();
        }
        if($setIfNull){
            $this->setCache($key, $result);
        }
        return $result;
    }
}