<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Service\Attach;

use App\Constants\CacheEnum;
use App\Http\Dao\Attach\SystemAttachDao;
use App\Http\Service\BaseService;
use App\Http\Service\Other\UploadService;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ServicesException;
use crmeb\exceptions\UploadException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use OSS\Core\OssException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 附件管理service
 * Class AttachService.
 */
class AttachService extends BaseService
{
    // 默认模块
    public const RELATION_TYPE_DEFAULT = '';

    // 汇报模块
    public const RELATION_TYPE_DAILY = 'daily';

    // 回款/续费模块
    public const RELATION_TYPE_BILL = 'bill';

    // 合同附件
    public const RELATION_TYPE_CONTRACT = 'contract';

    // 客户附件
    public const RELATION_TYPE_CLIENT = 'client';

    // 客户跟进附件
    public const RELATION_TYPE_FOLLOW = 'follow';

    // 发票附件
    public const RELATION_TYPE_INVOICE = 'invoice';

    // 打卡附件
    public const RELATION_TYPE_ATTENDANCE_CLOCK = 'attendance_clock';

    // 联系人
    public const RELATION_TYPE_LIAiSON = 'liaison';

    // 项目
    public const RELATION_TYPE_PROGRAM = 'program';

    // 财务模块
    public const RELATION_TYPE_FINANCE = 'finance';

    // 模块类型
    public const RELATION_TYPE = [
        self::RELATION_TYPE_DEFAULT          => 0,
        self::RELATION_TYPE_DAILY            => 1,
        self::RELATION_TYPE_BILL             => 2,
        self::RELATION_TYPE_CONTRACT         => 3,
        self::RELATION_TYPE_CLIENT           => 4,
        self::RELATION_TYPE_FOLLOW           => 5,
        self::RELATION_TYPE_INVOICE          => 6,
        self::RELATION_TYPE_ATTENDANCE_CLOCK => 7,
        self::RELATION_TYPE_LIAiSON          => 8,
        self::RELATION_TYPE_PROGRAM          => 9,
        self::RELATION_TYPE_FINANCE          => 10,
    ];

    /**
     * @var SystemAttachDao
     */
    public $dao;

    /**
     * 关联模块.
     */
    protected int $relationType = 0;

    /**
     * 关联ID.
     */
    protected int $relationId = 0;

    /**
     * 附件ID.
     */
    protected int $id = 0;

    /**
     * SystemAttachServices constructor.
     */
    public function __construct(SystemAttachDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取图片列表.
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getImageList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, ['*'], $page, $limit, 'id');
        $count          = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除图片.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function delImg(array|string $ids, int $entId = 1)
    {
        $ids = is_string($ids) ? explode(',', $ids) : $ids;
        if (empty($ids)) {
            throw new AdminException('请选择要删除的图片');
        }
        $entId = $entId ?: 1;
        foreach ($ids as $v) {
            $attinfo = $this->dao->get(['id' => (int) $v, 'entid' => $entId])?->toArray();
            if ($attinfo) {
                try {
                    $upload = UploadService::init($attinfo['up_type']);
                    if ($attinfo['up_type'] == 1) {
                        if (! str_starts_with($attinfo['att_dir'], '/')) {
                            $attinfo['att_dir'] = '/' . $attinfo['att_dir'];
                        }
                    }
                    if ($attinfo['att_dir']) {
                        $upload->delete($attinfo['att_dir']);
                    }
                } catch (\Throwable $e) {
                }
                $this->dao->delete((int) $v);
            }
        }
    }

