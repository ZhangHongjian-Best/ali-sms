<?php
/**
 * @Project aliSms
 * @Filename Sms.php
 * @Author zhang.hongjian <mr_zhanghj@sina.com>
 * @Date 2023/1/10
 * @Time 16:32
 */

namespace Hongjianzhang\AliSms;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Cache;

final class Sms
{

    /**
     * @var string appKey
     */
    private $appKey;

    /**
     * @var string appSecret
     */
    private $appSecret;

    /**
     * @var string 签名
     */
    private $signName;

    /**
     * @var string 发送模版
     */
    private $templateCode;

    /**
     * @var string 手机号
     */
    private $mobile;

    /**
     * @var string 缓存key
     */
    private $cacheKey;

    /**
     * @var int 验证码过期时间
     */
    private $ttl;

    /**
     * @var int 多少秒之内不能频繁发送验证码
     */
    private $inSeconds;

    /**
     * @var string 验证码
     */
    private $code;

    /**
     * @var bool 是否立即删除缓存
     */
    private $delNow;

    /**
     * @return mixed
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return mixed
     */
    public function getSignName()
    {
        return $this->signName;
    }

    /**
     * @return mixed
     */
    public function getTemplateCode()
    {
        return $this->templateCode;
    }

    /**
     * @return string
     */
    public function getMobile(): string
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile(string $mobile): Sms
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @param string $cacheKey
     * @return $this
     */
    public function setCacheKey(string $cacheKey = 'sendSmsCode'): Sms
    {
        $this->cacheKey = md5($cacheKey . '^_^' . $this->mobile);
        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTtl(int $ttl = 900): Sms
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return int
     */
    public function getInSeconds(): int
    {
        return $this->inSeconds;
    }

    /**
     * @param int $inSeconds
     * @return $this
     */
    public function setInSeconds(int $inSeconds = 60): Sms
    {
        $this->inSeconds = $inSeconds;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): Sms
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDelNow(): bool
    {
        return $this->delNow;
    }

    /**
     * @param bool $delNow
     */
    public function setDelNow($delNow = false): Sms
    {
        $this->delNow = $delNow;
        return $this;
    }


    /**
     * @param string $appKey
     * @param string $appSecret
     * @param string $signature
     * @param string $templateCode
     */
    public function __construct(string $appKey, string $appSecret, string $signature, string $templateCode)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->signName = $signature;
        $this->templateCode = $templateCode;
    }


    /**
     * 发送短信验证码
     * @return array
     */
    public function sendSmsCode()
    {
        if (!$this->getMobile()) {
            return resultError('请填写手机号');
        }
        $cacheValue = $this->getCacheValue();
        if (!empty($cacheValue) && isset($cacheValue[1])) {
            if (time() - $cacheValue[1] < $this->getInSeconds()) {
                return resultError('请不要频繁发送验证码');
            }
        }

        $code = randSmsCode();
        $client = $this->createClient();

        $sendSmsRequest = new SendSmsRequest([
            'signName' => $this->getSignName(),
            'templateCode' => $this->getTemplateCode(),
            'phoneNumbers' => $this->getMobile(),
            'templateParam' => \GuzzleHttp\json_encode(['code' => $code])
        ]);

        $result = $client->sendSms($sendSmsRequest)->body->toMap();

        if ($result['Code'] == 'OK') {
            $cacheValue = $code . '-' . time();
            Cache::put($this->getCacheKey(), $cacheValue, $this->getTtl());
            return resultSuccess();
        }
        return resultError($result['Message']);
    }

    /**
     * 使用AK&SK初始化账号Client
     * @return Dysmsapi
     */
    private function createClient()
    {
        $config = new Config([
            'accessKeyId' => $this->getAppKey(),
            'accessKeySecret' => $this->getAppSecret()
        ]);
        $config->endpoint = 'dysmsapi.aliyuncs.com';
        return new Dysmsapi($config);
    }

    /**
     * 验证短信验证码
     * @return array
     */
    public function checkCode()
    {
        if (!$this->getCode()) {
            return resultError('验证码必填');
        }
        $cacheData = $this->getCacheValue();
        if (!$cacheData || !$cacheData[0]) {
            return resultError('验证码已过期');
        }
        if ($this->getCode() != $cacheData[0]) {
            return resultError('验证码输入错误');
        }
        if ($this->getDelNow()) {
            $this->deleteCache();
        }
        return resultSuccess();
    }

    /**
     * 获取缓存key
     * @return array|false|string[]
     */
    private function getCacheValue()
    {
        $dataArr = Cache::get($this->getCacheKey());
        if ($dataArr) {
            $dataArr = explode('-', $dataArr);
            return $dataArr;
        }
        return [];
    }

    /**
     * 删除缓存key
     * @return void
     */
    private function deleteCache()
    {
        Cache::forget($this->getCacheKey());
    }
}