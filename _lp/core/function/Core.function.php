<?php

$_GET = transcribe( $_GET ); 
$_POST = transcribe( $_POST ); 
$_REQUEST = transcribe( $_REQUEST );
$_COOKIE = transcribe( $_COOKIE );

/**
 * 出于安全考虑，对字符进行检查和转换
 *
 * 包括过滤Xss恶意脚本代码
 * 
 * @param  array  $aList  需检查的数据
 * @param  boolean $aIsTopLevel 是否为严格模式，只有在magic_quotes_gpc开启后生效
 * @return array  返回被转换过的数据
 */
function transcribe($aList, $aIsTopLevel = true) 
{
   if( empty($aList) && !is_array($aList) )
   {
      return false;
   }
   $gpcList = array();
   $isMagic = get_magic_quotes_gpc();
  
   foreach ($aList as $key => $value) 
   {
       if (is_array($value)) 
       {
            $decodedKey = ($isMagic && !$aIsTopLevel) ? stripslashes($key) : $key;
            $decodedKey = RemoveXSS($decodedKey);
            $decodedValue = transcribe($value, false);
            $decodedValue = RemoveXSS($decodedValue);
       } else {
            $decodedKey = stripslashes($key);
            $decodedKey = RemoveXSS($decodedKey);
            $decodedValue = ($isMagic) ? stripslashes($value) : $value;
            $decodedValue = RemoveXSS($decodedValue);
       }
       $gpcList[$decodedKey] = $decodedValue;
   }
   return $gpcList;
}


/**
 * 过滤XSS（跨站脚本攻击）的函数
 *
 * @param $val 字符串参数，可能包含恶意的脚本代码
 * @return  处理后的字符串
 *
 */
function RemoveXSS($val) 
{  
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
   // this prevents some character re-spacing such as <java\0script>  
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  
      
   // straight replacements, the user should never need these since they're normal characters  
   // this prevents like <IMG SRC=@avascript:alert('XSS')>  
   $search = 'abcdefghijklmnopqrstuvwxyz'; 
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
   $search .= '1234567890!@#$%^&*()'; 
   $search .= '~`";:?+/={}[]-_|\'\\'; 
   for ($i = 0; $i < strlen($search); $i++) 
   { 
      // ;? matches the ;, which is optional 
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
     
      // @ @ search for the hex values 
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
      // @ @ 0{0,7} matches '0' zero to seven times  
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
   } 
     
   // now the only remaining whitespace attacks are \t, \n, and \r 
   //$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
   $ra1 = Array('javascript', 'vbscript', 'expression', 'script', 'embed', 'object', 'iframe'); 
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
   $ra = array_merge($ra1, $ra2); 
     
   $found = true; // keep replacing as long as the previous round replaced something 
   while ($found == true) 
   { 
      $val_before = $val; 
      for ($i = 0; $i < sizeof($ra); $i++) 
      { 
         $pattern = '/'; 
         for ($j = 0; $j < strlen($ra[$i]); $j++) 
         { 
            if ($j > 0) 
            { 
               $pattern .= '(';  
               $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
               $pattern .= '|';  
               $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
               $pattern .= ')*'; 
            } 
            $pattern .= $ra[$i][$j]; 
         } 
         $pattern .= '/i';  
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag  
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
         if ($val_before == $val) 
         {  
            // no replacements were made, so exit the loop  
            $found = false;  
         }  
      }  
   }  
   return trim($val);  
}   


/**
 * 读取参数，默认读取GET参数
 * 
 * @param  string $str    参数key
 * @param  string $method 数据来源类型，有 get, post, req
 * @return string 参数key对应的值
 */
function v( $str, $method='get')
{
    if( 'get' === strtolower($method) )
    {
        return isset( $_GET[$str] ) ? $_GET[$str] : '';
    }

    if( 'post' === strtolower($method) )
    {
        return isset( $_POST[$str] ) ? $_POST[$str] : '';
    }

    if( 'req' === strtolower($method) )
    {
        return isset( $_REQUEST[$str] ) ? $_REQUEST[$str] : '';
    }
}

