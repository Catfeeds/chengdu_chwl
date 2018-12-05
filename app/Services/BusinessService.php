<?php
namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLoginLog;
use App\Events\BusinessLogin;
use App\Models\Order;
use Carbon\Carbon;
use App\Jobs\deliverGoodsJob;
use App\Jobs\distributionJob;

/**
 * 商家管理平台
 *
 * @author lilin 
 * wx(tel):13408099056 
 * qq:182436607
 *
 */
class BusinessService extends BaseService
{
    /**
     * 已核销
     * @var integer
     */
    const CLASS_TYPE_1 = 1;
    
    /**
     * 未核销
     * @var integer
     */
    const CLASS_TYPE_2 = 2;
    
    /**
     * 地方订单
     * @var integer
     */
    const CLASS_TYPE_3 = 3;
    
    /**
     * 地方订单商家确认发货
     *
     * @param integer $uid
     * @param integer $orderId
     * @param string $expressCompany
     * @param string $expressNumber
     * @return array
     */
    static public function placeDeliverGoods($uid, $orderId, $expressCompany, $expressNumber)
    {
        $order = Order::where('business_id', $uid)->where('id', $orderId)->where('type', Order::TYPE_PLACE)->first(['id','uid','express_company','express_number','status']);
        
        // 订单是否存在
        if (!$order){
            return self::returnCode('sys.dataDoesNotExist');
        }
        
        // 订单状态是否正确
        if ($order->status != Order::STATUS_PAID){
            return self::returnCode('sys.statusIsNotNormal');
        }
        
        $ext = [
            'express_company' => $expressCompany,
            'express_number' => $expressNumber
        ];
        
        $result = OrderService::changeOrderStatus($order, Order::STATUS_SHIPPED, $ext);
        
        if ($result) { // 修改成功加入7天自动收货
            $minutes = config('console.sys_deliver_goods_day');
            $job = (new deliverGoodsJob($order))->delay(Carbon::now()->addMinutes($minutes))->onQueue('deliverGoods');
            dispatch($job);
            
            return self::returnCode('sys.success');
        }
        
        return self::returnCode('sys.fail');
    }
    
    /**
     * 核销记录,未核销的订单
     *
     * @param integer $uid
     * @param array $search
     * @param integer $type
     * @param integer $page
     * @param integer $limit
     * @return array
     */
    static public function getOrders($uid, $search, $type = 1, $page = 1, $limit = 20)
    {
        $query = Order::with([
            'standard' => function ($query) {
                $query->select(['id','name']);
            },'extend'=>function($query){
                
            }])->where('business_id', $uid);
        
        switch ($type){
            case self::CLASS_TYPE_1:
                $fields = ['id','code','sn','name','quantity','money','name','tel','verification_time','product_id','standard_id'];
                $query->where('type','<>',Order::TYPE_PLACE)->where('status', Order::STATUS_COMPLETED);
                break;
            case self::CLASS_TYPE_2:
                $fields = ['id','sn','name','quantity','money','name','tel','pay_time','product_id','standard_id'];
                $query->where('type','<>',Order::TYPE_PLACE)->whereIn('status', [Order::STATUS_PAID, Order::STATUS_RESERVED]);
                break;
            case self::CLASS_TYPE_3:
                $fields = ['id','sn','name','quantity','money','name','tel','pay_time','product_id','standard_id','status'];
                $query->where('type',Order::TYPE_PLACE)->where('status', '>', Order::STATUS_UNPAID);
                break;
        }
        
        $query->where(function($query) use($search, $type){
            if (!is_null($search['name'])){
                $query->where('name',$search['name']);
            }
            if (!is_null($search['tel'])){
                $query->where('tel',$search['tel']);
            }
            if (!is_null($search['sn'])){
                $query->where('sn',$search['sn']);
            }
            if (!is_null($search['code']) && $type == self::CLASS_TYPE_1){
                $query->where('code',$search['code']);
            }
        });
        
        $list = $query->orderBy('id','desc')->paginate($limit, $fields);
        
        $list->each(function($item){
            
            $item->addHidden(['extend']);
            $product = [
                'id' => $item->extend['copy']['id'],
                'name' => $item->extend['copy']['name'],
                'subtitle' => $item->extend['copy']['subtitle'],
            ];
            
            
            $item->product = $product;
        });
        
        return self::returnCode('sys.success', $list);
    }
    
