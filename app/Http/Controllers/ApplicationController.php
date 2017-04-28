<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use EasyWeChat\Foundation\Application;

use App\Http\Models\User;
use App\Http\Models\Order;
use App\Http\Models\Object;
use App\Http\Models\Record;
use App\Http\Models\Captcha;
use App\Http\Models\PayRequest;
use App\Http\Models\WithdrawRequest;
use App\Http\Models\Feedback;

class ApplicationController extends Controller {

    public function home(Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if ($user->body_phone == 0) {
            return redirect('/account/bind');
        }

        return redirect('/objects');

    }

    public function objects() {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $objects = Object::where('is_disabled', '0')->orderBy('body_rank', 'desc')->get();

        return view('apps.objects', [
            'navigator' => 'objects',
            'controller' => 'objectsController',
            'user' => $user,
            'objects' => $objects
        ]);

    }

    public function objectsDetail($id, $period) {
        
        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $object = Object::find($id);

        return view('apps.objectsDetail', [
            'navigator' => 'objects',
            'controller' => 'objectsDetailController',
            'user' => $user,
            'item' => $object,
            'period' => $period
        ]);

    }

    public function ordersHold() {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $orders = Order::orderBy('created_at', 'desc')->where('id_user', $user->id)->where('striked_at', '0000-00-00 00:00:00')->get();

        return view('apps.ordersHold', [
            'navigator' => 'ordersHold',
            'controller' => 'ordersHoldController',
            'user' => $user,
            'orders' => $orders
        ]);

    }

    public function ordersHistory() {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $orders = Order::orderBy('created_at', 'desc')->where('id_user', $user->id)->where('striked_at', '<>' ,'0000-00-00 00:00:00')->paginate(20);

        return view('apps.ordersHistory', [
            'navigator' => 'ordersHistory',
            'controller' => 'ordersHistoryController',
            'user' => $user,
            'orders' => $orders
        ]);

    }

    public function ordersDetail($id) {
        
        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();
        
        $item = Order::find($id);

        return view('apps.ordersDetail', [
            'navigator' => 'ordersHistory',
            'user' => $user,
            'item' => $item
        ]);

    }

    public function account(Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $count_refers = User::where('id_introducer', $user->id)->count();

        $count_bonus = 0;
        $records = Record::where('id_user', $user->id)->where('id_refer', '>', 0)->get();
        foreach ($records as $record) {
            $count_bonus = $count_bonus + $record->body_stake;
        }

        return view('application.account', [
            'title' => '會員中心',
            'user' => $user,
            'count_refers' => $count_refers,
            'count_bonus' => $count_bonus
        ]);

    }

    public function accountBind(Request $request, Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->body_phone != 0){
            return redirect('/');
        }