/**
 * 去除HTML、XML 以及 PHP 的标签。
 * @param  string $str 目标字符串
 * @return string 去除标签后的字符串
 */
function z( $str )
{
	return strip_tags( $str );
}

/**
 * 渲染前端页面
 * @param  mix $data  页面需要的数据
 * @param  string $layout 页面布局类型
 * @param  string $sharp  页面文件前缀， xxx.tpl.html
 * @return 
 */
function render( $data = NULL , $layout = NULL , $sharp = 'default' )
{
	if( $layout == null )
	{
		if( is_ajax_request() )
		{
			$layout = 'ajax';
		}
		elseif( is_mobile_request() )
		{
			$layout = 'mobile';
		}
		else
		{
			$layout = 'web';
		}
	}
	
	$GLOBALS['layout'] = $layout;
	$GLOBALS['sharp'] = $sharp;
	
	$layout_file = AROOT . 'view/layout/' . $layout . '/' . $sharp . '.tpl.html';
	if( file_exists( $layout_file ) )
	{
		@extract( $data );
		require( $layout_file );
	}
	else
	{
		$layout_file = CROOT . 'view/layout/' . $layout . '/' . $sharp .  '.tpl.html';
		if( file_exists( $layout_file ) )
		{
			@extract( $data );
			require( $layout_file );
		}	
	}
}

/**
 * 跳转url
 * @param  string $url 跳转地址
 * @return 
 */
function forward( $url )
{
    if(!strstr($url, 'http://')){
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    }
	header( "Location: " . $url );
}

/**
 * 获取ip地址
 * 
 */
function getip() {
	if (isset($_SERVER)) {
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$realip = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$realip = $_SERVER["REMOTE_ADDR"];
		}

		if($realip == '127.0.0.1' && isset($_SERVER["HTTP_X_REAL_IP"])) {
			$realip = $_SERVER["HTTP_X_REAL_IP"];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$realip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$realip = getenv('HTTP_CLIENT_IP');
		} else {
			$realip = getenv('REMOTE_ADDR');
		}

		if($realip == '127.0.0.1' && getenv('HTTP_X_REAL_IP')) {
			$realip = getenv('HTTP_X_REAL_IP');
		}
	}

	return $realip;
}

/**
 * 输出json格式数据
 * @param  integer $res    状态码标识
 * @param  string  $msg    提示信息
 * @param  array   $data   返回数据
 * @param  boolean $isExit 是否退出
 * @return 
 */
function jsonEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE) 
{ 
    echo json_encode(
            array(
                'res' => $res,
                'msg' => $msg,
                'data' => $data
            ), JSON_UNESCAPED_UNICODE
        );
    $isExit && exit;
}

/**
 * 输出jsonp格式数据
 * @param  integer $res    状态码标识
 * @param  string  $msg    提示信息
 * @param  array   $data   返回数据
 * @param  boolean $isExit 是否退出
 * @return 
 */
function jsonpEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE) { 
    $info = array( 'res' => $res, 'msg' => $msg, 'data' => $data);
    $callback = v('callback') !='' ? v('callback') : "callback";
    if (!preg_match("/^[0-9a-zA-Z_]+$/", $callback)) {
        die('参数错误！');
    }
    echo "$callback(".json_encode($info, JSON_UNESCAPED_UNICODE).");";
    $isExit && exit;
}


/**
 * 判断字符串是否为json格式
 */
function isJson($str)
{
    if( is_array($str) )
    {
        return false;
    }
    $data = json_decode($str, true);
    return !empty($data) && is_array($data) ? true : false;
}

/**
 * 判断字符串是否为序列化格式
 *
 */
function isSerialized($str) 
{
    //如果传递的字符串不可解序列化，则返回 FALSE，并产生一个 E_NOTICE。 
    return ($str == serialize(false) || @unserialize($str) !== false);
}

/**
 * curl 
 * 
 */
