<?php
namespace apps\admin\ctrl;
use core\lib\conf;
use apps\admin\model\adminUser;
use vendor\page\Page;
class adminUserCtrl extends baseCtrl{
  public $db;
  public $id;
  // 构造方法
  public function _auto(){
    $this->db = new adminUser();
    $this->id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  }

  // 添加后台用户
  public function add(){
    // Get
    if (IS_GET === true) {
      // id
      if ($this->id) {
        $res = $this->db->getInfo($this->id);
        // assign
        $this->assign('data',$res);
      }
      $this->display('adminUser','add.html');
      die;
    }
    // Ajax
    if (IS_AJAX === true) {
      // id
      if ($this->id) {
        // 修改后的密码
        $password = enPassword(htmlspecialchars($_POST['password']));
        // save
        $res = $this->db->save($this->id,$password);
      } else {
        // data
        $data = $this->getData();
        // add
        $res = $this->db->add($data);
      }
      // if
      if ($res) {
        echo json_encode(true);
        die;
      } else {
        echo json_encode(false);
        die;
      }
    }

  }

  // 初始化数据
  private function getData(){
    // data
    $data = array();
    $data['username'] = htmlspecialchars($_POST['username']);
    $data['password'] = enPassword(htmlspecialchars($_POST['password']));
    $data['ctime'] = time();
    $data['status'] = 0;
    return $data;
  }

  // 后台用户列表
  public function index(){
    // search
    $search = isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '';
    $cou = $this->db->cou();
    // 数据分页
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $p = new Page($cou,conf::get('PAGES','admin'),$page,conf::get('LIMIT','admin'));
    // 结果集
    $data = $this->db->sel($search,bcsub($p->currPage,1,0),$p->subPages);
    // assign
    $this->assign('data',$data);
    $this->assign('page',$p->showPages(1));
    // display
    $this->display('adminUser','index.html');
    die;
  }

  // flag
  public function flag(){
    // Ajax
    if (IS_AJAX === true) {
      // 获取数据
      $id = $_POST['id'];
      $status = $_POST['status'];
      $res = $this->db->flag($id,$status);
      // if
      if ($res) {
        echo json_encode(true);
        die;
      }else{
        echo json_encode(false);
        die;
      }
    }
  }

  // 删除
  public function del(){
    // Ajax
    if (IS_AJAX === true) {
      $res = $this->db->del($this->id);
      // if
      if ($res) {
        echo json_encode(true);
        die;
      } else {
        echo json_encode(false);
        die;
      }
    }
  }

}