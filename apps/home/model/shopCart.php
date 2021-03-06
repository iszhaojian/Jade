<?php
namespace apps\home\model;
use  core\lib\model;
class shopCart extends model{
    public $table='shop_cart';
    /*查询购物车
    ＠param wuid 微信用户id
     * */
    public function selShopcar($wuid){
       $info = $this->select($this->table,['[>]goods'=>['gid'=>'id']]
                ,['goods.id','shop_cart.id(spid)','shop_cart.specification','shop_cart.quantity','cname',
                'cover_path','promotion_price'],
           ['shop_cart.wuid'=>$wuid,'ORDER'=>['shop_cart.ctime'=>'DESC']]);
        foreach($info as $k=>$v){
            $info[$k]['cover_path']=unserialize($v['cover_path']);
        }
        return $info;
    }

    //删除购物车  @param id   购物车id
    public function delCar($id){
         return $this->delete($this->table,['id'=>$id]);
    }
}