function curl( $url , $data=array() , $method='get', $cookie = NUll)
{
    $ch = curl_init();
    if( 'post' == strtolower($method) )
    {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    }else{
        if($data)
        {
            foreach($data as $key=>$val)
            {
                $parame[] = $key.'='.$val;
            }
            $parame_str = implode('&', $parame);
            $url = $url . '?' . $parame_str;
        }
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if($cookie)
    {
        curl_setopt($ch, CURLOPT_COOKIE , $cookie);
    }
   
    $response = curl_exec($ch);
    
    return $response;
}

/**
 * 设置cookie
 *
 */
function set_cookie($key, $value, $expire='', $path='', $domain='')
{

    //默认24小时内有效
    $expire = empty($expire) ?  time() + 24 * 3600 : $expire;
    $path = empty($path) ? '/' : $path;
    $domain = empty($domain) ? 'xunlei.com' : $domain ;

    setcookie($key, $value, $expire, $path, $domain);
}

/**
 * 获取cookie
 */
function get_cookie($name)
{
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false; 
}

/**
 * 删除cookie
 *
 */
function delete_cookie($key, $domain='')
{
    $domain = empty($domain) ? 'xunlei.com' : $domain ;
    set_cookie($key, '', time()-3600, '/', $domain);
    unset($_COOKIE[$key]);
}

/**
 * 对url参数进行签名，游戏接口常用
 *
 */
function s_sign_new($data , $key, $pass=array('sign'))
{
    foreach($pass as $item)
    {
        if (isset($data[$item])) unset($data[$item]);
    }
    ksort($data);
    $query = http_build_query($data);
    return md5($query . $key);
}

/**
 * 是否为有效邮箱，最大限度检查邮箱的有效性
 * @param  string  $email  email address
 * @return boolean
 */
function isEmail( $email )
{
  return validEmail( $email );
}

/**
 * Validate an email address.
 * Provide email address (raw input)
 * Returns true if the email address has the email address format and the domain exists.
 *
 */
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ( !checkdnsrr($domain) )
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

/**
* 创建唯一的guid
* @param  int $len 长度
*/
function guid($len)
{
    $len = $len ? $len : 32;
    $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678'; // 默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1 
    $maxPos = strlen($chars); 
    $pwd = ''; 
    for( $i = 0; $i < $len; $i++)
    {
        $index = floor( rand(0,9)/10 * $maxPos  );
        $pwd .= substr( $chars, $index ,1 ); 
    }  
    return $pwd; 
}

/**
 * 删掉数组里的某个值
 * 
 */
function arrayRemove( $value , $array )
{
    return array_diff($array, array($value));
}

/**
 * 对日期格式化，返回星期几
 *
 */
function formatWeek($date)
{
    $week = array('日','一','二','三','四','五','六');
    return  '周'.$week[date('w', strtotime($date))] ;
}

/*
 * 对二维数组，根据指定的键值进行排序，默认为升序
 *
 * array_sort($t_data,'vv','desc');
 */
function arraySort($arr,$keys,$type='asc')
{
    $keysvalue = $new_array = array();
    if(!empty($arr) && is_array($arr)){
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc' || empty($type)){
            asort($keysvalue);
        }else if($type == 'desc'){
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
    }
    return $new_array;
}


/**
 * 获取UTF-8编码下的字符串长度，UTF-8编码下，中文、中文标点符号长度算为1，英文、英文标点符号、数字长度算为0.5
 * @param $source_str 原始字符处
 * @return string 返回字符串长度
 */
function getStrLen($source_str)
{
  if(!$source_str){
    return 0;
  }
  $i=0;
  $n=0;
  $str_length = strlen($source_str);//字符串的字节数
  while ($i<$str_length)
  {
    $temp_str=substr($source_str,$i,1);
    $ascnum=Ord($temp_str); //得到字符串中第$i位字符的ascii码
    if ($ascnum>=224)       //如果ASCII位高与224，
    {
      $i=$i+3;            //实际Byte计为3
      $n++;               //字串长度计1
    }
    elseif ($ascnum>=192)   //如果ASCII位高与192，
    {
      $i=$i+2;            //实际Byte计为2
      $n++;               //字串长度计1
    }
    elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
    {
      $i=$i+1;            //实际的Byte数仍计1个
      $n += 0.5;          //经测试，大写字母串MMDDCCFFGGTTLL跟小写字母串mmddccffggttll的长度一样长
    }
    else                    //其他情况下，包括小写字母和半角标点符号，
    {
      $i=$i+1;            //实际的Byte数计1个
      $n=$n+0.5;          //小写字母和半角标点等与半个高位字符宽...
    }
  }
  return $n;
}

/**
 * 截取UTF-8编码下的指定长度的字符串(一个中文长度为1，英文字母长度为0.5)
 * $sourcestr 原始字符处
 * $cutlength 截取字符串的长度
 * @return string 返回截取后的字符串
 */
function cutString($sourcestr, $cutlength)
{
  $returnstr='';
  $i=0;
  $n=0;
  $str_length = strlen($sourcestr);    //字符串的字节数
  while ($i < $str_length)
  {
    $temp_str=substr($sourcestr,$i,1);
    $ascnum=Ord($temp_str);         //得到字符串中第$i位字符的ascii码
    if ($ascnum>=224)               //如果ASCII位高与224，
    {
      $n++;                       //字串长度计1
      if($n > $cutlength){
        break;
      } else {
        $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
        $i=$i+3;                //实际Byte计为3
      }
        
    }
    elseif ($ascnum>=192)           //如果ASCII位高与192，
    {
      $n++;                       //字串长度计1
      if($n > $cutlength){
        break;
      } else {
        $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
        $i=$i+2;                //实际Byte计为2
      }
    }
    elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
    {
      $n+=0.5;                    //大写字母字串长度计0.5
      if($n > $cutlength){
        break;
      } else {
        $returnstr=$returnstr.substr($sourcestr,$i,1);
        $i=$i+1;                //实际的Byte数仍计1个
      }
    }
    else                            //其他情况下，包括小写字母和半角标点符号，
    {
      $n+=0.5;                    //小写字母和半角标点等与半个高位字符宽...长度计0.5
      if($n > $cutlength){
        break;
      } else {
        $returnstr=$returnstr.substr($sourcestr,$i,1);
        $i=$i+1;                //实际的Byte数仍计1个
      }
    }
  }
  return $returnstr;
}

/**
 * 常用发送socket请求
 * @param $ip 连接IP
 * @param $port 连接端口
 * @param $cmd 请求命令
 * @param $timeout 连接超时[最大超时时长5s，最大重试次数3次]
 */
function sendSock($ip, $port, $cmd, $timeout = 1) 
{
    if ($timeout > 5) 
    {
        $timeout = 5;
    }
    $retry = 0;
    while($retry++ < 3)
    {
        $sock = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($sock) break;
        usleep(100);//每次重试等待0.1秒
    }
    if (!$sock)
    {
        xlog('!!! sendSock function return false,  request : '.$ip . ', cmd :'.$cmd. ', errstr:' . $errstr , 'api');
        return FALSE;
    }
    fputs($sock, $cmd);
    
    $body = '';
    while (!feof($sock)) 
    {
        $body .= fread($sock, 1024);
    }
    fclose($sock);
    return $body;
}

/**
 * 检查图片验证码输入是否正确
 * @param  string $verify 用户输入的验证码
 * @return bool 
 */
function checkVerify($verify)
{
    $yzmurl = 'GET /verify/'.$verify.'/'.trim($_COOKIE['VERIFY_KEY']).'?t=MEA HTTP/1.1';        
    $vstatus = sendSock('verify2.xunlei.com',80,$yzmurl);
    $vstatus = intval($vstatus);

    if ($vstatus ==200 && $verify && $_COOKIE['VERIFY_KEY'])
    {
        return true;
    }
    return false;
}

/**
 *  把字符串的大致一半长度的中间部分替换成星号 *
 *  
 */
function starReplace($str)
{
    $len = mb_strlen($str, 'utf8');
    $halfLen = intval($len / 2);
    $quaterLen = intval($len / 4);
    return mb_substr($str, 0, $quaterLen, 'utf8') . str_repeat('*', $halfLen) . mb_substr($str, $quaterLen + $halfLen, $len, 'utf8');
}