    /**
     * 核销订单
     *
     * @param   integer $uid
     * @param   string  $code
     * @return  array
     */
    static public function verifyTheOrder($uid, $code, $showInfo = false)
    {
        $startTime = microtime();
        //订单信息
        //$fields = ['id','code','type','sn','uid','business_id','product_id','standard_id','quantity','money','name','tel','status','verification_time','remark'];
        $order = Order::with(['extend'=>function($query){
           
        },'standard'=>function($query){
            $query->select(['id','name']);
        }])->where('business_id', $uid)->where('type','<>', Order::TYPE_PLACE)->where('code',$code)->first();
        
        //订单是否存在
        if (!$order){
            self::setLog('核销订单出错:订单是否存在',$startTime, $order);
            return self::returnCode('sys.dataDoesNotExist');
        }
        
        //未支付的订单也是不存在的
        if ($order->status == Order::STATUS_UNPAID){
            self::setLog('核销订单出错:未支付的订单也是不存在的',$startTime, $order);
            return self::returnCode('sys.dataDoesNotExist');
        }
        
        $order->addHidden(['extend']);
        $product = [
            'id' => $order->extend['copy']['id'],
            'name' => $order->extend['copy']['name'],
            'subtitle' => $order->extend['copy']['subtitle'],
        ];
        $order->product = $product;
        
        if (!$showInfo){ //展示信息
            return self::returnCode('sys.success', $order);
        } 
        
        // 开始走核销流程
          
        // 是否已核销过
        if ($order->status == Order::STATUS_COMPLETED) {
            return self::returnCode('sys.codeIsAuthenticated');
        }

        $ext = [
            'verification_time' => Carbon::now()
        ];
        
        $result = OrderService::changeOrderStatus($order, Order::STATUS_COMPLETED, $ext);
        
        if ($result) {
            // 收货成功后,触发分销,一级,二级,团队
            dispatch((new distributionJob($order))->onQueue('distribution'));
            
            return self::returnCode('sys.success');
        } else {
            return self::returnCode('sys.fail');
        }
    }
    
    /**
     * 通过手机验证码修改密码
     *
     * @param   string  $mobile
     * @param   string  $code
     * @param   string  $pwd
     * @return  array|array[]|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function updatePwd($mobile, $code, $pwd, $salt)
    {
        $fields = ['id','username','mobile','password','salt','status'];
        $business = self::getBusinessByField('mobile', $mobile, $fields);
        
        //检测帐号是否存在
        if ($business['code'] != self::SUCCESS_CODE){
            return $business;
        }
        
        //状态是否正常
        if ($business['data']->status != Business::STATUS_NORMAL){
            return self::returnCode('sys.statusIsNotNormal');
        }
        
        //测试手机验证码是否正确
        $verifyCode = SmsService::verifyCode($mobile, $code, 'forget_pwd');
        if ($verifyCode['code'] != self::SUCCESS_CODE){
            return $verifyCode;
        }

        //重置密码
        return $business['data']->where('id',$business['data']->id)->update(['password'=>$pwd,'salt'=>$salt]) ? self::returnCode('sys.success') : self::returnCode('sys.fail');
    }
    
    /**
     * 忘记手机号 发送短信
     *
     * @param string $mobile
     * @return array|array[]|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function forgetPwd($mobile)
    {
        // 验证手机号是否存在
        $fields = ['id','username','mobile','password','salt','status'];
        $business = self::getBusinessByField('mobile', $mobile, $fields);
        
        //检测帐号是否存在
        if ($business['code'] != self::SUCCESS_CODE){
            return $business;
        }
        
        //状态是否正常
        if ($business['data']->status != Business::STATUS_NORMAL){
            return self::returnCode('sys.statusIsNotNormal');
        }
        
        // 发送短信
        $send = SmsService::sendSms($mobile, 'forget_pwd');
        if (!$send['code'] == self::SUCCESS_CODE){
            return $send;
        }
        
        return self::returnCode('sys.success',['username'=>$business['data']->username,'mobile'=>$business['data']->mobile]);
    }
    
    /**
     * 商家登陆
     *
     * @param   string  $userName
     * @param   string  $pwd
     * @param   array   $log ['platform','login_ip']
     * @return  array
     */
    static public function login($userName, $pwd, $log)
    {
        //判断帐号类型
        $type = self::_getAccountType($userName);
        
        //帐户信息
        $fields = ['id','username','password','status'];
        $business = self::getBusinessByField($type, $userName, $fields);
        
        //检测帐号是否存在
        if ($business['code'] != self::SUCCESS_CODE){
            return $business;
        }
        
        //状态是否正常
        if ($business['data']->status != Business::STATUS_NORMAL){
            return self::returnCode('sys.statusIsNotNormal');
        }

        //密码是否正确
        if ($pwd != $business['data']->password){
            return self::returnCode('sys.incorrect_password');
        }
        
        //设置登陆状态
        $data = event(new BusinessLogin($business['data'], $log));
        
        return $data[0];
    }
    
