<?php
/**
 * Description of B
 * Business 的抽象类 提供一些魔术方法，自动调用mod库等
 * eg.自动调用表Dealer_Notice的mod里的方法则直接
 * $this->DealerNotice() 或 $this->dealerNotice() 
 * 忽略首字母的大小写
 * 
 * 调用其他的类则直接$this-> 加类名，例如$this->classname()即可
 *
 * @author tingting42<wangtingting847@sina.com>
 * @version SVN:$Id: B.php 153711 2016-04-27 04:02:48Z tingting42 $
 */
abstract class Abstract_B {

    private function _getCall() {
        return array('mod');
    }

    public function __call($function_name, $args) {
        $call = $this->_getCall();
        foreach ($call as $v) {
            $r = false;
            $param = array(
                'fn' => $function_name,
                'args' => $args,
            );
            if (method_exists($this, "_call_{$v}")) {
                $r = call_user_func(array($this, "_call_" . $v), $param);
            }
            if ($r !== false) {
                break;
            }
        }
        if($r === false){
            $r = call_user_func(array($this, "_call_default"), $param);
        }
        return $r;
    }
    private function _call_default($param){
        return false;
    }
    /**
     * 调用mod

     * @param type $param
     * @return boolean
     */
    private function _call_mod($param) {
        $fn = $param['fn'];
//        $fn = 'Mod_'.ucfirst($fn)."Model";
        if (!@class_exists($fn)) {
            return false;
        }
        if (empty($this->$fn)) {
            $this->$fn = $fn::instance();
        }
        return $this->$fn;
    }
}
