<?php

/**
 * 使用 cURL 实现 HTTP GET 请求
 *
 * @param       string $url , 请求地址
 * @param       string $host , 服务器 host 名, 默认为空(当一台机器有多个虚拟主机时需要指定 host)
 * @param       int $timeout , 连接超时时间, 默认为2
 *
 * @return      bool      $data, 为返回数据, 失败返回 false
 */
function cURLHTTPGet($url, $timeout = 2, $host = '', $failOnError = true)
{

    $header = array('Content-transfer-encoding: text');

    if (!empty($host)) {
        $header[] = 'Host: ' . $host;
    }

    $curl_handle = curl_init();

    // 连接超时
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
    // 执行超时
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 3);
    // HTTP返回错误时, 函数直接返回错误
    curl_setopt($curl_handle, CURLOPT_FAILONERROR, $failOnError);
    // 允许重定向
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    // 允许重定向的最大次数
    curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 2);
    // ssl验证host
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, FALSE);
    // 返回为字符串
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    // 设置HTTP头
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
    // 指定请求地址
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    // 执行请求
    $response = curl_exec($curl_handle);
    if ($response === false) {
        $errCode = 10615;
        $errMsg = 'cURL errno: ' . curl_errno($curl_handle) . '; error: ' . curl_error($curl_handle);
        // 关闭连接
        curl_close($curl_handle);

        return false;
    }

    // 关闭连接
    curl_close($curl_handle);

    return $response;
}

/**
 * 使用 cURL 实现 HTTP POST 请求
 *
 * @param       string $url , 请求地址
 * @param       string $post_data , 请求的post数据，一般为经过urlencode 和用&处理后的字符串
 * @param       string $host , 服务器 host 名, 默认为空(当一台机器有多个虚拟主机时需要指定 host)
 * @param       int $timeout , 连接超时时间, 默认为2
 *
 * @return      bool      $data, 为返回数据, 失败返回 false
 */
function cURLHTTPPost($url, $post_data, $timeout = 2, $host = '', $header_append = array(), $failOnError = true)
{
    $data_len = strlen($post_data);
    $header = array('Content-transfer-encoding: text', 'Content-Length: ' . $data_len);

    if (!empty($header_append)) {
        foreach ($header_append as $v) {
            $header[] = $v;
        }
    }

    if (!empty($host)) {
        $header[] = 'Host: ' . $host;
    }

    $curl_handle = curl_init();

    // 连接超时
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
    // 执行超时
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 3);
    // HTTP返回错误时, 函数直接返回错误
    curl_setopt($curl_handle, CURLOPT_FAILONERROR, $failOnError);
    // 允许重定向
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    // 允许重定向的最大次数
    curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 2);
    // ssl验证host
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, FALSE);
    // 返回为字符串
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    // 设置HTTP头
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
    // 指定请求地址
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    //设置为post方式
    curl_setopt($curl_handle, CURLOPT_POST, TRUE);
    //post 参数
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_data);
    // 执行请求
    $response = curl_exec($curl_handle);
    if ($response === false) {
        $errCode = 10616;
        $errMsg = 'cURL errno: ' . curl_errno($curl_handle) . '; error: ' . curl_error($curl_handle);
        // 关闭连接
        curl_close($curl_handle);

        return false;
    }

    // 关闭连接
    curl_close($curl_handle);

    return $response;
}


function getid()
{
    $url = 'https://haoche.atzuche.com/activity/list?openid=ohryYtwcni4p1mpuw-F-fKAVAH0I';
    $host = 'haoche.atzuche.com';
    $res = json_decode(cURLHTTPGet($url, 2, $host, true), true);
    foreach ($res['datas'] as $key => $value) {
        if ($value['cityCode'] == 310100) {  //换成你所在城市的id
            return $value['current']['id'];
            break;
        }
    }
}

function grab()
{
    $id = getid();
    $url = 'https://haoche.atzuche.com/activity/join';
    $post_data = 'openid=oEc9puHjemTRUsY0tqXk72LVG-Uw&activityId='.$id.'&channel=106';
    $host = 'haoche.atzuche.com';
    $res = json_decode(cURLHTTPPost($url, $post_data, $timeout = 2, $host, $header_append = array(), true), true);
    return $res;
}

//执行500次，5秒钟，间隔0.01s，5个线程，脚本设置12:59:58开始
for ($i = 0; $i < 501; $i++) {
    $res = grab();
    $result = 'Test ';
    list($usec, $sec) = explode(" ", microtime());
    $msec=round($usec*1000);
    $time = $sec.$msec;
    error_log(json_encode($result . $i . ' times ' . $time . ' result ' . $res['resCode']) . "\n", 3, "/tmp/my-errors.log");
    if ($res['resCode'] == "111116") {  //结束代码
        error_log(json_encode($result . $i . ' times ' . $time . ' result ' . $res['resCode']) . "\n", 3, "/tmp/my-errors.log");
        break;
    }
    usleep(10000);

}




