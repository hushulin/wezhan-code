<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Material;

use App\Http\Models\Order;
use App\Http\Models\Record;
use App\Http\Models\PayRequest;
use App\Http\Models\WithdrawRequest;
use App\Http\Models\User;

class TestController extends Controller {

    public function run(Request $request, Application $wechat) {

        // 更新微信公共号底部菜单
        if ($request->input('method') == 'menu') {

            $menu = $wechat->menu;
            $menu->destroy();

            $buttons = [
                [
                    'type' => 'view',
                    'name' => '开始交aaa易',
                    'url' => 'http://www.jiahongguoji.com/'
                ],
                [
                    'type' => 'view',
                    'name' => '会员中心',
                    'url' => 'http://www.jiahongguoji.com/account'
                ],
                [
                    'type' => 'view',
                    'name' => '在线咨询',
                    'url' => 'http://www.jiahongguoji.com/support'
                ]
            ];
            $menu->add($buttons);

            die('OK');
        }

        // 获取微信公共号素材列表
        if ($request->input('method') == 'material') {

            $material = $wechat->material;
            $lists = $material->lists('news', 0, 10);

            die(print_r($lists));

        }


    }

}