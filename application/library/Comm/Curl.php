<?php
/**
 * 557 发起Curl请求.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2018/11/15 12:39 PM
 */

class Comm_Curl
{

    /**
     * 向服务器发起CURL请求
     * @param $url
     * @param string $method
     * @param null $postFields
     * @param null $header
     * @return mixed
     * @throws Exception
     */
    public static function curl($url, $method = 'GET', $postFields = null, $header = null)
    {
        $systemConfig = Comm_Config::getConf('config.'.DEVELOPMENT.'.system');
        $url = $systemConfig['api'].$url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

//        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        }

        switch ($method) {
            case 'POST' :
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty ($postFields)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, self::getCurlFields($postFields));
                }
                break;
            case 'PUT' :
                curl_setopt($ch, CURLOPT_PUT, true);
                if (!empty ($postFields)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, self::getCurlFields($postFields));
                }
                break;

            case 'PATCH' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                if (!empty ($postFields)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, self::getCurlFields($postFields));
                }
                break;

            case 'DELETE' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                if (!empty ($postFields)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, self::getCurlFields($postFields));
                }
                break;

            default :
                if (!empty ($postFields) && is_array($postFields))
                    $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($postFields);
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty ($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception (curl_error($ch), 0);
        }
        curl_close($ch);

        return $response;
    }


    /**
     * 对参数进行处理和加工
     * @param $postFields
     * @return bool|mixed|string
     */
    public static function getCurlFields($postFields)
    {
        if (is_array($postFields) || is_object($postFields)) {
            if (is_object($postFields))
                $postFields = Comm_Tools::object2array($postFields);
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1)) { // 判断是不是文件上传
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                } else { // 文件上传用multipart/form-data，否则用www-form-urlencoded
                    $postMultipart = true;
                }
            }
            unset ($k, $v);
            if (!$postMultipart) {
                return substr($postBodyString, 0, -1);
            }
        }
        return $postFields;

    }


}