    /**
     * 删除图片.
     * @param mixed $uid
     * @param mixed $entid
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function delCover(array $ids, $entid, $uid)
    {
        if (empty($ids)) {
            throw new AdminException('请选择要删除的图片');
        }
        foreach ($ids as $v) {
            $attinfo = $this->dao->get(['id' => (int) $v, 'entid' => $entid]);
            if ($attinfo && $uid == $attinfo['uid']) {
                try {
                    $upload = UploadService::init($attinfo['up_type']);
                    if ($attinfo['up_type'] == 1) {
                        if (strpos($attinfo['att_dir'], '/') == 0) {
                            $attinfo['att_dir'] = substr($attinfo['att_dir'], 1);
                        }
                        if ($attinfo['att_dir']) {
                            $upload->delete($attinfo['att_dir']);
                        }
                    } else {
                        if ($attinfo['name']) {
                            $upload->delete($attinfo['name']);
                        }
                    }
                } catch (\Throwable $e) {
                }
                $this->dao->delete((int) $v);
            }
        }
    }

    /**
     * 前端上传保存信息.
     * @param int $pid 分类ID
     * @param array $file 文件内容
     * @param int $way 来源：1、总后台；2、分后台；3、用户
     * @param int $entid 企业ID
     * @param string $uuid 上传用户ID
     * @throws BindingResolutionException
     */
    public function save(int $pid, array $file = [], int $way = 2, int $entid = 1, string $uuid = ''): array
    {
        $fileType              = pathinfo($file['name'], PATHINFO_EXTENSION);
        $data['name']          = $file['name'];
        $data['real_name']     = $file['name'];
        $data['att_dir']       = $file['url'];
        $data['thumb_dir']     = $file['url'];
        $data['att_size']      = $file['size'];
        $data['att_type']      = $file['type'];
        $data['file_ext']      = $fileType;
        $data['up_type']       = (int) sys_config('upload_type', 1);
        $data['cid']           = $pid;
        $data['uid']           = $uuid;
        $data['way']           = $way;
        $data['entid']         = $entid;
        $data['relation_type'] = $this->relationType;
        $data['relation_id']   = $this->relationId;
        $model                 = $this->dao->create($data);
        return [
            'src'       => $model->att_dir,
            'url'       => $model->att_dir,
            'attach_id' => $model->id,
            'id'        => $model->id,
            'size'      => $data['att_size'],
            'name'      => $data['real_name'],
        ];
    }

