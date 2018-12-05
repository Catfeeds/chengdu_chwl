<?php
namespace App\Services;

use YueCode\Cos\QCloudCos;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Redis;

/**
 * 图片上传
 *
 * @author liugang
 *        
 */
class UploadService extends BaseService
{

    /**
     * 给图片加二维码
     *
     * @param string $url
     *            二维码链接
     * @param string $img
     *            产品原图
     * @return array|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function addQrcodeToPic($url, $img)
    {
        $redisKey = config('console.redis_key.upload_img') . md5($url . $img);
        
        $result = Redis::get($redisKey);
        
        if (! $result) {
            $qrImg = QrCode::format('png')->margin(0)
                ->size(130)
                ->generate($url);
            $src = imagecreatefromstring($qrImg);
            
            $picInfo = getimagesize($img);
            
            if (! $picInfo) {
                return self::returnCode('sys.dataDoesNotExist');
            }
            
            $ext = explode('/', $picInfo['mime']);
            
            // 合并二维图片指定x,y
            switch ($ext[1]) {
                case 'png':
                    $img = imagecreatefrompng($img);
                    break;
                case 'jpeg':
                case 'jpg':
                    $img = imagecreatefromjpeg($img);
                    break;
                case 'gif':
                    $img = imagecreatefromgif($img);
                    break;
            }
            
            imagecopymerge($img, $src, 9, 1195, 0, 0, 130, 130, 100);
            
            // 本地存储路径
            $savePath = public_path('img/' . time() . rand() . '.png');
            imagepng($img, $savePath);
            
            imagedestroy($img);
            imagedestroy($src);
            
            // 重新上传云服务器
            $result = json_encode(self::upload($savePath));
            
            // 删除本地图片
            if (file_exists($savePath)) {
                unlink($savePath);
            }
            
            Redis::set($redisKey, $result);
        }
        
        return self::returnCode('sys.success', json_decode($result));
    }

    /**
     * 本地文件上传
     *
     * @param string $file
     * @param string $dst
     * @param number $returnIsArray
     * @return array|array[]|mixed[]|\Illuminate\Foundation\Application[]
     */
    static public function upload($file, $dst = 'product')
    {
        $picInfo = getimagesize($file);
        
        if (! $picInfo) {
            return [];
        }
        
        $fileName = time() . rand();
        $ext = explode('/', $picInfo['mime']);
        $dstPath = $dst . '/' . date('Ym') . '/' . $fileName . '.' . $ext[1];
        
        $ret = QCloudCos::upload(env('BUCKET'), $file, $dstPath);
        
        $ret = json_decode($ret, true);
        
        if ($ret['code'] == 0) {
            $data = [
                'name' => $dstPath,
                'width' => $picInfo[0],
                'height' => $picInfo[1],
                'mime' => $picInfo['mime']
            ];
            return $data;
        } else {
            return [];
        }
    }

    static public function uploadPic($file, $dst = 'product', $returnIsArray = 1)
    {
        $picInfo = getimagesize($file);
        
        if (! $picInfo) {
            return self::returnCode('sys.dataDoesNotExist');
        }
        
        $srcPath = $file->getPathname();
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . rand();
        $dstPath = $dst . '/' . date('Ym') . '/' . $fileName . '.' . $extension;
        $size = $file->getSize();
        
        $ret = QCloudCos::upload(env('BUCKET'), $srcPath, $dstPath);
        
        $ret = json_decode($ret, true);
        
        if ($ret['code'] == 0) {
            if ($returnIsArray) {
                $data = [
                    'name' => $dstPath,
                    'width' => $picInfo[0],
                    'height' => $picInfo[1],
                    'mime' => $picInfo['mime'],
                    'size' => $size
                ];
            } else {
                $data = [
                    'src' => config('console.pic_url') . $dstPath,
                    'title' => ''
                ];
            }
            
            return self::returnCode('sys.success', $data);
        } else {
            return self::returnCode('sys.fail');
        }
    }
}