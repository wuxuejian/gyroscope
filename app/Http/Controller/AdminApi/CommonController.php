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

namespace App\Http\Controller\AdminApi;

use App\Constants\CacheEnum;
use App\Constants\CloudEnum;
use App\Constants\UserEnum;
use App\Http\Contract\Company\CompanyInterface;
use App\Http\Requests\user\UserLoginRequest;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\Client\ClientInvoiceService;
use App\Http\Service\Cloud\CloudFileService;
use App\Http\Service\CommonService;
use App\Http\Service\Notice\NoticeRecordService;
use App\Http\Service\Other\UploadService;
use App\Http\Service\System\SystemBackupService;
use App\Http\Service\System\SystemCityService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Mews\Captcha\Captcha;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\RouteAttributes\Attributes\Any;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * 公共控制器
 * Class CommonController.
 */
#[Prefix('ent/common')]
class CommonController extends AuthController
{
    /**
     * 网站配置.
     * @return mixed
     */
    #[Get('config', '获取网站配置', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function getConfig()
    {
        $defaultLogo           = sys_config('ent_website_logo', '');
        $defaultSiteTitle      = sys_config('site_name', '');
        $globalWatermarkStatus = (int) sys_config('global_watermark_status', 0);
        $wpsType               = sys_config('wps_type', 0);
        $logo                  = $this->isEnt && ! empty($this->entInfo['logo']) ? $this->entInfo['logo'] : $defaultLogo;
        $siteTitle             = $this->isEnt && ! empty($this->entInfo['title']) ? $this->entInfo['title'] : $defaultSiteTitle;
        return $this->success([
            'site_logo'               => $logo,
            'site_title'              => $siteTitle,
            'avatar'                  => $this->userInfo['avatar'],
            'account'                 => $this->userInfo['account'],
            'enterprise_name'         => $this->entInfo['enterprise_name'] ?? '',
            'global_watermark_status' => $globalWatermarkStatus,
            'wps_type'                => $wpsType,
        ]);
    }

    /**
     * 退出登录.
     * @return mixed
     */
    #[Get('logout', '退出登录', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function logout()
    {
        auth('admin')->logout();
        return $this->success('退出成功', tips: 0);
    }

