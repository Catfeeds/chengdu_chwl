<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSet extends Model
{

    /**
     * 邀请关注公众号奖励
     * @var string
     */
    const TYPE_NAME_ATTENTION = 'attention';
    
    /**
     * 提现提示
     * @var string
     */
    const TYPE_NAME_WITHDRAWAL_PROMPT = 'withdrawal_prompt';
    
    /**
     * 组建团队设置
     * @var string
     */
    const TYPE_NAME_TEAM_SETTING = 'team_setting';
    
    protected $guarded = [];

    protected $casts = [
        'value' => 'json'
    ];
}
