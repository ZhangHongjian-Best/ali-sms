<?php
/**
 * @Project aliSms
 * @Filename Demo.php
 * @Author zhang.hongjian <mr_zhanghj@sina.com>
 * @Date 2023/1/10
 * @Time 17:12
 */

namespace Hongjianzhang\AliSms\Test;

use Hongjianzhang\AliSms\Sms;

class Demo
{

    /**
     * @var string appKey
     */
    private $appKey = '';

    /**
     * @var string appSecret
     */
    private $appSecret = '';

    /**
     * @var string 签名
     */
    private $signName = '';

    /**
     * @var string 发送模版
     */
    private $templateCode = '';

    /**
     * 发送验证码
     * @return bool
     */
    public function sendSms()
    {
        $phone = '';
        $ret = $this->createClient()
            ->setMobile($phone)
            ->setCacheKey()
            ->setTtl()
            ->setInSeconds()
            ->sendSmsCode();
        if ($ret['errcode']) {
            return false;
        }
        return true;
    }

    /**
     * 验证短信验证码
     * @return bool
     */
    public function checkCode()
    {
        $phone = '';
        $code = '';
        $ret = $this->createClient()
            ->setMobile($phone)
            ->setCode($code)
            ->setCacheKey()
            ->setDelNow(true)
            ->checkCode();
        if ($ret['errcode']) {
            return false;
        }
        return true;
    }

    /**
     * @return Sms
     */
    private function createClient()
    {
        return new Sms($this->appKey, $this->appSecret, $this->signName, $this->templateCode);
    }
}