<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use General\Controller\GeneralController;

class PushController extends CommonController{
    public function plist()
    {
        $data=page(M("push","ms_common_"),null,10,"","id desc");
        $this->assign("list",$data["list"]);
        $this->assign("page",$data["page"]);

        $this->display();
    }
    public function add()
    {
        $this->display("info");
    }
    public function add_push()
    {
        if(M("push","ms_common_")->add($_POST))
        {
            $this->success("添加成功",U("Push/plist"));
        }else{
            $this->error("添加失败");
        }
    }
    public function del_push()
    {
        if(M("push","ms_common_")->where(["id"=>$_GET["id"]])->delete())
        {
            $this->success("删除成功",U("Push/plist"));
        }else{
            $this->success("删除失败",U("Push/plist"));
        }
    }
    public function jpush()
    {
        $pushinfo=M("push","ms_common_")->where(["id"=>$_GET["id"]])->find();
        {
            if($pushinfo)
            {
                $pushclient=new \JPush\Client("22d4194b8852f23d9ff01535","39e3c54d29e4192b218e9430");
                $res=$pushclient->push()
                    ->setPlatform('all')
                    ->addAllAudience()->setMessage($pushinfo["content"])
                    ->setNotificationAlert($pushinfo["title"])
                    ->send();
                M("push","ms_common_")->where(["id"=>$_GET["id"]])->save(["status"=>2]);
                $this->success("推送成功",U("Push/plist"));

            }else{
                $this->success("发送失败",U("Push/plist"));
            }
        }
    }
}