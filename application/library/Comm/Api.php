<?php
/**
 * 557 daisy_webapps_home.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2018/11/15 11:38 AM
 */

class Comm_Api
{

    public static function getArticles()
    {
        return Comm_Curl::curl('/login/user');
    }


    public static function getArticle($id)
    {
        return Comm_Curl::curl('/login/user');
//        echo "articles by ".$id;
    }



}