        if ($request->isMethod('post')) {

            if(!$request->input('mobile', null)
            || !$request->input('vcode', null)){
                return view('application.info', [
                    'title' => '绑定失敗',
                    'icon' => 'warn',
                    'content' => '請將表單填寫完整，謝謝'
                ]);
            }

            if(Captcha::where('body_mobile', $request->input('mobile'))->where('body_code', $request->input('vcode'))->count() == 0){
                return view('application.info', [
                    'title' => '绑定失敗',
                    'icon' => 'warn',
                    'content' => '您填写的验证码不正确'
                ]);
            }

            Captcha::where('body_mobile', $request->input('mobile'))->where('body_code', $request->input('vcode'))->delete();

            $user->body_phone = $request->input('mobile');
            $user->save();

            return redirect('/');
            
        } else {

            return view('application.accountBind', [
                'title' => '帳戶激活'
            ]);

        }

    }

    public function accountPay(Request $request, Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        if ($request->isMethod('post')) {

            
            if(!$request->input('stake', null)){
                return view('application.info', [
                    'title' => '支付失敗',
                    'icon' => 'warn',
                    'content' => '参数提交不全'
                ]);
            }

            $payRequest = new PayRequest;
            $payRequest->id_user = $user->id;
            $payRequest->body_stake = $request->input('stake');
            $payRequest->body_gateway = 'online';
            $payRequest->save();

            $parameterForRequest = '';
            $parameterForSign = '';
            $parameters = array(
                'pay_memberid' => env('PAYMENT_PID'),
                'pay_orderid' => $payRequest->id,
                'pay_amount' => $payRequest->body_stake,
                'pay_applydate' => date('Y-m-d H:i:s'),
                'pay_bankcode' => 'WeiXin',
                'pay_notifyurl' => env('PAYMENT_URL_NO'),
                'pay_callbackurl' => env('PAYMENT_URL_RE')
            );
            ksort($parameters);
            reset($parameters);
            foreach ($parameters as $key => $value) {
                $parameterForSign = $parameterForSign . $key . '=>' . $value . '&';
            }
            $sign = strtoupper(md5($parameterForSign . 'key=' . env('PAYMENT_KEY')));
            $parameters['pay_md5sign'] = $sign;
            
            $requestURL = 'http://zf.cnzypay.com/Pay_Index.html';

            return view('application.accountPayRedirect', [
                'requestURL' => $requestURL,
                'parameters' => $parameters,
                'sign' => $sign
            ]);

        }

        return view('application.accountPay', [
            'title' => '我要充值'
        ]);
    }

    public function accountPayStaff(Application $wechat) {
        return view('application.accountPayStaff', [
            'title' => '人工充值'
        ]);
    }

    public function accountWithdrawRecords(Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $withdrawRequests = WithdrawRequest::where('id_user', $user->id)->orderBy('created_at', 'desc')->get();

        return view('application.accountWithdrawRecords', [
            'title' => '提现记录',
            'withdrawRequests' => $withdrawRequests
        ]);

    }

    public function accountWithdraw(Request $request, Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        if ($request->isMethod('post')) {

            if(!$request->input('name', null)
            || !$request->input('number', null)
            || !$request->input('bank', null)
            || !$request->input('deposit', null)
            || !$request->input('stake', null)){
                return view('application.info', [
                    'title' => '提現失敗',
                    'icon' => 'warn',
                    'content' => '請將表單填寫完整，謝謝'
                ]);
            }

            if(intval($request->input('stake', 0)) < 100) {
                return view('application.info', [
                    'title' => '提現失敗',
                    'icon' => 'warn',
                    'content' => '單次提現金額不得低於 100 元'
                ]);
            }

            if(intval($request->input('stake', 0)) > intval($user->body_balance)) {
                return view('application.info', [
                    'title' => '提現失敗',
                    'icon' => 'warn',
                    'content' => '您當前的帳戶餘額不足'
                ]);
            }

            if(intval($request->input('stake', 0)) % 100 != 0) {
                return view('application.info', [
                    'title' => '提現失敗',
                    'icon' => 'warn',
                    'content' => '提现金额必须为 100 元的倍数'
                ]);
            }

            $orderSum = Order::where('id_user', $user->id)->sum('body_stake');
            if(intval($orderSum) < 300){
                return view('application.info', [
                    'title' => '提現失敗',
                    'icon' => 'warn',
                    'content' => '为避免恶意透支，累积交易金额超过 300 元即可提现'
                ]);
            }

            DB::beginTransaction();

            $user->body_balance = $user->body_balance - $request->input('stake');
            $user->save();

            if($user->body_balance < 0) {
                DB::rollback();
            } else {
                $withdrawRequest = new WithdrawRequest;
                $withdrawRequest->id_user = $user->id;
                $withdrawRequest->body_stake = $request->input('stake');
                $withdrawRequest->body_name = $request->input('name');
                $withdrawRequest->body_bank = $request->input('bank');
                $withdrawRequest->body_deposit = $request->input('deposit');
                $withdrawRequest->body_number = $request->input('number');
                $withdrawRequest->save();

                $record = new Record;
                $record->id_user = $user->id;
                $record->id_withdrawRequest = $withdrawRequest->id;
                $record->body_name = '結餘提現';
                $record->body_direction = 0;
                $record->body_stake = $withdrawRequest->body_stake;
                $record->save();
            }

            DB::commit();

            return view('application.info', [
                'title' => '申請成功',
                'icon' => 'success',
                'content' => '我們已經收到您的提現申請，将在24小时内處理'
            ]);
            
        } else {
            return view('application.accountWithdraw', [
                'title' => '我要提現',
                'user' => $user
            ]);
        }

    }

    public function accountRecords(Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $records = Record::orderBy('created_at', 'desc')->where('id_user', $user->id)->paginate(20);

        return view('application.accountRecords', [
            'title' => '資金記錄',
            'records' => $records
        ]);

    }

    public function accountOrders(Application $wechat) {

        $user = session('wechat.oauth_user');
        $user = User::where('id_wechat', $user->id)->first();

        if($user->is_disabled > 0) return $this->denialUser();

        $orders = Order::orderBy('created_at', 'desc')->where('id_user', $user->id)->paginate(20);

        return view('application.accountOrders', [
            'title' => '交易記錄',
            'orders' => $orders
        ]);

    }

    public function accountExpand(Application $wechat, $id) {

        $qrcode = $wechat->qrcode;
        $result = $qrcode->forever($id);
        $ticket = $result->ticket;

        return view('application.accountExpand', [
            'title' => '恒信微交易',
            'qrcode' => $qrcode->url($ticket)
        ]);

    }

    public function support(Application $wechat) {
        return view('application.support', [
            'title' => '在綫咨詢'
        ]);
    }

    public function supportFaq(Application $wechat) {
        return view('application.supportFaq', [
            'title' => '常見問題'
        ]);
    }

    public function supportService(Application $wechat) {
        return view('application.supportService', [
            'title' => '在綫客服'
        ]);
    }

    public function supportFeedback(Request $request, Application $wechat) {

        if ($request->isMethod('post')) {

            if(!$request->input('content', null)
            || !$request->input('tool', null)
            || !$request->input('number', null)){
                return view('application.info', [
                    'title' => '反饋失敗',
                    'icon' => 'warn',
                    'content' => '請將表單填寫完整，謝謝'
                ]);
            }

            $feedback = new Feedback;
            $feedback->body_content = $request->input('content');
            $feedback->body_tool = $request->input('tool');
            $feedback->body_number = $request->input('number');
            $feedback->save();

            return view('application.info', [
                'title' => '反饋成功',
                'icon' => 'success',
                'content' => '我們已經收到您的反饋，謝謝'
            ]);
            
        } else {

            return view('application.supportFeedback', [
                'title' => '意見反饋'
            ]);

        }

    }

}
