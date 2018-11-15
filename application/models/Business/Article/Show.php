<?php

/**
 * 文章基础类
 *
 */
class Business_Article_ShowModel extends Abstract_B
{

    protected $_err = 0;

    /**
     * 获取文章详情
     * @param type $id
     * @return type
     */
    public function getOne($id)
    {
        $condition['id'] = $id;
        $extend = [];
        $main = $this->Mod_ArticleModel()->where($condition)->findOne();
        return empty($main) ? array() : array_merge($extend, $main);
    }
}