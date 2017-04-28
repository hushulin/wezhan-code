<?php

namespace App\Http\Controllers;

use DB;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use EasyWeChat\Foundation\Application;

use App\Http\Models\Captcha;
use App\Http\Models\Object;
use App\Http\Models\Order;
use App\Http\Models\Price;
use App\Http\Models\Line;
use App\Http\Models\User;
use App\Http\Models\Record;
use App\Http\Models\PayRequest;

class ApiController extends Controller {

    protected function insertLineItem($period, $object, $objectPrice, $objectPriceTime) {
        if(Line::where('id_object', $object->id)->where('body_period', $period)->where('created_at', '>=', date('Y-m-d H:i:s', time() - $period))->count() == 0) {
            $line = new Line;
            $line->id_object = $object->id;
            $line->body_period = $period;
            $line->body_open = sprintf('%.' . $object->body_price_decimal . 'f', $objectPrice);
            $line->body_high = $line->body_open;
            $line->body_low = $line->body_open;
            $line->body_close = $line->body_open;
            $line->body_volume = 0;
            $line->save();
            Line::where('id_object', $object->id)->where('body_period', $period)->where('created_at', '<', date('Y-m-d H:i:s', strtotime($objectPriceTime) - $period * 60))->delete();
        } else {
            $line = Line::where('id_object', $object->id)->where('body_period', $period)->where('created_at', '>=', date('Y-m-d H:i:s', time() - $period))->first();
            $line_closed_at = strtotime($line->created_at) + $line->body_period;
            if(time() < $line_closed_at) {
                if($objectPrice > $line->body_high){
                    $line->body_high = sprintf('%.' . $object->body_price_decimal . 'f', $objectPrice);
                }
                if($objectPrice < $line->body_low){
                    $line->body_low = sprintf('%.' . $object->body_price_decimal . 'f', $objectPrice);
                }
                $line->body_close = sprintf('%.' . $object->body_price_decimal . 'f', $objectPrice);
                $line->body_volume = $line->body_volume + mt_rand(0, 1);
                $line->save();
            }
        }
    }