    /**
     * 获取商家密码盐值
     *
     * @param string $username
     * @return array
     */
    static public function getSalt($username)
    {
        //判断帐号类型
        $type = self::_getAccountType($username);
        
        return self::getBusinessByField($type, $username, ['salt']);
    }
    
    /**
     * 指定一个字段和值查找数据
     *
     * @param   string $field
     * @param   string $value
     * @param   array $fields
     * @return  array
     */
    static public function getBusinessByField($field, $value, $fields)
    {
        $data = Business::where($field,$value)->first($fields);
        
        return $data ? self::returnCode('sys.success',$data) : self::returnCode('sys.dataDoesNotExist');
    }
    
    /**
     * 帐号是否存在
     *
     * @param   string $account
     * @param   string $type
     * @return  array
     */
    static public function accountExistsOrNot($account, $type)
    {
        switch ($type) {
            case 'mobile':
                $exists = Business::where('mobile', $account)->exists();
                break;
            case 'username':
                $exists = Business::where('username', $account)->exists();
                break;
        }
        
        return $exists ? self::returnCode('sys.success') : self::returnCode('sys.dataDoesNotExist');
    }
    
    /**
     * 判断帐号是哪一种类型
     *
     * @param string $account
     * @return string
     */
    static private function _getAccountType ($account)
    {
        $type = 'username';
        
        if (preg_match('/^\d{11}$/', $account)) {
            $type = 'mobile';
        }elseif (preg_match("/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([.a-zA-Z0-9_-])+([.a-zA-Z0-9_-]+)+([.a-zA-Z0-9_-])$/",$account)){
            $type = 'email';
        }else{
            $type = 'username';
        }
        
        return $type;
    }

    /**
     * 登陆日志
     *
     * @param array $log
     * @param boolean $clearErrNum
     * @return array
     */
    static public function setLoginLog($log)
    {
        if (config('console.login_delete_other_log')){
            BusinessLoginLog::where('uid', $log['uid'])->delete();
        }
        
        $result = BusinessLoginLog::create($log);
        $business = self::getBusinessByField('id', $log['uid'], ['id','name']);
        $result->business = $business['data'];
        
        return self::returnCode('sys.success', $result);
    }
    
    /**
     * 登陆信息 合并 加密
     *
     * @param Business $user
     * @return string
     */
    static public function passwordEncrypt(Business $user, $time)
    {
        $salted = $user->mobile . $user->password . $user->salt . config('console.reg_pwd_sn') . $time;
        return hash('sha256', $salted);
    }
    
    /**
     * 验证用户token是否正确
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    static public function getLoginLog($token, $field = ['*'])
    {
        $loginLog = BusinessLoginLog::where('token', $token)->first($field);
        
        if ($loginLog) {
            return self::returnCode('sys.success', $loginLog);
        } else {
            return self::returnCode('sys.authenticationFailed');
        }
    }
}