    /**
     * 获取上传token.
     * @return JsonResponse|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('upload_key', '获取上传token', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function getTempKeys()
    {
        $upload                   = UploadService::init();
        $type                     = (int) sys_config('upload_type', 1);
        [$key,$path,$contentType] = $this->request->getMore([
            ['key', ''],
            ['path', ''],
            ['contentType', ''],
        ], true);
        if ($type === 5) {
            if (! $key || ! $contentType) {
                return app('json')->fail('缺少参数');
            }
            $res = $upload->getTempKeys($key, $path, $contentType);
        } else {
            $res = $upload->getTempKeys();
        }
        return $res ? $this->success($res) : $this->fail('获取失败');
    }

    /**
     * 上传文件.
     * @return mixed
     */
    #[Post('upload', '上传文件', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function upload(AttachService $services)
    {
        [$pid, $file, $relationType, $relationId, $eid] = $this->request->postMore([
            ['cid', 0],
            ['file', 'file'],
            ['relation_type', ''],
            ['relation_id', 0],
            ['eid', ''],
        ], true);

        $key = 'upload.key.' . $this->request->ip();

        if (Cache::tags([CacheEnum::TAG_OTHER])->get($key) > 500) {
            return $this->fail('您今日上传文件的次数已达上限');
        }

        if (Cache::tags([CacheEnum::TAG_OTHER])->has($key)) {
            Cache::increment($key, 1);
        } else {
            Cache::add($key, 1, Carbon::today()->endOfDay()->getTimestamp() - Carbon::today()->getTimestamp());
        }

        try {
            $fileInfo = $services->setRelationType($relationType)
                ->setRelationId((int) $relationId, (int) $eid)
                ->upload((int) $pid, $file, 1, 0, 2, $this->entId, $this->uuid);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        $fileInfo['url'] = link_file($fileInfo['url']);
        return $this->success('上传成功', $fileInfo);
    }

    /**
     * 获取下载地址
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('download_url', '获取下载地址', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function getDownloadUrl(): mixed
    {
        [$version, $type, $fileId] = $this->request->getMore([
            ['version', 0],
            ['type', ''],
            ['file_id', ''],
        ], true);

        switch ($type) {
            case 'apply':
                $data = [
                    'url'  => '/template/apply.xlsx',
                    'name' => '邀请成员表格模板.xlsx',
                    'type' => 'local',
                ];
                $downloadUrl = get_download_url('', $data);
                break;
            case 'folder':
                $fileService = app()->get(CloudFileService::class);
                $folder      = $fileService->get(['file_sn' => $fileId, 'type' => 0])?->toArray();
                if (! $fileId || ! $folder) {
                    return $this->fail('资源不存在');
                }
                $spaceId = explode('/', trim($folder['path'], '/'))[0];
                $fileService->checkAuth((int) $spaceId, auth('admin')->id(), $folder['id'], CloudEnum::DOWNLOAD_AUTH, [
                    'id' => $folder['id'],
                ]);
                $downloadUrl = get_download_url($fileId, $version ? ['version' => $version, 'folder' => 1] : ['folder' => 1]);
                break;
            default:
                if (! $fileId) {
                    return $this->fail('缺少FileId');
                }
                $downloadUrl = get_download_url($fileId, $version ? ['version' => $version] : []);
        }

        return $this->success(['download_url' => $downloadUrl]);
    }

    /**
     * 消息列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('message', '获取消息列表', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function message(NoticeRecordService $services): mixed
    {
        return $this->success($services->getMessageList(
            $this->uuid,
            $this->entId,
            $this->request->get('cate_id', ''),
            $this->request->get('title', '')
        ));
    }

    /**
     * 修改消息状态
     */
    #[Put('message/{id}/{isRead}', '修改消息状态', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function updateMessage(NoticeRecordService $services, $id, $isRead): mixed
    {
        if (! $id) {
            return $this->fail('common.empty.attrs');
        }
        $messageInfo = $services->get($id);
        if (! $messageInfo) {
            return $this->fail('消息不存在');
        }
        $messageInfo->is_read = $isRead;
        if ($messageInfo->save()) {
            return $this->success('common.update.succ');
        }
        return $this->fail('common.update.fail');
    }

    /**
     * 获取默认数据路径.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Post('initData', '获取默认数据路径', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function initData()
    {
        $where = $this->request->postMore([
            ['version', ''],
        ]);
        $path = app()->get(SystemBackupService::class)->value($where, 'path');
        return $this->success(['url' => $path ? sys_config('site_url') . $path : '']);
    }

    /**
     * @return mixed
     */
    #[Get('auth', '获取信息', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function auth()
    {
        return $this->success();
    }

    /**
     * @return mixed
     */
    #[Get('version', '获取版本信息', ['auth.admin', 'ent.auth', 'ent.log'])]
    public function getVersion()
    {
        return $this->success([
            'version' => getVersion('version'),
            'label'   => 48,
            'product' => 'oa',
        ]);
    }

    /**
     * 获取网址
     */
    #[Get('site_address', '获取网址')]
    public function getSiteAddress(): mixed
    {
        return $this->success(['address' => sys_config('site_url', config('app.url'))]);
    }

    /**
     * 图像验证码
     * @return array|mixed
     * @throws \Exception
     */
    #[Get('captcha', '获取验证码')]
    public function captcha(Captcha $captcha)
    {
        return $this->success($captcha->create('user', true));
    }

    /**
     * 站点配置.
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    #[Get('site', '站点配置')]
    public function siteConfig(CompanyInterface $services)
    {
        $type = sys_config('login_password_type');
        $type = is_array($type) ? $type : [0, 2];
        sort($type);
        $typeStr = implode('', $type);
        return $this->success([
            // 备案号
            'site_record_number' => sys_config('site_record_number'),
            // 地址
            'site_address' => $services->getCompanyAddress(),
            // 电话号码
            'site_tel' => sys_config('site_tel'),
            // logo
            'site_logo'       => $services->getCompanyInfo(),
            'password_type'   => $typeStr,
            'password_length' => sys_config('login_password_length'),
            'version_name'    => getVersion('version'),
        ]);
    }

    /**
     * 验证码短信发送
     * @param AdminService $service
     * @return mixed
     * @throws ValidationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Post('verify', '验证码短信发送')]
    public function verify(UserLoginRequest $request, AdminService $adminService, CommonService $service)
    {
        $request->scene('phone')->check();
        [$phone, $key, $types, $from] = $request->postMore([
            ['phone', ''],
            ['key', ''],
            ['types', 0],
            ['from', 0], // 来源：默认为登录操作
        ], true);
        if ($from) {
            if ($adminService->exists(['phone' => $phone])) {
                if ($adminService->value(['phone' => $phone], 'status') == UserEnum::USER_LOCKING) {
                    return $this->fail('该手机号已被锁定');
                }
            } elseif (! sys_config('registration_open', 0)) {
                return $this->fail('短信发送失败，未注册的手机号');
            }
        }
        if ($service->smsVerifyCode($phone, $key, $types)) {
            return $this->success('短信发送成功');
        }
        return $this->fail('短信发送失败');
    }

    /**
     * 获得短信发送key.
     * @return mixed
     */
    #[Get('verify/key', '获得短信发送key')]
    public function verifyCode(CommonService $service)
    {
        return $this->success($service->smsVerifyKey());
    }

    /**
     * 查找城市数据.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Get('city', '查找城市数据')]
    public function city()
    {
        /** @var SystemCityService $systemCity */
        $systemCity = app()->get(SystemCityService::class);

        return $this->success($systemCity->cityTree());
    }