    protected function insertLine($object, $objectPrice, $objectPriceTime) {

        if($object->body_status == 1){
            $this->insertLineItem(60, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(300, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(900, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(1800, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(3600, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(86400, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(604800, $object, $objectPrice, $objectPriceTime);
            $this->insertLineItem(2592000, $object, $objectPrice, $objectPriceTime);
        }

    }

    protected function insertPrice($object, $objectPrice, $objectPriceTime, $objectPriceMin, $objectPriceMax, $objectPriceInterval) {
        
        if(Price::where('id_object', $object->id)->where('body_price_time', date('Y-m-d H:i:s', time()))->count() == 0){
            if(Price::where('id_object', $object->id)->where('body_price_time', $objectPriceTime)->count() == 0){

                if(floatval($object->body_price) == floatval($objectPrice)){
                    $object->body_price_repeat = intval($object->body_price_repeat) + 1;
                    if ($object->body_price_repeat >= 180) {
                        $object->body_status = 0;
                    }
                } else {

                    $object->body_price_repeat = 0;
                    $object->body_status = 1;

                    $price = new Price;
                    $price->id_object = $object->id;
                    $price->body_price = sprintf('%.' . $object->body_price_decimal . 'f', $objectPrice);
                    $price->body_price_time = $objectPriceTime;
                    $price->save();

                    $object->body_price_previous = $object->body_price;
                    $object->body_price = $price->body_price;
                    $object->body_price_min = $objectPriceMin;
                    $object->body_price_max = $objectPriceMax;
                    $object->body_price_interval = $objectPriceInterval;
                    $object->save();
                    
                    Price::where('id_object', $object->id)->where('body_price_time', '<', date('Y-m-d H:i:s', strtotime($price->body_price_time) - 6400))->delete();

                }


            }
        } else {
            $price = Price::where('id_object', $object->id)->where('body_price_time', date('Y-m-d H:i:s', time()))->first();
            $object->body_price_previous = $object->body_price;
            $object->body_price = $price->body_price;
            $object->save();
        }

        $this->insertLine($object, $objectPrice, $objectPriceTime);

    }

    public function fetch() {


        $requestResults = file_get_contents('http://market.forex.com.cn/zhongfuMarketIndex/findAllPriceAjax.do?' . mt_rand(100000000, 999999999));
        
        if (!Cache::has(md5($requestResults))) {
            Cache::put(md5($requestResults), 'FETCHED', 4320);
            $requestResults = json_decode($requestResults, TRUE);
            $objects = Object::all();
            foreach ($objects as $item) {
                $this->insertPrice($item, $requestResults[$item->body_tag_forex]['sellPrice'], date('Y-m-d H:i:s', time()), $requestResults[$item->body_tag_forex]['sellPrice'], $requestResults[$item->body_tag_forex]['sellPrice'], 0);
            }
        }

        /*
        $requestResults = json_decode(file_get_contents('http://market.forex.com.cn/zhongfuMarketIndex/ajaxTable.do?' . mt_rand(100000000, 999999999)), TRUE);
        $responseLists = $requestResults['list'];
        $responseArray = array();

        foreach ($responseLists as $item) {
            $responseArray[$item['symbolCode']] = $item;
        }

        //die(print_r($responseArray['GBPJPY']));

        */
        


        echo('FETCH_DONE');

        /*
        $requestResults = json_decode(file_get_contents('http://market.forex.com.cn/zhongfuMarketIndex/ajaxTable.do?' . mt_rand(100000000, 999999999)), TRUE);
        $responseLists = $requestResults['list'];
        $responseArray = array();

        foreach ($responseLists as $item) {
            $responseArray[$item['symbolCode']] = $item;
        }
        
        $objects = Object::all();
        foreach ($objects as $item) {
            $this->insertPrice($item, $responseArray[$item->body_tag_forex]['sellPrice'], date('Y-m-d H:i:s', time()), $responseArray[$item->body_tag_forex]['sellPrice'], $responseArray[$item->body_tag_forex]['sellPrice'], 0);
        }
        */

        /*

        $requestString = 'http://hq.sinajs.cn/list=';
        $objects = Object::all();
        foreach($objects as $object) $requestString = $requestString . ',' .$object->body_tag;
        $requestString = str_replace('list=,', 'list=', $requestString);
	    $requestResults = explode(';', file_get_contents($requestString));
	    foreach ($requestResults as $item) {
            if(strstr($item, '=')){
		        $arrayForName = explode('=', $item);
		        $arrayForObject = explode(',', $arrayForName[1]);
		        $objectName = trim(str_replace('var hq_str_', '', $arrayForName[0]));
		        if($objectName && $object = Object::where('body_tag', $objectName)->first()){
                    if ($objectName == 'sh000010'
                    || $objectName == 'sh000300') {
                        $objectPrice = str_replace('"', '', $arrayForObject[3]);
                        $objectPriceTime = $arrayForObject[30] . ' ' . $arrayForObject[31];
                        $objectPriceMin = $arrayForObject[4];
                        $objectPriceMax = $arrayForObject[5];
                        $objectPriceInterval = $arrayForObject[6];
                    } else if ($objectName == 'rt_hkHSI') {
                        $objectPrice = str_replace('"', '', $arrayForObject[6]);
                        $objectPriceTime = str_replace('/', '-', $arrayForObject[17]) . ' ' . str_replace('"', '', $arrayForObject[18]);
                        $objectPriceMin = $arrayForObject[5];
                        $objectPriceMax = $arrayForObject[4];
                        $objectPriceInterval = 0;
                    } else if ($objectName == 'gb_$ndx'
                    || $objectName == 'gb_$dji'
                    || $objectName == 'gb_bidu'
                    || $objectName == 'gb_baba'
                    || $objectName == 'gb_jd') {
                        $objectPrice = str_replace('"', '', $arrayForObject[1]);
                        $objectPriceTime = $arrayForObject[3];
                        $objectPriceMin = $arrayForObject[6];
                        $objectPriceMax = $arrayForObject[7];
                        $objectPriceInterval = 0;
                    } else if ($objectName == 'hf_CL'
                    || $objectName == 'hf_GC'
                    || $objectName == 'hf_SI') {
                        $objectPrice = str_replace('"', '', $arrayForObject[0]);
                        $objectPriceTime = $arrayForObject[12] . ' ' . $arrayForObject[6];
                        $objectPriceMin = $arrayForObject[5];
                        $objectPriceMax = $arrayForObject[4];
                        $objectPriceInterval = $arrayForObject[1];
                    } else {
                        $objectPrice = $arrayForObject[1];
                        $objectPriceTime = str_replace('"', '', $arrayForObject[17]) . ' ' . str_replace('"', '', $arrayForObject[0]);
                        $objectPriceMin = $arrayForObject[7];
                        $objectPriceMax = $arrayForObject[6];
                        $objectPriceInterval = $arrayForObject[11];
                    }
                    $this->insertPrice($object, $objectPrice, $objectPriceTime, $objectPriceMin, $objectPriceMax, $objectPriceInterval);
		        }
            }
	    }

        */
    }

    public function compute() {
        
        $orders = Order::where('striked_at', '0000-00-00 00:00:00')->get();
        foreach($orders as $order){
            
            $order_striked_time = strtotime($order->created_at) + $order->body_time;
            if($order_striked_time <= time()){
                
                if($order->body_is_controlled == 0){
                    $object_latestPrice = Price::where('id_object', $order->id_object)->where('body_price_time', '<=', date('Y-m-d H:i:s', $order_striked_time))->orderBy('created_at', 'desc')->first();
                    $order->body_price_striked = $object_latestPrice->body_price;
                }

                $order->striked_at = date('Y-m-d H:i:s', time());
                if(floatval($order->body_price_buying) == floatval($order->body_price_striked)) {
                    $order->body_is_win = 0;
                    $order->body_is_draw = 1;
                } else if(floatval($order->body_price_buying) < floatval($order->body_price_striked)) {
                    $order->body_is_win = $order->body_direction == 1 ? 1 : 0;
                    $order->body_is_draw = 0;
                } else {
                    $order->body_is_win = $order->body_direction == 0 ? 1 : 0;
                    $order->body_is_draw = 0;
                }
                $order->save();

                $user = User::where('id', $order->id_user)->first();
                if($order->body_is_win == 1){

                    $user->body_balance = $user->body_balance + $order->body_stake + $order->body_bonus;
                    $user->body_bonus = $user->body_bonus + $order->body_bonus;
                    $user->save();

                    $record = new Record;
                    $record->id_user = $user->id;
                    $record->id_order = $order->id;
                    $record->body_name = $order->body_direction == 1 ? '看漲盈利' : '看跌盈利';
                    $record->body_direction = 1;
                    $record->body_stake = $order->body_stake + $order->body_bonus;
                    $record->save();

                } else if($order->body_is_draw == 1){

                    $user->body_balance = $user->body_balance + $order->body_stake;
                    $user->save();

                    $record = new Record;
                    $record->id_user = $user->id;
                    $record->id_order = $order->id;
                    $record->body_name = $order->body_direction == 1 ? '看漲退回' : '看跌退回';
                    $record->body_direction = 1;
                    $record->body_stake = $order->body_stake;
                    $record->save();

                }

            }
        }

        echo('COMPUTE_DONE');

    }

    protected function computeNumber($number, $direction, $margin) {

        $numberExplode = explode('.', $number);
        $numberDecimal = end($numberExplode);
        $numberDecimalLength = strlen($numberDecimal);
        $numberDecimalControl = pow(0.1, $numberDecimalLength);

        if (env('ORDER_WILL_WIN')) {
            if($direction == 1){
                return floatval($number) + $margin * $numberDecimalControl;
            } else {
                return floatval($number) - $margin * $numberDecimalControl;
            }
        } else {
            if($direction == 1){
                return floatval($number) - $margin * $numberDecimalControl;
            } else {
                return floatval($number) + $margin * $numberDecimalControl;
            }
        }

    }

    protected function computeRate($id) {
        $orders_count = Order::where('id_user', $id)->count();
        if($orders_count > 0){
            $orders_is_win = Order::where('id_user', $id)->where('body_is_win', '1')->count();
            return ($orders_is_win / $orders_count) * 100;
        } else return 0;
    }

    protected function computePriceItem($time, $order, $object, $margin) {

        $price = Price::firstOrNew(array(
            'id_object' => $order->id_object,
            'body_price_time' => date('Y-m-d H:i:s', $time),
            'created_at' => date('Y-m-d H:i:s', $time)
        ));

        if($order->body_stake > $price->body_rank) {
            $price->body_price = $this->computeNumber(sprintf('%.' . $object->body_price_decimal . 'f', $order->body_price_buying), $order->body_direction, $margin);
            $price->body_rank = $order->body_stake;
            $price->save();
        }

        return $price;

    }

    protected function computePrice($user, $order, $object) {
        
        $rate = $this->computeRate($user->id);

        if(floatval($order->body_stake) < 100) { 
            $rate = 0;
        } else {
            if(floatval($order->body_stake) >= 200) $rate = $rate + 10;
            if(floatval($order->body_stake) >= 500) $rate = $rate + 10;
            if(floatval($order->body_stake) >= 1000) $rate = $rate + 15;
        }

        if (env('ORDER_WILL_LOST') || env('ORDER_WILL_WIN')) {
            $rate = 100;
        }
        
        if(rand(0, 100) <= $rate) {

            $order_striked_time = strtotime($order->created_at) + $order->body_time;
            $price = $this->computePriceItem($order_striked_time, $order, $object, mt_rand(1, 3));
            $this->computePriceItem($order_striked_time - 4, $order, $object, 4);
            $this->computePriceItem($order_striked_time - 3, $order, $object, 3);
            $this->computePriceItem($order_striked_time - 2, $order, $object, 2);
            $this->computePriceItem($order_striked_time - 1, $order, $object, 1);
            $this->computePriceItem($order_striked_time + 1, $order, $object, 1);
            $this->computePriceItem($order_striked_time + 2, $order, $object, -1);

            $order->body_price_striked = $price->body_price;
            $order->body_is_controlled = 1;
            $order->save();

        }

    }

    protected function computeNetwork($user, $order) {

        $introducerIndex = 0;
        while ($user->id_introducer != 0) {

            $introducerIndex++;
            $introducer = User::where('id', $user->id_introducer)->first();
            $introducer->body_transactions_network = $introducer->body_transactions_network + $order->body_stake;

            if($introducerIndex <= 3){

                $bonus = floatval($order->body_stake * floatval(env("AGENT_$introducerIndex")));
                if($bonus < 0.01) $bonus = 0.01;

                $introducer->body_balance = floatval($introducer->body_balance) + $bonus;

                $record = new Record;
                $record->id_user = $introducer->id;
                $record->id_refer = $user->id;
                $record->body_name = '推廣收入';
                $record->body_direction = 1;
                $record->body_stake = $bonus;
                $record->save();

            }

            $introducer->save();
            $user = $introducer;

        }

    }

    public function automate() {

        $this->fetch();
        $this->compute();

        return response()->json([
            'result' => 'OK'
        ]);

    }

    public function objects() {

        $result = array(
            'timestamp' => time(),
            'objects' => array()
        );

        $objects = Object::where('is_disabled', '0')->orderBy('body_rank', 'desc')->get();
        $result['objects'] = $objects->toArray();

        if($user = session('wechat.oauth_user')){
            $user = User::where('id_wechat', $user->id)->first();
            $result['user'] = $user->toArray();
        }

        return response()->json($result);

    }

    public function objectsDetail(Request $request, Response $response, $id, $period) {

        $object = Object::find($id);
        $lines = Line::where('id_object', $object->id)->where('body_period', $period)->orderBy('id', 'desc')->take(60)->get();

        if($object->body_status == 1) {
            $object['status'] = TRUE;
        } else {
            $object['status'] = FALSE;
        }
        
        $result = array(
            'timestamp' => time(),
            'period' => $period,
            'object' => $object,
            'lines' => $lines->toArray()
        );

        return response()->json($result);

    }

    public function objectsDetailUpdate(Request $request, Response $response, $id, $period) {

        $object = Object::find($id);
        $lines = Line::where('id_object', $object->id)->where('body_period', $period)->orderBy('id', 'desc')->first();

        if($object->body_status == 1) {
            $object['status'] = TRUE;
        } else {
            $object['status'] = FALSE;
        }

        $result = array(
            'timestamp' => time(),
            'period' => $period,
            'object' => $object,
            'lines' => $lines->toArray()
        );

        return response()->json($result);

    }

    public function ordersDetail(Request $request, Response $response, $id) {
        
        $item = Order::find($id);
        $result = array(
            'timestamp' => time(),
            'item' => $item
        );

        return response()->json($result);

    }

    public function update(Request $request, Response $response) {
        
        $result = array(
            'user' => NULL,
            'timestamp' => time(),
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'objects' => array()
        );

        if($request->input('object', null)){
            $objects = Object::where('id', $request->input('object'))->get();
        } else {
            $objects = Object::orderBy('body_rank', 'desc')->get();
        }

        foreach($objects as $object){
            $result_item = $object->toArray();
            $result_item_latestPrice = Price::where('id_object', $object->id)->orderBy('created_at', 'desc')->first();

            if ($request->input('mode') == 'fs') {
                $cacheTime = time();
                if(!Cache::has('prices_' . $object->id . '_' . $cacheTime)) {
                    Cache::put('prices_' . $object->id . '_' . $cacheTime, Price::where('id_object', $object->id)->where('body_price_time', '<=', date('Y-m-d H:i:s', $cacheTime))->where('body_price_time', '>', date('Y-m-d H:i:s', strtotime($result_item_latestPrice->body_price_time) - 900))->orderBy('created_at', 'desc')->get()->toArray(), 1);
                }
                $result_item['prices'] = Cache::get('prices_' . $object->id . '_' . $cacheTime);
            } else {

                if ($object->body_tag == 'sh000010'
                || $object->body_tag == 'sh000300') {
                    $requestString = 'http://money.finance.sina.com.cn/quotes_service/api/jsonp_v2.php/DATA/CN_MarketData.getKLineData?symbol=#CODE#&scale=5&ma=no&datalen=30';
                } else if ($object->body_tag == 'gb_$ndx') {
                    $requestString = 'http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/DATA/US_MinKService.getMinK?symbol=.ndx&type=5';
                } else if ($object->body_tag == 'gb_$dji') {
                    $requestString = 'http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/DATA/US_MinKService.getMinK?symbol=.dji&type=5';
                } else if ($object->body_tag == 'gb_bidu') {
                    $requestString = 'http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/DATA/US_MinKService.getMinK?symbol=bidu&type=5';
                } else if ($object->body_tag == 'gb_baba') {
                    $requestString = 'http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/DATA/US_MinKService.getMinK?symbol=baba&type=5';
                } else if ($object->body_tag == 'gb_jd') {
                    $requestString = 'http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/DATA/US_MinKService.getMinK?symbol=jd&type=5';
                } else {
                    $requestString = 'http://vip.stock.finance.sina.com.cn/forex/api/jsonp.php/DATA/NewForexService.getMinKline?symbol=#CODE#&scale=5&datalen=30';
                }

                $requestString = str_replace('#CODE#', $object->body_tag, $requestString);
                $requestResult = explode('DATA(', file_get_contents($requestString));
                $requestResult = str_replace(')', '', $requestResult[1]);
                $requestResult = str_replace('{day:', '{d:', $requestResult);
                $requestResult = str_replace(',open:', ',o:', $requestResult);
                $requestResult = str_replace(',high:', ',h:', $requestResult);
                $requestResult = str_replace(',low:', ',l:', $requestResult);
                $requestResult = str_replace(',close:', ',c:', $requestResult);
                $requestResult = str_replace(',volume:', ',v:', $requestResult);
                $requestResult = str_replace('d', '"d"', $requestResult);
                $requestResult = str_replace('o', '"o"', $requestResult);
                $requestResult = str_replace('l', '"l"', $requestResult);
                $requestResult = str_replace('h', '"h"', $requestResult);
                $requestResult = str_replace('c', '"c"', $requestResult);
                $requestResult = str_replace('v', '"v"', $requestResult);
                $requestResult = str_replace(';', '', $requestResult);
                $result_item['prices'] = json_decode($requestResult, TRUE);
            }

            if($object->body_status == 1) {
                $result_item['status'] = TRUE;
            } else {
                $result_item['status'] = FALSE;
            }

            if($user = session('wechat.oauth_user')){
                $user = User::where('id_wechat', $user->id)->first();
                $result_item['orders'] = Order::where('id_object', $object->id)->where('id_user', $user->id)->where('striked_at', '0000-00-00 00:00:00')->orderBy('created_at', 'desc')->get()->toArray();
            }

            $result['objects'][] = $result_item;
        }

        if($user = session('wechat.oauth_user')){

            $user = User::where('id_wechat', $user->id)->first();

            $latestStrikedOrder = Order::where('id_object', $object->id)->where('id_user', $user->id)->where('striked_at', '<>', '0000-00-00 00:00:00')->orderBy('created_at', 'desc')->first();
            if($latestStrikedOrder) $latestStrikedOrder = $latestStrikedOrder->toArray();
            else $latestStrikedOrder = NULL;

            $result['user'] = array(
                'balance' => floatval($user->body_balance),
                'latestStrikedOrder' => $latestStrikedOrder
            );

        }

        return response()->json($result);

    }

    public function captchaCreate(Request $request, Response $response) {
        
        $code = mt_rand(100000, 999999);

        $captcha = new Captcha;
        $captcha->body_mobile = $request->input('mobile');
        $captcha->body_code = $code;
        $captcha->save();

        $requestUri = env('SMS_BASE');
        $requestUri = str_replace('#1#', env('SMS_KEY'), $requestUri);
        $requestUri = str_replace('#2#', $request->input('mobile'), $requestUri);
        $requestUri = str_replace('#3#', urlencode("【恒信】你的验证码是" . $code . "，请在10分钟内输入。"), $requestUri);

        $result = file_get_contents($requestUri);
        return response()->json(['result' => $result]);

    }

    public function orderCreate(Request $request, Response $response) {

        if(!$user = session('wechat.oauth_user')){
            return response()->json([
                'error' => '身份驗證失敗，請重新打開頁面再試'
            ]);
        }

        $carbon = Carbon::now();
        if($carbon->hour < 6 && $carbon->hour > 2) {
            return response()->json([
                'error' => '非交易时间无法进行交易'
            ]);
        }

        if(is_null($request->input('object', NULL))
        || is_null($request->input('stake', NULL))
        || is_null($request->input('time', NULL))
        || is_null($request->input('direction', NULL))){
            return response()->json([
                'error' => '參數提交不全，請重新打開頁面再試'
            ]);
        }

        if($request->input('stake') != 20
        && $request->input('stake') != 50
        && $request->input('stake') != 100
        && $request->input('stake') != 200
        && $request->input('stake') != 500
        && $request->input('stake') != 1000
        && $request->input('stake') != 2000
        && $request->input('stake') != 5000
        && $request->input('stake') != 10000){
            return response()->json([
                'error' => '參數提交錯誤，請重新打開頁面再試'
            ]);
        }

        if($request->input('time') != 60
        && $request->input('time') != 120
        && $request->input('time') != 180
        && $request->input('time') != 240
        && $request->input('time') != 300
        && $request->input('time') != 900
        && $request->input('time') != 1800
        && $request->input('time') != 3600){
            return response()->json([
                'error' => '參數提交錯誤，請重新打開頁面再試'
            ]);
        }

        if($request->input('direction') != 1
        && $request->input('direction') != 0){
            return response()->json([
                'error' => '參數提交錯誤，請重新打開頁面再試'
            ]);
        }

        if(!$object = Object::find($request->input('object'))){
            return response()->json([
                'error' => '參數提交錯誤，請重新打開頁面再試'
            ]);
        }

        if($object->is_disabled > 0) {
            return response()->json([
                'error' => '參數提交錯誤，請重新打開頁面再試'
            ]);
        }

        if((strtotime($object->updated_at) + 180) < time()) {
            return response()->json([
                'error' => '休市期間無法進行交易'
            ]);
        }

        if($object->body_status == 0){
            return response()->json([
                'error' => '休市期間無法進行交易'
            ]);
        }
        
        if(!$user = User::where('id_wechat', $user->id)->first()){
            return response()->json([
                'error' => '身份驗證失敗，請重新打開頁面再試'
            ]);
        }
        
        if(floatval($user->body_balance) < $request->input('stake')){
            return response()->json([
                'error' => '帳戶可用餘額不足，請先充值後再交易'
            ]);
        }

        if($user->is_disabled > 0){
            return response()->json([
                'error' => '帳戶已被封禁，无法进行交易'
            ]);
        }

        DB::beginTransaction();

        $user->body_balance = floatval($user->body_balance) - $request->input('stake');
        $user->body_transactions = floatval($user->body_transactions) + $request->input('stake');
        $user->save();

        if($user->body_balance < 0) {
            DB::rollback();
        } else {

            $order = new Order;
            $order->id_user = $user->id;
            $order->id_object = $object->id;
            $order->body_price_buying = $object->body_price;
            $order->body_stake = $request->input('stake');
            $order->body_bonus = $object->body_profit * $request->input('stake');
            $order->body_direction = $request->input('direction');
            $order->body_time = $request->input('time');
            $order->save();

            $record = new Record;
            $record->id_user = $user->id;
            $record->id_order = $order->id;
            $record->body_name = $request->input('direction') == 1? '買入看漲' : '買入看跌';
            $record->body_direction = 0;
            $record->body_stake = $order->body_stake;
            $record->save();

        }

        DB::commit();

        if(!$order){
            return response()->json([
                'error' => '下单过于频繁，请稍后再试'
            ]);
        } else {
            $this->computeNetwork($user, $order);

            if(env('ORDER_CONTROL')){
                $this->computePrice($user, $order, $object);
            }

        }

        $result = $order->toArray();

        $order_striked_time = strtotime($order->created_at) + $order->body_time;
        $result['distance'] = intval($order->body_time);
        $result['distance_year'] = date('Y', $order_striked_time);
        $result['distance_month'] = date('m', $order_striked_time);
        $result['distance_day'] = date('d', $order_striked_time);
        $result['distance_hour'] = date('H', $order_striked_time);
        $result['distance_minute'] = date('i', $order_striked_time);
        $result['distance_second'] = date('s', $order_striked_time) + 1;

        return response()->json([
            'result' => $result
        ]);


    }

    public function payRequestUpdate(Request $request, Response $response, $id) {

        if(!$payRequest = PayRequest::find($id)){
            return response()->json([
                'result' => 'FAIL'
            ]);
        }

        if($payRequest->processed_at == '0000-00-00 00:00:00'){
            return response()->json([
                'result' => 'FAIL'
            ]);
        }

        return response()->json([
            'result' => 'OK'
        ]);

    }

}