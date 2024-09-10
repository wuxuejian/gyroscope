<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\utils;

use App\Http\Service\Other\UploadService;
use crmeb\exceptions\AdminException;
use Intervention\Image\Facades\Image;

/**
 * 下载图片到本地
 * Class DownloadImage.
 * @method $this thumb(bool $thumb) 是否生成缩略图
 * @method $this thumbWidth(int $thumbWidth) 缩略图宽度
 * @method $this thumHeight(int $thumHeight) 缩略图宽度
 * @method $this path($path) 存储位置
 */
class DownloadImage
{
    // 是否生成缩略图
    protected $thumb = false;

    // 缩略图宽度
    protected $thumbWidth = 300;

    // 缩略图高度
    protected $thumHeight = 300;

    // 存储位置
    protected $path = 'attach';

    /**
     * @var string[]
     */
    protected $rules = ['thumb', 'thumbWidth', 'thumHeight', 'path'];

    /**
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->rules)) {
            if ($name === 'path') {
                $this->{$name} = $arguments[0] ?? 'attach';
            } else {
                $this->{$name} = $arguments[0] ?? null;
            }
            return $this;
        }
        throw new \RuntimeException('Method does not exist' . __CLASS__ . '->' . $name . '()');
    }

    /**
     * 获取即将要下载的图片扩展名.
     * @param string $url
     * @param string $ex
     * @return array|string[]
     */
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $_empty = ['file_name' => '', 'ext_name' => $ex];
        if (! $url) {
            return $_empty;
        }
        if (strpos($url, '?')) {
            $_tarr = explode('?', $url);
            $url   = trim($_tarr[0]);
        }
        $arr = explode('.', $url);
        if (! is_array($arr) || count($arr) <= 1) {
            return $_empty;
        }
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = ! $ext_name ? $ex : $ext_name;
        return ['file_name' => md5($url) . '.' . $ext_name, 'ext_name' => $ext_name];
    }

    /**
     * 下载图片.
     * @param string $name
     * @return mixed
     */
    public function downloadImage(string $url, $name = '')
    {
        if (! $name) {
            // TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $ext               = $downloadImageInfo['ext_name'];
            $name              = $downloadImageInfo['file_name'];
            if (! $name) {
                throw new AdminException(400725);
            }
        } else {
            $ext = $this->getImageExtname($name)['ext_name'];
        }
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'JPG', 'JPEG', 'PNG', 'GIF'])) {
            throw new AdminException(400558);
        }
        if (strstr($url, 'http://') === false && strstr($url, 'https://') === false) {
            $url = 'http:' . $url;
        }
        $url = str_replace('https://', 'http://', $url);
        if ($this->path == 'attach') {
            $date_dir = date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
            $to_path  = $this->path . '/' . $date_dir;
        } else {
            $to_path = $this->path;
        }
        $upload = UploadService::init(1);
        if (! file_exists($upload->uploadDir($to_path) . '/' . $name)) {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
            $size = strlen(trim($content));
            if (! $content || $size <= 2) {
                throw new AdminException(400726);
            }
            if ($upload->to($to_path)->down($content, $name) === false) {
                throw new AdminException(400727);
            }
            $imageInfo = $upload->getDownloadInfo();
            $path      = $imageInfo['dir'];
            if ($this->thumb) {
                Image::make(public_path($path))->fit($this->thumbWidth, $this->thumHeight)->save(public_path($path));
                $this->thumb = false;
            }
        } else {
            $path              = '/uploads/' . $to_path . '/' . $name;
            $imageInfo['name'] = $name;
        }
        $date['path']       = $path;
        $date['name']       = $imageInfo['name'];
        $date['size']       = $imageInfo['size'] ?? '';
        $date['mime']       = $imageInfo['type'] ?? '';
        $date['image_type'] = 1;
        $date['is_exists']  = false;
        return $date;
    }
}
