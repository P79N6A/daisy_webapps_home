<?php
/**
 * 557 Yaf.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  20161001  Sina
 */
class IndexController extends Abstract_C
{

    //空模块例子
    public function indexAction() {
        $res['list'] = "LISTLIST....LIST";
        $this->getView()->assign("list", $res);
        return TRUE;
    }

    public function modelAction(){
    }
    public function emptyAction(){
        $db = Comm_Config::getConfig('config');
//        $object = new Business_Article_ShowModel();
//        $data = $object->getOne("1");


        $object2 = new Business_Api_ArticleModel();
        $data2 = $object2->getArticle("1");

        echo "<pre>";
        print_r($db);
//        print_r($data);
        print_r($data2);
        exit;

    }



}
