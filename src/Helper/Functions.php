<?php
/**
 * @description 公共函数库
 * @Project composer
 * @Filename Functions.php
 * @Author zhang.hongjian <mr_zhanghj@sina.com>
 * @Date 2023/1/14
 * @Time 14:41
 */

if(!function_exists('randSmsCode')) {
	/**
	 * 生成随机验证码
	 * @param $length
	 * @return string
	 */
	function randSmsCode($length = 6)
	{
		$chars = '0123456789';
		$result = '';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$result .= $chars[rand(0, $max)];
		}
		return $result;
	}
}

if(!function_exists('resultError')) {
	/**
	 * 失败返回数据
	 * @param $errmsg
	 * @param $errcode
	 * @return array
	 */
	function resultError($errmsg = 'error', $errcode = 500)
	{
		return ['errcode' => $errcode, 'errmsg' => $errmsg];
	}
}

if(!function_exists('resultSuccess')) {
	/**
	 * 成功返回数据
	 * @param $data
	 * @param $errmsg
	 * @param $errcode
	 * @return array
	 */
	function resultSuccess($data = [], $errmsg = 'success', $errcode = 0)
	{
		return ['errcode' => $errcode, 'errmsg' => $errmsg, 'data' => $data];
	}
}