    /**
     * 图片上传.
     * @return mixed
     */
    public function upload(int $pid, string $file, int $upload_type = 1, int $type = 0, int $way = 1, int $entid = 1, string $uuid = '')
    {
        if ($upload_type == 1) {
            $upload_type = sys_config('upload_type', 1) ?? 1;
        }
        try {
            $path   = $this->make_path('attach', 2, true);
            $upload = UploadService::init($upload_type);
            $res    = $upload->to($path)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            }
            $fileInfo = $upload->getUploadInfo();
            $fileType = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            if ($fileInfo && $type == 0) {
                $data['name']          = $fileInfo['name'];
                $data['real_name']     = $fileInfo['real_name'];
                $data['att_dir']       = $fileInfo['dir'];
                $data['thumb_dir']     = $fileInfo['dir'];
                $data['att_size']      = $fileInfo['size'];
                $data['att_type']      = $fileInfo['type'];
                $data['file_ext']      = $fileType;
                $data['up_type']       = $upload_type;
                $data['cid']           = $pid;
                $data['uid']           = $uuid;
                $data['way']           = $way;
                $data['entid']         = $entid;
                $data['relation_type'] = $this->relationType;
                $data['relation_id']   = $this->relationId;
                $model                 = $this->dao->create($data);
                $this->id              = $model->id;
            }
            $id = $model->id ?? 0;
            return ['src' => $model->att_dir, 'url' => $model->att_dir, 'attach_id' => $id, 'id' => $id, 'size' => $data['att_size'], 'name' => $data['real_name']];
        } catch (\Throwable $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 文件上传.
     * @return mixed
     */
    public function fileUpload(string $file, int $upload_type)
    {
        if ($upload_type == 1) {
            $upload_type = sys_config('upload_type', 1) ?? 1;
        }
        try {
            $path   = $this->make_path('source', 2, true);
            $upload = UploadService::init($upload_type);
            $res    = $upload->to($path)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            }
            return $res->filePath;
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 修改附件分类.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function move(array $data)
    {
        $res = $this->dao->move($data);
        if (! $res) {
            throw new AdminException('移动失败或不能重复移动到同一分类下');
        }
    }

    /**
     * 获取考核封面图.
     * @param mixed $where
     * @return array
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getAssessCover($where)
    {
        $id             = app()->get(AttachCateService::class)->value(['keyword' => 'assessCover', 'is_show' => 1], 'id');
        [$page, $limit] = $this->getPageValue();
        $where['cid']   = $id;
        $list           = $this->dao->getList($where, ['*'], $page, $limit, 'id');
        $count          = $this->dao->count($where);
        return compact('list', 'count');
    }

    public function getRelationType(): int
    {
        return $this->relationType;
    }

    public function setRelationType(string $relationType): AttachService
    {
        $this->relationType = self::RELATION_TYPE[$relationType] ?? self::RELATION_TYPE_DEFAULT;
        return $this;
    }

    public function getRelationId(): int
    {
        return $this->relationId;
    }

    public function setRelationId(int $relationId, int $eid = 0): AttachService
    {
        if ($relationId < 1 && $eid && $this->relationType === 4) {
            $relationId = $eid;
        }
        $this->relationId = $relationId;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 保存关联.
     */
    public function saveRelation(array|string $ids, string $uid, int $relationId, string $relationType): bool|int
    {
        $entId = 1;
        $ids   = array_filter(array_map('intval', ! is_array($ids) ? explode(',', $ids) : $ids));
        return $this->transaction(function () use ($entId, $ids, $uid, $relationType, $relationId) {
            $where = ['uid' => $uid, 'entid' => $entId, 'relation_type' => self::RELATION_TYPE[$relationType]];
            $list  = $this->dao->column(array_merge($where, ['relation_id' => $relationId]), 'att_dir', 'id');
            if ($list && $diff = array_diff(array_keys($list), $ids)) {
                $this->delCover($diff, $entId, $uid);
            }

            if (empty($ids)) {
                return true;
            }
            return $this->dao->update(array_merge($where, ['id' => $ids]), ['relation_id' => $relationId]);
        });
    }

    /**
     * 本地文件上传.
     * @param mixed $file
     */
    public function localFileUpload($file): mixed
    {
        try {
            $path   = $this->make_path('source', 2, true);
            $upload = UploadService::init(1);
            $res    = $upload->to($path)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            }
            return $res->filePath;
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 重命名.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function setRealName(int $id, int $entId, string $realName): bool
    {
        $info = $this->dao->get(['id' => $id, 'entid' => $entId]);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        if ($info->real_name === $realName) {
            return true;
        }
        $info->real_name = $realName;
        return $info->save();
    }

    /**
     * 附件数据.
     * @param mixed $limit
     * @param null|mixed $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getListByRelation(array $where, array $field = ['*'], int $page = 0, $limit = 0, $sort = null, array $with = []): array
    {
        if (isset($where['relation_type'])) {
            foreach ($where['relation_type'] as &$item) {
                $item = self::RELATION_TYPE[$item] ?? -1;
                if ($item < 0) {
                    throw $this->exception('关联类型错误');
                }
            }
        }

        return $this->dao->getListByRelation($where, $field, $page, $limit, $sort, $with);
    }

    /**
     * 获取附件列表数据.
     * @param null|mixed $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getRelationList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        [$page, $limit] = $this->getPageValue();
        $count          = $this->dao->getListCountByRelation($where);
        $list           = $this->dao->getListByRelation($where, $field, $page, $limit, $sort, array_merge($with, ['card']));
        return $this->listData($list, $count);
    }

    /**
     * 获取关联模块指定附件数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getListByRelationType(string $relationType, array $ids = [], array $field = ['id', 'att_dir', 'att_size', 'real_name'], int $relationId = 0): array
    {
        $where['relation_type'] = self::RELATION_TYPE[$relationType] ?? -1;
        if ($relationId) {
            $where['relation_id'] = $relationId;
        } else {
            $where['id'] = $ids;
        }
        return $this->dao->getList($where, $field);
    }

    /**
     * 获取关联模块指定附件数量.
     * @param int $relationId
     * @throws BindingResolutionException
     */
    public function getCountByRelationType(string $relationType, mixed $relationId): int
    {
        $where = ['relation_type' => self::RELATION_TYPE[$relationType] ?? -1, 'relation_id' => $relationId];
        return $this->dao->count($where);
    }

    /**
     * 更新文件.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws OssException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws \ReflectionException
     */
    public function updateFile(int $uid, int $fileId, mixed $file, bool $isDoc = false): void
    {
        $folder = $this->dao->get($fileId, ['file_name', 'file_url', 'upload_type'])?->toArray();
        $key    = 'upload.key.' . app('request')->ip();
        if (Cache::tags([CacheEnum::TAG_OTHER])->get($key) > 500) {
            throw new ServicesException('您今日上传文件的次数已达上限');
        }
        if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
            Cache::tags([CacheEnum::TAG_OTHER])->increment($key, 1);
        } else {
            Cache::tags([CacheEnum::TAG_OTHER])->add($key, 1, now()->endOfDay()->timestamp - now()->timestamp);
        }
        $time     = now()->toArray();
        $dir      = 'attach/' . $time['year'] . '/' . $time['month'] . '/' . $time['day'];
        $upload   = UploadService::init();
        $fileSize = 0;
        if ($isDoc) {
            $name     = pathinfo($folder['file_name'], PATHINFO_FILENAME) . '.' . pathinfo($folder['file_name'], PATHINFO_EXTENSION);
            $pathFile = public_path($name);
            // 创建一个新的Word文档
            $phpWord = new PhpWord();
            // 添加一个新的空白页
            $section = $phpWord->addSection();
            $file    = str_replace('<br>', '<br/>', $file);
            // 将HTML内容添加到文档中
            Html::addHtml($section, $file);
            $writer = IOFactory::createWriter($phpWord);
            $writer->save($pathFile);
            $fileSize = filesize($pathFile);
            $res      = $upload->to($dir)->validate()->stream(file_get_contents($pathFile), md5(micro_time()) . '.' . pathinfo($folder['file_name'], PATHINFO_EXTENSION));
            if ($res === false) {
                unlink($pathFile);
                throw new UploadException($upload->getError());
            }
            unlink($pathFile);
        } else {
            $res = $upload->to($dir)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            }
        }
        $fileInfo                = $upload->getUploadInfo();
        $fileInfo['file_ext']    = pathinfo($fileInfo['dir'], PATHINFO_EXTENSION);
        $fileInfo['name']        = pathinfo($fileInfo['real_name'], PATHINFO_FILENAME);
        $fileInfo['upload_type'] = (int) sys_config('upload_type', 1);
        $this->transaction(function () use ($fileInfo, $fileId, $folder, $upload, $fileSize, $uid) {
            $upload = UploadService::init($folder['up_type']);
            $fileInfo['dir'] != $folder['file_url'] && $upload->delete($folder['file_url']);
            $this->dao->update($fileId, [
                'uid'         => uid_to_uuid($uid),
                'file_ext'    => $fileInfo['file_ext'],
                'file_size'   => $fileSize ?: $fileInfo['size'],
                'file_url'    => $fileInfo['att_dir'],
                'upload_type' => $fileInfo['up_type'],
            ]);
            return true;
        });
    }

    /**
     * 上传路径转化,默认路径.
     * @param mixed $path
     * @return string
     * @throws \Exception
     */
    protected function make_path($path, int $type = 2, bool $force = false)
    {
        $path = DIRECTORY_SEPARATOR . ltrim(rtrim($path));
        $path .= match ($type) {
            1       => DIRECTORY_SEPARATOR . date('Y'),
            2       => DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'),
            3       => DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d'),
            default => '',
        };
        try {
            if (is_dir(public_path('uploads') . $path) || mkdir(public_path('uploads') . $path, 0777, true)) {
                return trim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '.');
            }
            return '';
        } catch (\Exception $e) {
            if ($force) {
                throw new \Exception($e->getMessage());
            }
            return '无法创建文件夹，请检查您的上传目录权限：' . public_path('uploads') . DIRECTORY_SEPARATOR . 'attach' . DIRECTORY_SEPARATOR;
        }
    }
}
