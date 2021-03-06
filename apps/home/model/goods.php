<?php
namespace apps\home\model;
use core\lib\model;
class goods extends model{
    public $table='goods';
    //展示该类的所有商品,上架状态
    public function selGood($type){
        $info = $this->select($this->table,['id','cname','cover_path'],['ORDER'=>['id'=>'DESC'],'LIMIT'=>10,'type'=>$type,'status'=>0]);
        foreach($info as $k=>$v){
            $info[$k]['cover_path']=unserialize($v['cover_path']);
        }
        return $info;
    }
    /*商品详情页面
    @param id 商品id值
     * */
    public function selDetail($id){
        $info = $this->get($this->table,['id','cname','cover_path','keywords',
            'specification','market_price',
            'promotion_price','details','inventory',
            'ctime','gtype','type'],['id'=>$id]);
        $info['cover_path']=unserialize($info['cover_path']);
        $info['specification']=unserialize($info['specification']);
        $string='';
        foreach($info['specification'] as $v){
            $string.=$v.",";
        }
        $string=rtrim($string,',');//去掉最右边的逗号 ','
        $info['specification']=$string;
        return $info;
    }
    /*商品评价
    @param id 商品id值
     * */
    public function good_estimate($id){
        $sql= "SELECT content,headimgurl,nickname,goods_estimate.type FROM 
              goods_estimate LEFT JOIN  wechat_user ON wechat_user.id=goods_estimate.wuid WHERE gid=$id";

       $info = $this->query($sql)->fetchAll();
        return $info;
    }

    //加入购物车
    public function addCar($data){
        return $this->insert('shop_cart',$data);
    }

     //购物车选择的商品展示
        /*@param id 购物车id字符串
         * */
    public function shopDetail($id){
        $info = $this->select('shop_cart',['[>]goods'=>['gid'=>'id']]
            ,['goods.id','shop_cart.id(spid)','shop_cart.specification','shop_cart.quantity','cname',
                'cover_path','promotion_price'],
            ['shop_cart.id'=>$id,'ORDER'=>['shop_cart.ctime'=>'DESC']]);
        foreach($info as $k=>$v){
            $info[$k]['cover_path']=unserialize($v['cover_path']);
        }
        return $info;
    }

        /*查询默认收货地址
        @param wuid 当前 微信用户
        status=1 默认地址
         * */
        public function myAddr($wuid){
            $info = $this->get('receiver_address',['id','consignee','contact_number',
                'address'],['status'=>1]);
            if($info){
                $info['address']= implode(' ',unserialize($info['address']));
            }
            return $info;
        }

        /*直接购买商品,返回商品信息
        @param id 商品id
         * */
        public function shopGood($id){
            $info = $this->get($this->table,['id','cname','cover_path','specification','promotion_price'],['id'=>$id]);
            $info['cover_path']=unserialize($info['cover_path']);
            return $info;
        }


    //追加商品数据
    public function moreGood($page,$type){
        $info = $this->select($this->table,['id','cname','cover_path'],['ORDER'=>['id'=>'DESC'],'LIMIT'=>[$page*10,10],'type'=>$type,'status'=>0]);
        foreach($info as $k=>$v){
            $info[$k]['cover_path']=unserialize($v['cover_path']);
        }
        return $info;
    }

    //确认订单
    public function orderInfo($data,$wuid){
        //下单时间
        $data['dtime']=time();
        $data['wuid']=$wuid;
        //订单编号
        $data['serial_number']=indent_number();
        //操作时间
        $data['ctime']=time();
        //类型 ,待付款
         $data['type']=0;
        //状态,正常
        $data['status']=0;
        
        //订单生成后删除购物车信息  ,根据 id值删除
        if($data['shopcar_id']){
            //通过购物车购买
            $res = $this->delete('shop_cart',['id'=>$data['shopcar_id']]);
            unset($data['shopcar_id']);
        }
        $re = $this->insert('indent',$data);
        if($re && $res){
            return true;
        }
    }

    //买家给出评价  @param name  商品名称
    public function writeEstimate($data){
        $needInfo=array();
        $needInfo['wuid']=$data['wuid'];
        $needInfo['gid']=$data['gid'];
        $needInfo['specification']=$data['specification'];
        $needInfo['content']=$data['content'];
        $needInfo['ctime']=$data['ctime'];
        $needInfo['type']=$data['type'];
        $info = $this->insert('goods_estimate',$needInfo);
        if($info){
            //将订单改为售后
            $re = $this->update('indent',['type'=>4],['id'=>$data['indentId']]);
        }
        if($info && $re){
            return true;
        }else{
            return false;
        }
    }

    //根据商品名称查询商品id
    public function selId($name){
        return $this->get('goods',['id'],['cname'=>$name]);
    }


    //根据订单id获取商品id,规格
     public function selIndent_info($id){
         $info = $this->get('indent',['goods_name','goods_specification','id'],['id'=>$id]);

             $goodName=unserialize($info['goods_name']);
             $specification=unserialize($info['goods_specification']);
         //商品id数组
         $idArr=array();
         foreach($goodName as $k=>$v){
             $arr = $this->get('goods',['id'],['cname'=>$v]);
             $idArr[$k]=$arr['id'];
         }

         $needArr=array();
         for($i=0;$i<count($idArr);$i++){
             $a=$idArr[$i];
             $b=$specification[$i];
             $needArr[$i]['gid']=$a;
             $needArr[$i]['specification']=$b;
         }
         return $needArr;
     }
}
