<?php
namespace App\Services\Wx;

use App\Services\Interfaces\Wx\WxEventInterface;
use App\Services\UserService;
use App\Services\WeixinService;
use App\Models\User;
use App\Services\AccountService;
use App\Models\UserAccountRecord;
use App\Models\AdminSet;
use App\Models\UserAccount;

/**
 * 微信推送事件
 *
 * @author lilin
 *         wx(tel):13408099056
 *         qq:182436607
 *        
 */
class WxEventService extends WxToolService implements WxEventInterface
{
    public function msgShortvideo()
    {}

    /**
     * {@inheritDoc}
     * @see \App\Services\Interfaces\Wx\WxEventInterface::eventClick()
     */
    public function eventClick(array $eventData)
    {
        return $this->repText($eventData['FromUserName'], $eventData['rep_content']);
    }

    public function msgLocation()
    {}

    public function eventLocation()
    {}

    public function msgVideo()
    {}

    /**
     * {@inheritDoc}
     * @see \App\Services\Interfaces\Wx\WxEventInterface::msgText()
     */
    public function msgText(array $eventData)
    {
       return $this->repText($eventData['FromUserName'], '已收到'); 
    }

    public function eventSubscribe()
    {}

    public function msgVoice()
    {}

    public function msgLink()
    {}

    /**
     * {@inheritDoc}
     * @see \App\Services\Interfaces\Wx\WxEventInterface::eventQrCode()
     */
    public function eventQrCode(array $eventData)
    {
        $repText = config('console.wx_subscribe_tip');
        $addMoney = false;
        
        $eventKey = str_replace('qrscene_', '', $eventData['EventKey']);
        
        //解析内容
        $data = json_decode($eventKey);
        
        //查找有没有此用户数据,没有创建用户,建立关系
        $user = UserService::getUserInfoByConditions([['openid'=>$eventData['FromUserName']]]);
        if ($user && isset($user['data']->inviter) && $user['data']->inviter){
            return $this->repText($eventData['FromUserName'], $repText);
//             return self::returnCode('sys.fail');
        }
     
        //获取用户
        $weixin     = new WeixinService();
        $userInfo   = $weixin->getUserInfo($eventData['FromUserName']);
        
        if (!isset($user['data']->id) || $user['data']->id != $data->id){
            $userInfo['inviter'] = $data->id;
            $addMoney = true;
        }

        $addUser = User::updateOrCreate(['openid' => $eventData['FromUserName']], $userInfo);
        
        //邀请加钱
        if ($addMoney && $addUser){
            $uid            = $data->id;
            $money          = AdminSet::where('type_name','attention')->first(['value'])->value;
            
            //如果用户没有帐户,则开通一个
            $exist = UserAccount::where('uid',$uid)->exists();
            if (!$exist){
                UserAccount::create(['uid'=>$uid]);
            }
            
            $objectType     = UserAccountRecord::OBJECT_TYPE_3;
            $balance        = UserAccount::where('uid',$uid)->first(['balance'])->balance;
            $objectId       = $addUser->id;
            
            self::setLog('邀请用户加钱',0,['$uid'=>$uid,'money'=>$money['money'],'objectType'=>$objectType,'balance'=>$balance,'objectId'=>$objectId]);
            
            AccountService::addMoney($uid, $money['money'], $objectType, $balance, $objectId);
        }
        
        return $this->repText($eventData['FromUserName'], $repText);
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Services\Interfaces\Wx\WxEventInterface::repText()
     */
    public function repText($toUsername, $content)
    {
        $data = [
            'ToUserName'    => $toUsername,
            'FromUserName'  => config('console.wx_auth_account'),
            'CreateTime'    => time(),
            'MsgType'       => 'text',
            'Content'       => $content
        ];
        
        return self::arrayToXml($data);
    }
}

