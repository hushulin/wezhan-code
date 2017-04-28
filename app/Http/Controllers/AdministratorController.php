<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Models\Administrator;
use App\Http\Models\User;
use App\Http\Models\Order;
use App\Http\Models\Record;
use App\Http\Models\PayRequest;
use App\Http\Models\WithdrawRequest;
use App\Http\Models\Object;
use App\Http\Models\Feedback;
use Excel;

class AdministratorController extends Controller {

    private function requiredSession(Request $request) {
        if(!$request->session()->has('administrator')){
            header('location: /administrator/signIn');
            exit();
        }
    }

    private function modifyEnv(array $data) {

        $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));
        $contentArray->transform(function ($item) use ($data){
             foreach ($data as $key => $value){
                 if(str_contains($item, $key)){
                     return $key . '=' . $value;
                 }
             }
 
             return $item;
         });
        $content = implode($contentArray->toArray(), "\n");
        \File::put($envPath, $content);
    }

    public function home(Request $request) {

        $this->requiredSession($request);
        $data = array(
            'today' => array(
                'users' => User::where('created_at', '>=', date('Y-m-d 00:00:00', time()))->count(),
                'orders' => Order::where('created_at', '>=', date('Y-m-d 00:00:00', time()))->count(),
                'payRequests' => PayRequest::where('processed_at', '<>', '0000-00-00 00:00:00')->where('created_at', '>=', date('Y-m-d 00:00:00', time()))->sum('body_stake'),
                'withdrawRequests' => WithdrawRequest::where('created_at', '>=', date('Y-m-d 00:00:00', time()))->sum('body_stake')
            ),
            'count' => array(
                'day' => array(
                    'stake' => floatval(Order::where('created_at', '>=', date('Y-m-d 00:00:00', time()))->sum('body_stake')) - floatval(Record::where('id_order', '<>', '0')->where('body_direction', '1')->where('created_at', '>=', date('Y-m-d 00:00:00', time()))->sum('body_stake')),
                    'free' => Record::where('created_at', '>=', date('Y-m-d 00:00:00', time()))->where('body_name', '註冊贈金')->sum('body_stake'),
                    'profit' => 0
                ),
                'month' => array(
                    'stake' => floatval(Order::where('created_at', '>=', date('Y-m-01 00:00:00', time()))->sum('body_stake')) - floatval(Record::where('id_order', '<>', '0')->where('body_direction', '1')->where('created_at', '>=', date('Y-m-01 00:00:00', time()))->sum('body_stake')),
                    'free' => Record::where('created_at', '>=', date('Y-m-01 00:00:00', time()))->where('body_name', '註冊贈金')->sum('body_stake'),
                    'profit' => 0
                ),
                'all' => array(
                    'payRequests' => PayRequest::where('processed_at', '<>', '0000-00-00 00:00:00')->sum('body_stake'),
                    'withdrawRequests' => WithdrawRequest::sum('body_stake'),
                    'balance' => User::sum('body_balance'),
                    'free' => Record::where('body_name', '註冊贈金')->sum('body_stake'),
                    'profit' => 0
                )
            )
        );

        $data['count']['day']['profit'] = floatval($data['count']['day']['stake']) - floatval($data['count']['day']['free']);
        $data['count']['month']['profit'] = floatval($data['count']['month']['stake']) - floatval($data['count']['month']['free']);
        $data['count']['all']['profit'] = floatval($data['count']['all']['payRequests']) - floatval($data['count']['all']['balance']) - floatval($data['count']['all']['withdrawRequests']);

        return view('administrator.home', [
            'active' => 'home',
            'data' => $data
        ]);

    }

    public function users(Request $request) {

        $this->requiredSession($request);

        $datas = User::orderBy('created_at', 'desc');
        if($request->input('id_user', null)) $datas->where('id', $request->input('id_user'));
        if($request->input('body_phone', null)) $datas->where('body_phone', $request->input('body_phone'));
        if($request->input('id_introducer', null)) $datas->where('id_introducer', $request->input('id_introducer'));
        $datas = $datas->paginate(20);

        return view('administrator.users', [
            'active' => 'users',
            'datas' => $datas,
            'id_user' => $request->input('id_user')
        ]);

    }

    public function statusForUser(Request $request, $id) {

        $this->requiredSession($request);

        $user = User::find($id);

        if($user->is_disabled == 0) $user->is_disabled = 1;
        else $user->is_disabled = 0;

        $user->save();

        return '<script>alert("操作成功"); history.go(-1);</script>';

    }

    public function orders(Request $request) {

        $this->requiredSession($request);

        $datas = Order::orderBy('created_at', 'desc');
        if($request->input('id_order', null)) $datas->where('id', $request->input('id_order'));
        if($request->input('id_user', null)) $datas->where('id_user', $request->input('id_user'));
        if($request->input('id_object', null)) $datas->where('id_object', $request->input('id_object'));
        $datas = $datas->paginate(20);

        return view('administrator.orders', [
            'active' => 'orders',
            'datas' => $datas,
            'id_user' => $request->input('id_user'),
            'id_object' => $request->input('id_object')
        ]);

    }

    public function records(Request $request) {

        $this->requiredSession($request);

        $datas = Record::orderBy('created_at', 'desc');
        if($request->input('id_user', null)) $datas->where('id_user', $request->input('id_user'));
        $datas = $datas->paginate(20);

        return view('administrator.records', [
            'active' => 'records',
            'datas' => $datas,
            'id_user' => $request->input('id_user')
        ]);

    }

    public function payRequests(Request $request) {

        $this->requiredSession($request);

        $datas = PayRequest::orderBy('created_at', 'desc');
        if($request->input('id_payRequest', null)) $datas->where('id', $request->input('id_payRequest'));
        if($request->input('id_user', null)) $datas->where('id_user', $request->input('id_user'));
        $datas = $datas->paginate(20);

        return view('administrator.payRequests', [
            'active' => 'payRequests',
            'datas' => $datas,
            'id_user' => $request->input('id_user')
        ]);

    }

    public function withholdForUser(Request $request, $id) {

        $this->requiredSession($request);
        $alert = NULL;

        if ($request->isMethod('post')) {
            
            if(!$request->input('stake', null) 
            || !$request->input('transfer_number', null)){
                $alert = '参数提交不全';
            } else {
                if(intval($request->input('stake')) <=0){
                    $alert = '扣款金额必须大于0元';
                } else {

                    $user = User::find($id);
                    $user->body_balance = $user->body_balance - intval($request->input('stake'));
                    $user->save();

                    $record = new Record;
                    $record->id_user = $user->id;
                    $record->body_name = $request->input('transfer_number');
                    $record->body_direction = 0;
                    $record->body_stake = intval($request->input('stake'));
                    $record->save();

                    $alert = '扣款成功';

                }
            }

        }

        return view('administrator.withholdForUser', [
            'active' => 'users',
            'id_user' => $id,
            'alert' => $alert
        ]);

    }

    public function payForUser(Request $request, $id) {

        $this->requiredSession($request);
        $alert = NULL;

        if ($request->isMethod('post')) {
            
            if(!$request->input('stake', null) 
            || !$request->input('transfer_number', null)){
                $alert = '参数提交不全';
            } else {
                if(intval($request->input('stake')) <=0){
                    $alert = '充值金额必须大于0元';
                } else {

                    $payRequest = new payRequest;
                    $payRequest->id_user = $id;
                    $payRequest->body_stake = intval($request->input('stake'));
                    $payRequest->body_gateway = 'staff';
                    $payRequest->body_transfer_number = $request->input('transfer_number');
                    $payRequest->processed_at = date('Y-m-d H:i:s', time());
                    $payRequest->save();

                    $user = User::find($id);
                    $user->body_balance = $user->body_balance + $payRequest->body_stake;
                    $user->save();

                    $record = new Record;
                    $record->id_user = $user->id;
                    $record->id_payRequest = $payRequest->id;
                    $record->body_name = '帳戶充值';
                    $record->body_direction = 1;
                    $record->body_stake = $payRequest->body_stake;
                    $record->save();

                    $alert = '充值成功';

                }
            }

        }

        return view('administrator.payForUser', [
            'active' => 'users',
            'id_user' => $id,
            'alert' => $alert
        ]);

    }

    public function withdrawForUser(Request $request, $id) {

        $this->requiredSession($request);
        $alert = NULL;

        $withdrawRequest = WithdrawRequest::find($id);

        if ($request->isMethod('post') && $withdrawRequest->processed_at == '0000-00-00 00:00:00') {
            
            if(!$request->input('transfer_number', null)){
                $alert = '参数提交不全';
            } else {
                $withdrawRequest->body_transfer_number = $request->input('transfer_number');
                $withdrawRequest->processed_at = date('Y-m-d H:i:s', time());
                $withdrawRequest->save();
                $alert = '处理完毕';
            }

        }

        return view('administrator.withdrawForUser', [
            'active' => 'withdrawRequests',
            'alert' => $alert,
            'id' => $id,
            'transfer_number' => $withdrawRequest->body_transfer_number,
            'processed_at' => $withdrawRequest->processed_at
        ]);

    }

    public function withdrawForUserCanceled(Request $request, $id) {

        $this->requiredSession($request);

        $withdrawRequest = WithdrawRequest::find($id);

        if ($withdrawRequest->processed_at == '0000-00-00 00:00:00') {

            $withdrawRequest->body_transfer_number = 'FAIL';
            $withdrawRequest->processed_at = date('Y-m-d H:i:s', time());
            $withdrawRequest->save();

            $user = User::find($withdrawRequest->id_user);
            $user->body_balance = $user->body_balance + $withdrawRequest->body_stake;
            $user->save();

            $record = new Record;
            $record->id_user = $user->id;
            $record->id_withdrawRequest = $withdrawRequest->id;
            $record->body_name = '提现退回';
            $record->body_direction = 1;
            $record->body_stake = $withdrawRequest->body_stake;
            $record->save();

        }

        return redirect('/administrator/withdrawRequests');

    }

    public function withdrawRequests(Request $request) {

        $this->requiredSession($request);

        $datas = WithdrawRequest::orderBy('created_at', 'desc');
        if($request->input('id_withdrawRequest', null)) $datas->where('id', $request->input('id_withdrawRequest'));
        if($request->input('id_user', null)) $datas->where('id_user', $request->input('id_user'));
        $datas = $datas->paginate(20);

        return view('administrator.withdrawRequests', [
            'active' => 'withdrawRequests',
            'datas' => $datas,
            'id_user' => $request->input('id_user')
        ]);

    }

    public function objects(Request $request) {
        $this->requiredSession($request);
        $datas = Object::orderBy('created_at', 'desc')->paginate(20);
        return view('administrator.objects', [
            'active' => 'objects',
            'datas' => $datas
        ]);
    }

    public function feedbacks(Request $request) {
        $this->requiredSession($request);
        $datas = Feedback::orderBy('created_at', 'desc')->paginate(20);
        return view('administrator.feedbacks', [
            'active' => 'feedbacks',
            'datas' => $datas
        ]);
    }

    public function administrators(Request $request) {
        $this->requiredSession($request);
        $datas = Administrator::orderBy('created_at', 'desc')->paginate(20);
        return view('administrator.administrators', [
            'active' => 'administrators',
            'datas' => $datas
        ]);
    }

    public function signIn(Request $request) {
        if($request->session()->get('administrator')){
            return redirect('/administrator');
        }
        if ($request->isMethod('post')) {
            $administrator = Administrator::where('body_email', $request->input('email'))->where('body_password', md5($request->input('password')))->first();
            if($administrator){
                $request->session()->put('administrator', $administrator->id);
                return redirect('/administrator');
            }
        }
        return view('administrator.signIn');
    }

    public function signOut(Request $request) {
        $request->session()->forget('administrator');
        return redirect('/administrator/signIn');
    }

    public function usersExport(Request $request) {

        $this->requiredSession($request);

        $result = array(
            array(
                '用户编号',
                '账户状态',
                '介绍人',
                '电话号码',
                '账户余额',
                '累积交易',
                '累积盈利',
                '下线交易',
                '注册时间'
            )
        );

        $datas = User::all();
        foreach ($datas as $item) {

            if($item->is_disabled == 1) $status_name = '封停';
            else $status_name = '正常';

            $result[] = array(
                $item->id,
                $status_name,
                $item->id_introducer,
                $item->body_phone,
                $item->body_balance,
                $item->body_transactions,
                $item->body_bonus,
                $item->body_transactions_network,
                $item->created_at
            );

        }

        Excel::create('Users', function($excel) use($result) {
            $excel->sheet('Datas', function($sheet) use($result) {
                $sheet->fromArray($result);
            });
        })->export('xls');

    }

    public function ordersExport(Request $request) {

        $this->requiredSession($request);

        $result = array(
            array(
                '订单编号',
                '用户',
                '交易标的',
                '买入价格',
                '买入金额',
                '买入方向',
                '买入时长',
                '买入时间',
                '结算价格',
                '结算结果',
                '结算时间',
                '订单调控'
            )
        );

        $datas = Order::all();
        foreach ($datas as $item) {

            if($item->body_direction == 1) $direction_name = '看涨';
            else $direction_name = '看跌';

            $result_name = '亏损';
            if($item->body_is_draw == 1) $result_name = '平局';
            if($item->body_is_win == 1) $result_name = '盈利';

            $controlled_name = '否';
            if($item->body_is_controlled == 1) $controlled_name = '是';

            $result[] = array(
                $item->id,
                $item->user->body_phone,
                $item->object->body_name,
                $item->body_price_buying,
                $item->body_stake,
                $direction_name,
                $item->body_time,
                $item->created_at,
                $item->body_price_striked,
                $result_name,
                $item->striked_at,
                $controlled_name
            );

        }

        Excel::create('Orders', function($excel) use($result) {
            $excel->sheet('Datas', function($sheet) use($result) {
                $sheet->fromArray($result);
            });
        })->export('xls');

    }

    public function recordsExport(Request $request) {

        $this->requiredSession($request);

        $result = array(
            array(
                '记录编号',
                '用户',
                '关联用户',
                '关联充值',
                '关联提现',
                '变动缘由',
                '变动方向',
                '变动金额',
                '变动时间'
            )
        );
        $datas = Record::all();
        foreach ($datas as $item) {

            if($item->body_direction == 1) $direction_name = '收入';
            else $direction_name = '支出';

            $result[] = array(
                $item->id,
                $item->user->body_phone,
                $item->id_refer,
                $item->id_payRequest,
                $item->id_withdrawRequest,
                $item->body_name,
                $direction_name,
                $item->body_stake,
                $item->created_at
            );

        }

        Excel::create('Records', function($excel) use($result) {
            $excel->sheet('Datas', function($sheet) use($result) {
                $sheet->fromArray($result);
            });
        })->export('xls');

    }

    public function payRequestsExport(Request $request) {

        $this->requiredSession($request);

        $result = array(
            array(
                '充值编号',
                '用户',
                '金额',
                '充值方式',
                '流水编号',
                '申请时间',
                '入账时间'
            )
        );
        $datas = PayRequest::all();
        foreach ($datas as $item) {

            $gateway_name = '未知';

            if($item->body_gateway == 'wechat') $gateway_name = '微信支付';
            if($item->body_gateway == 'union') $gateway_name = '银联支付';
            if($item->body_gateway == 'staff') $gateway_name = '人工充值';

            $result[] = array(
                $item->id,
                $item->user->body_phone,
                $item->body_stake,
                $gateway_name,
                $item->body_transfer_number,
                $item->created_at,
                $item->processed_at
            );

        }

        Excel::create('PayRequests', function($excel) use($result) {
            $excel->sheet('Datas', function($sheet) use($result) {
                $sheet->fromArray($result);
            });
        })->export('xls');

    }

    public function withdrawRequestsExport(Request $request) {

        $this->requiredSession($request);

        $result = array(
            array(
                '提现编号',
                '用户',
                '金额',
                '开户银行',
                '开户名称',
                '开户网点',
                '开户帐号',
                '流水编号',
                '申请时间',
                '处理时间'
            )
        );
        $datas = WithdrawRequest::all();
        foreach ($datas as $item) {

            $bank_name = '未知';

            if($item->body_bank == 'ccb') $bank_name = '建设银行';
            if($item->body_bank == 'icbc') $bank_name = '工商银行';
            if($item->body_bank == 'boc') $bank_name = '中国银行';
            if($item->body_bank == 'abc') $bank_name = '农业银行';
            if($item->body_bank == 'comm') $bank_name = '交通银行';
            if($item->body_bank == 'spdb') $bank_name = '浦发银行';
            if($item->body_bank == 'ecb') $bank_name = '光大银行';
            if($item->body_bank == 'cmbc') $bank_name = '民生银行';
            if($item->body_bank == 'cib') $bank_name = '兴业银行';
            if($item->body_bank == 'cmb') $bank_name = '招商银行';
            if($item->body_bank == 'psbc') $bank_name = '邮政储蓄';

            $result[] = array(
                $item->id,
                $item->user->body_phone,
                $item->body_stake,
                $bank_name,
                $item->body_name,
                $item->body_deposit,
                $item->body_number,
                $item->body_transfer_number,
                $item->created_at,
                $item->processed_at
            );

        }

        Excel::create('WithdrawRequests', function($excel) use($result) {
            $excel->sheet('Datas', function($sheet) use($result) {
                $sheet->fromArray($result);
            });
        })->export('xls');

    }

    public function orderWillWin(Request $request) {
        
        if (env('ORDER_WILL_WIN')) {
            $this->modifyEnv([
                'ORDER_WILL_WIN' => 0,
                'ORDER_WILL_LOST' => 0
            ]);
        } else {
            $this->modifyEnv([
                'ORDER_WILL_WIN' => 1,
                'ORDER_WILL_LOST' => 0
            ]);
        }

        return back()->withInput();

    }

    public function orderWillLost(Request $request) {
        
        if (env('ORDER_WILL_LOST')) {
            $this->modifyEnv([
                'ORDER_WILL_LOST' => 0,
                'ORDER_WILL_WIN' => 0
            ]);
        } else {
            $this->modifyEnv([
                'ORDER_WILL_LOST' => 1,
                'ORDER_WILL_WIN' => 0
            ]);
        }

        return back()->withInput();

    }

    public function orderControl(Request $request) {
        
        if (env('ORDER_CONTROL')) {
            $this->modifyEnv([
                'ORDER_CONTROL' => 0
            ]);
        } else {
            $this->modifyEnv([
                'ORDER_CONTROL' => 1
            ]);
        }

        return back()->withInput();

    }

}