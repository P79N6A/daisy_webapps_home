<?php
class Mod_ArticleModel extends Abstract_M{
    protected $_tbl_name = 'mc_article';
    protected $_tbl_alis_name = 'article';
    protected static $_instance = null;
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}