<?php
namespace App\Services\Admin;

use App\Models\AdminSet;

class AdminSetService extends BaseService
{

    /**
     * 根据类型名称获取后台设置
     *
     * @param string $typeName
     *            类型名称
     * @return array|array[]|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function getAdminSetByTypeName($typeName)
    {
        $adminSet = AdminSet::where('type_name', $typeName)->first();
        
        return self::returnCode('sys.success', $adminSet);
    }

    /**
     * 根据ID和类型名称修改后台设置
     * 
     * @param integer $id
     *            数据id
     * @param string $typeName
     *            类型名称
     * @param array $data
     *            数组值
     * @return array|array[]|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function updateAdminSet($id, $typeName, $data)
    {
        $adminSet = AdminSet::where('id', $id)->where('type_name', $typeName)->first();
        
        if (! $adminSet) {
            return self::returnCode('sys.dataDoesNotExist');
        }
        
        $adminSet->value = $data;
        
        $result = $adminSet->save();
        
        return $result ? self::returnCode('sys.success') : self::returnCode('sys.fail');
    }
}