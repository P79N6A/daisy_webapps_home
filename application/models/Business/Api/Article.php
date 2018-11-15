<?php
/**
 * 557 daisy_webapps_home.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2018/11/15 1:36 PM
 */

class Business_Api_ArticleModel  extends Abstract_B
{
//    protected $_ArticleModel;
//    public function __construct(){
//        $this->_ArticleModel = $this->Mod_ArticleModel();
//    }

    /**
     * @param $id
     */
    public function getArticle($id){

        return Comm_Api::getArticle($id);


    }


}