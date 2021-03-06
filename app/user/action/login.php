<?php
defined('IN_TS') or die('Access Denied.');
//程序主体
switch($ts){
	case "":
		if(intval($TS_USER['userid']) > 0) {
            header('Location: '.SITE_URL);exit;
        }
		
		//记录上次访问地址
		$jump = $_SERVER['HTTP_REFERER'];

		$title = '登录';
		include template("login");
		break;
	
	//执行登录
	case "do":
		
		//用于JS提交验证
		$js = intval($_GET['js']);

        $ad = intval($_POST['ad']);
		
		/*禁止以下IP用户登陆或注册*/
        /*
		$arrIp = aac('system')->antiIp();
		if(in_array(getIp(),$arrIp)){
			getJson('你的IP已被锁定，暂无法登录！',$js);
		}
        */
		
		$jump = trim($_POST['jump']);
		
		$email = trim($_POST['email']);
		
		$pwd = trim($_POST['pwd']);
		
		$cktime = $_POST['cktime'];
		
		if($email=='' || $pwd=='') getJson('Email和密码都不能为空！',$js);

		#先判断是否是Email
		if(valid_email($email)==true){

            $strUser = $new['user']->find('user',array(
                'email'=>$email,
            ));

            if($strUser == '') getJson('Email不存在，你可能还没有注册！',$js);

        }else{

		    #判断是否是手机号
            if(isPhone($email)==true){

                $strUser = $new['user']->find('user',array(
                    'phone'=>$email,
                ));

                if($strUser == '') getJson('手机号不存在，你可能还没有注册！',$js);

            }else{
                getJson('账号不存在，你可能还没有注册！',$js);
            }

        }
			
		if(md5($strUser['salt'].$pwd)!==$strUser['pwd']) getJson('密码错误！',$js);
		
		$new['user']->login($strUser['userid']);

		//对积分进行处理
		aac('user')->doScore($GLOBALS['TS_URL']['app'], $GLOBALS['TS_URL']['ac'], $GLOBALS['TS_URL']['ts']);

        if($ad==1){
            getJson('登录成功！',$js,2,SITE_URL.'index.php?app=system');
        }

		//跳转
		if($jump != ''){
			getJson('登录成功！',$js,2,$jump);
		}else{
			
			//登陆是否跳转到我的社区
			if($TS_SITE['istomy']){
				getJson('登录成功！',$js,2,tsUrl('my'));
			}else{
				getJson('登录成功！',$js,2,SITE_URL);
			}
			
		}
		
		break;
	
	//退出	
	case "out":
		aac('user')->logout();
		header('Location: '.tsUrl('user','login'));
		
		break;
}