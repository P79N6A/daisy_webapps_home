<?php
/**
 * description: redis类
 * author: weiqiang6@staff.sina.com.cn
 * createTime: 2016/7/4 13:44
 */

class Comm_Redis {
    private static $_write_handle = null;
    private static $_read_handle = null;

    /**
     * 读库
     * @return null|Redis
     * @throws Exception_System
     */
    public static final function getRead() {
        $redisConfig = Comm_Config::getConf('config.'.DEVELOPMENT.'.redis');
        if (!self::$_read_handle) {
            self::$_read_handle = new Redis();
            self::$_read_handle->connect($redisConfig['host_r'],$redisConfig['port_r']);
        }

        return self::$_read_handle;
    }

    /**
     * 写库
     * @return null|Redis
     * @throws Exception_System
     */
    public static final function getWrite() {
        $redisConfig = Comm_Config::getConf('config.'.DEVELOPMENT.'.redis');
        if (!self::$_write_handle) {
            self::$_write_handle = new Redis();
            self::$_write_handle->connect($redisConfig['host'],$redisConfig['port']);
        }

        return self::$_write_handle;
    }


    /**
     * 写缓存
     *
     * @param string $key 组存KEY
     * @param string $value 缓存值
     * @param int $expire 过期时间， 0:表示无过期时间
     */
    public static function set($key, $value, $expire=0){
        if (!self::getWrite()) return false;

        // 永不超时
        if($expire == 0){
            $ret = self::getWrite()->set($key, $value);
        }else{
            $ret = self::getWrite()->setex($key, $expire, $value);
        }

        return $ret;
    }

    /**
     * 读缓存
     *
     * @param string $key 缓存KEY,支持一次取多个 $key = array('key1','key2')
     * @return string || boolean  失败返回 false, 成功返回字符串
     */
    public static function get($key){
        // 是否一次取多个值
        $func = is_array($key) ? 'mGet' : 'get';
        return self::getRead()->{$func}($key);
    }

    /**
     * 条件形式设置缓存，如果 key 不存时就设置，存在时设置失败
     *
     * @param string $key 缓存KEY
     * @param string $value 缓存值
     * @return boolean
     */
    public static function setnx($key, $value){
        if (!self::getWrite()) return false;

        return self::getWrite()->setnx($key, $value);
    }

    /**
     * 删除缓存
     *
     * @param string || array $key 缓存KEY，支持单个健:"key1" 或多个健:array('key1','key2')
     * @return int 删除的健的数量
     */
    public static function remove($key){
        if (!self::getWrite()) return false;

        // $key => "key1" || array('key1','key2')
        return self::getWrite()->delete($key);
    }

    /**
     * 值加加操作,类似 ++$i ,如果 key 不存在时自动设置为 0 后进行加加操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public static function incr($key,$default=1,$timeOut=0){
        if (!self::getWrite()) return false;
        //若要求失效时间
        if ($timeOut) {
            //未被设置
            if (!self::get($key)) {
                self::set($key,0);
                self::getWrite()->expire($key,$timeOut);
            }
        }

        if($default == 1){
            return self::getWrite()->incr($key);
        }else{
            return self::getWrite()->incrBy($key, $default);
        }
    }

    /**
     * 值减减操作,类似 --$i ,如果 key 不存在时自动设置为 0 后进行减减操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public static function decr($key,$default=1){
        if($default == 1){
            return self::getWrite()->decr($key);
        }else{
            return self::getWrite()->decrBy($key, $default);
        }
    }


    /**
     * 左侧如队列
     * @param $key
     * @param $value
     * @return int
     */
    public static function lpush($key,$value) {
        return self::getWrite()->lPush($key,$value);
    }


    /**
     * 右侧如队列
     * @param $key
     * @param $value
     * @return int
     */
    public static function rpush($key,$value) {
        if (!self::getWrite()) return false;

        return self::getWrite()->rPush($key,$value);
    }


    /**
     * 左侧出队列
     * @param $key
     */
    public static function lpop($key) {
        return self::getRead()->lPop($key);
    }


    /**
     * 右侧出队列
     * @param $key
     */
    public static function rpop($key) {
        return self::getRead()->rPop($key);
    }


    /**
     * 插入集合
     * @param $key
     * @param $value
     * @return int
     */
    public static function sadd($key,$value) {
        if (!self::getWrite()) return false;

        return self::getWrite()->sAdd($key,$value);
    }


    /**
     * 移除集合中的指定元素
     * @param $key
     * @param $value
     */
    public static function sremove($key, $value) {
        if (!self::getWrite()) return false;

        return self::getWrite()->sRemove($key,$value);
    }


    /**
     * 获取指定集合的所有元素
     * @param $key
     * @return array
     */
    public static function smembers($key) {
        return self::getRead()->sMembers($key);
    }
}