    /**
     * 下载文件.
     * @return \Illuminate\Http\Response|SymfonyResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Any('download', '下载文件')]
    public function download(CloudFileService $services)
    {
        $signature = $this->request->get('signature');
        if (! $signature) {
            return Response::make()->setStatusCode(500);
        }

        // 验签
        try {
            $decrypted = Crypt::decryptString($signature);
        } catch (DecryptException $e) {
            return Response::make()->setStatusCode(500);
        }

        $decrypted = json_decode($decrypted, true);
        $type      = $decrypted['type'] ?? 'db';
        $isFolder  = $decrypted['folder'] ?? false;

        switch ($type) {
            case 'db':// 从数据库验证读取下载
                if (! isset($decrypted['fileId'])) {
                    return Response::make()->setStatusCode(500);
                }
                try {
                    if ($isFolder) {
                        $folder = app()->get(CloudFileService::class)->get(['file_sn' => $decrypted['fileId'], 'type' => 0]);
                        if (! $folder) {
                            return Response::make()->setStatusCode(404);
                        }
                        $url  = $folder->file_url;
                        $name = $folder->name . '.' . $folder->file_ext;
                        ++$folder->download_count;
                        $folder->save();
                    } else {
                        $where = ['file_id' => $decrypted['fileId']];
                        if (isset($decrypted['version']) && $decrypted['version']) {
                            $where['version'] = $decrypted['version'];
                        } else {
                            $where['is_master'] = 1;
                        }
                        $fileInfo = $services->get($where, ['id', 'download_count', 'upload_type', 'url', 'name']);
                        if (! $fileInfo) {
                            return Response::make()->setStatusCode(404);
                        }
                        // 记录下载次数等事件
                        ++$fileInfo->download_count;
                        $fileInfo->save();
                        $url  = $fileInfo->url;
                        $name = $fileInfo->name;
                    }

                    // 远程资源下载
                    $name     = str_replace(['/', '\\'], '&', $name);
                    $response = new SymfonyResponse();
                    $response->headers->set('Content-Disposition', 'attachment; filename=' . $name);
                    if (str_starts_with($url, 'http')) {
                        //                        return Response::streamDownload(function () use ($url) {
                        //                            echo Http::withoutVerifying()->get($url);
                        //                        }, $name);
                        $response->setContent(file_get_contents($url));
                        return $response;
                    }

                    $response->setContent(file_get_contents(public_path($url)));
                    return $response;
                } catch (\Throwable $e) {
                    return Response::make()->setStatusCode(500);
                }
            case 'local':// 本地下载
                if (! isset($decrypted['url'])) {
                    return Response::make()->setStatusCode(500);
                }
                $response = new SymfonyResponse();
                $response->headers->set('Content-Disposition', 'attachment; filename=' . $decrypted['name']);
                $response->setContent(file_get_contents(public_path($decrypted['url'])));
                return $response;
                //                return Response::download(public_path($decrypted['url']), $decrypted['name']);
            default:
                return Response::make()->setStatusCode(404);
        }
    }

    /**
     * 发票回调.
     * @return JsonResponse
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    #[Any('invoice/call_back', '发票回调')]
    public function invoiceCallBack(ClientInvoiceService $service)
    {
        $res = $service->invoiceCallBack();
        if (! $res) {
            return Response::json(['status' => 400]);
        }
        if (isset($res['unique'])) {
            return Response::json($res);
        }
        return Response::json(['status' => 200]);
    }
}
