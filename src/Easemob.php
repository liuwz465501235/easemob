<?php

namespace Luwz\Easemob;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Introduction 环信类
 *
 * @author 刘维中
 * @email liu.wz@qq.com
 * @since 1.0
 * @date 2017-12-05
 */
class Easemob
{

    /**
     * Client_Id
     * @var type 
     */
    protected $client_id;

    /**
     * Client_Secret
     * @var type 
     */
    protected $client_secret;

    /**
     * Org_Name
     * @var type 
     */
    protected $org_name;

    /**
     * App_Name
     * @var type 
     */
    protected $app_name;

    /**
     * Url
     * @var type 
     */
    protected $url;

    /**
     * Introduction 构造方法
     */
    public function __construct()
    {
        $config = config('easemob');

        if (!$config)
        {
            throw new InvalidArgumentException('配置参数不存在');
        }

        if (!isset($config['app_name']) || !isset($config['org_name']) || !isset($config['client_id']) || !isset($config['client_secret']))
        {
            throw new InvalidArgumentException('配置参数不正确');
        }

        $this->client_id     = $config['client_id'];
        $this->client_secret = $config['client_secret'];
        $this->org_name      = $config['org_name'];
        $this->app_name      = $config['app_name'];
        if (!empty($this->org_name) && !empty($this->app_name))
        {
            $this->url = 'https://a1.easemob.com/' . $this->org_name . '/' . $this->app_name . '/';
        }
    }

    /**
     * Introduction 获取系统的token值
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param boolean $force 是否强制获取
     */
    public function getToken($force = false)
    {
        $access_token = Cache::get('IM_TOKEN');
        if ($access_token && $force === false)
        {
            return "Authorization:Bearer " . $access_token;
        }

        $options = array(
            "grant_type"    => "client_credentials",
            "client_id"     => $this->client_id,
            "client_secret" => $this->client_secret
        );

        //json_encode()函数，可将PHP数组或对象转成json字符串，使用json_decode()函数，可以将json字符串转换为PHP数组或对象
        $body        = json_encode($options);
        $url         = $this->url . "token";
        $tokenResult = $this->postCurl($url, $body, $header      = array());
        if(isset($tokenResult['error']) && !empty($tokenResult['error']))
        {
            throw new InvalidArgumentException('环信参数错误');
        }
        
        $access_token = $tokenResult['access_token'];
        $expires_in   = $tokenResult['expires_in'];

        //表示该token值为合法的token值
        if (isset($tokenResult['application']) && !empty($tokenResult['application']))
        {
            Cache::put("IM_TOKEN", $access_token, 60);
        }

        return "Authorization:Bearer " . $access_token;
    }

    /**
     * Introduction 授权注册
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username 用户名
     * @param string $password 密码
     */
    public function createUser($username, $password)
    {
        $url     = $this->url . 'users';
        
        $options = array(
            "username" => $username,
            "password" => $password
        );
        $body    = json_encode($options);
        $header  = array($this->getToken());
        $result  = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 批量注册用户
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param array $options 注册选项
     */
    public function createUsers($options)
    {
        $url = $this->url . 'users';

        $body   = json_encode($options);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 重置用户密码
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username 用户名
     * @param string $newpassword 新密码
     */
    public function resetPassword($username, $newpassword)
    {
        $url     = $this->url . 'users/' . $username . '/password';
        
        $options = array(
            "newpassword" => $newpassword
        );
        $body    = json_encode($options);
        $header  = array($this->getToken());
        $result  = $this->postCurl($url, $body, $header, "PUT");
        
        return $result;
    }

    /**
     * Introduction 获取单个用户
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username 用户名
     */
    public function getUser($username)
    {
        $url    = $this->url . 'users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");
        
        return $result;
    }

    /**
     * Introduction 获取批量用户----不分页
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param int $limit 限制个数
     */
    public function getUsers($limit = 0)
    {
        if (!empty($limit))
        {
            $url = $this->url . 'users?limit=' . $limit;
        }
        else
        {
            $url = $this->url . 'users';
        }
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");
        
        return $result;
    }

    /**
     * Introduction 获取批量用户----不分页
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param int $limit 限制个数
     * @param string $cursor 分页标识
     */
    public function getUsersForPage($limit = 0, $cursor = '')
    {
        $url = $this->url . 'users?limit=' . $limit . '&cursor=' . $cursor;

        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");
        
        if (!empty($result["cursor"]))
        {
            $cursor = $result["cursor"];
            $this->writeCursor("userfile.txt", $cursor);
        }
        
        return $result;
    }

    /**
     * Introduction 创建文件夹
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $dir 
     * @param string $mode 
     */
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode))
        {
            return TRUE;
        }
        
        if (!mkdirs(dirname($dir), $mode))
        {
            return FALSE;
        }
        
        return @mkdir($dir, $mode);
    }

    /**
     * Introduction 写入cursor
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filename 
     * @param string $content 
     */
    public function writeCursor($filename, $content)
    {
        //判断文件夹是否存在，不存在的话创建
        if (!file_exists("easemob/txtfile"))
        {
            mkdirs("easemob/txtfile");
        }
        
        $myfile = @fopen("easemob/txtfile/" . $filename, "w+") or die("Unable to open file!");
        @fwrite($myfile, $content);
        fclose($myfile);
    }

    /**
     * Introduction 读取cursor
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filename 
     */
    public function readCursor($filename)
    {
        //判断文件夹是否存在，不存在的话创建
        if (!file_exists("easemob/txtfile"))
        {
            mkdirs("easemob/txtfile");
        }
        
        $file = "easemob/txtfile/" . $filename;
        $fp   = fopen($file, "a+"); //这里这设置成a+
        if ($fp)
        {
            while (!feof($fp))
            {
                //第二个参数为读取的长度
                $data = fread($fp, 1000);
            }
            fclose($fp);
        }
        
        return $data;
    }

    /**
     * Introduction 删除单个用户
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username 
     */
    public function deleteUser($username)
    {
        $url    = $this->url . 'users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 删除批量用户
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param int $limit
     * @Tips  limit:建议在100-500之间，、注：具体删除哪些并没有指定, 可以在返回值中查看。
     */
    public function deleteUsers($limit)
    {
        $url    = $this->url . 'users?limit=' . $limit;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 修改用户昵称
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param string $nickname
     */
    public function editNickname($username, $nickname)
    {
        $url     = $this->url . 'users/' . $username;
        
        $options = array(
            "nickname" => $nickname
        );
        $body    = json_encode($options);
        $header  = array($this->getToken());
        $result  = $this->postCurl($url, $body, $header, 'PUT');
        
        return $result;
    }

    /**
     * Introduction 添加好友
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param string $friend_name
     */
    public function addFriend($username, $friend_name)
    {
        $url    = $this->url . 'users/' . $username . '/contacts/users/' . $friend_name;
        
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header, 'POST');
        
        return $result;
    }

    /**
     * Introduction 删除好友
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param string $friend_name
     */
    public function deleteFriend($username, $friend_name)
    {
        $url    = $this->url . 'users/' . $username . '/contacts/users/' . $friend_name;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 查看好友
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function showFriends($username)
    {
        $url    = $this->url . 'users/' . $username . '/contacts/users';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 查看用户黑名单
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function getBlacklist($username)
    {
        $url    = $this->url . 'users/' . $username . '/blocks/users';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 往黑名单中加人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param array $usernames
     */
    public function addUserForBlacklist($username, $usernames)
    {
        $url    = $this->url . 'users/' . $username . '/blocks/users';
        
        $body   = json_encode($usernames);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header, 'POST');
        
        return $result;
    }

    /**
     * Introduction 从黑名单中减人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param string $blocked_name
     */
    public function deleteUserFromBlacklist($username, $blocked_name)
    {
        $url    = $this->url . 'users/' . $username . '/blocks/users/' . $blocked_name;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 查看用户是否在线
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function isOnline($username)
    {
        $url    = $this->url . 'users/' . $username . '/status';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 查看用户离线消息数
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function getOfflineMessages($username)
    {
        $url    = $this->url . 'users/' . $username . '/offline_msg_count';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 查看某条消息的离线状态
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     * @param string $msg_id
     * @Tips deliverd 表示此用户的该条离线消息已经收到
     */
    public function getOfflineMessageStatus($username, $msg_id)
    {
        $url    = $this->url . 'users/' . $username . '/offline_msg_status/' . $msg_id;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 禁用用户账号
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function deactiveUser($username)
    {
        $url    = $this->url . 'users/' . $username . '/deactivate';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header);
        
        return $result;
    }

    /**
     * Introduction 解禁用户账号
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function activeUser($username)
    {
        $url    = $this->url . 'users/' . $username . '/activate';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header);
        
        return $result;
    }

    /**
     * Introduction 强制用户下线
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function disconnectUser($username)
    {
        $url    = $this->url . 'users/' . $username . '/disconnect';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 上传图片或文件
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filePath
     */
    public function uploadFile($filePath)
    {
        $url          = $this->url . 'chatfiles';
        
        $file         = file_get_contents($filePath);
        $body['file'] = $file;
        $header       = array('enctype:multipart/form-data', $this->getToken(), "restrict-access:true");
        $result       = $this->postCurl($url, $body, $header, 'XXX');
        
        return $result;
    }
    
    /**
     * Introduction 下载文件或图片
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $uuid
     * @param string $shareSecret
     */
    public function downloadFile($uuid, $shareSecret)
    {
        $url      = $this->url . 'chatfiles/' . $uuid;
        
        $header   = array("share-secret:" . $shareSecret, "Accept:application/octet-stream", $this->getToken());
        $result   = $this->postCurl($url, '', $header, 'GET');
        $filename = md5(time() . mt_rand(10, 99)) . ".png"; //新图片名称
        if (!file_exists("easemob/down"))
        {
            //mkdir("../image/down");
            mkdirs("easemob/down/");
        }

        $file = @fopen("easemob/down/" . $filename, "w+"); //打开文件准备写入
        @fwrite($file, $result); //写入
        fclose($file); //关闭
        
        return $filename;
    }

    /**
     * Introduction 下载图片缩略图
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $uuid
     * @param string $shareSecret
     */
    public function downloadThumbnail($uuid, $shareSecret)
    {
        $url      = $this->url . 'chatfiles/' . $uuid;
        
        $header   = array("share-secret:" . $shareSecret, "Accept:application/octet-stream", $this->getToken(), "thumbnail:true");
        $result   = $this->postCurl($url, '', $header, 'GET');
        $filename = md5(time() . mt_rand(10, 99)) . "th.png"; //新图片名称
        if (!file_exists("easemob/down"))
        {
            //mkdir("../image/down");
            mkdirs("easemob/down/");
        }

        $file = @fopen("easemob/down/" . $filename, "w+"); //打开文件准备写入
        @fwrite($file, $result); //写入
        fclose($file); //关闭
        
        return $filename;
    }

    /**
     * Introduction 发送文本消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $content
     * @param array $ext
     */
    public function sendText($from, $target_type, $target, $content, $ext)
    {
        $url                 = $this->url . 'messages';
        
        $body['target_type'] = $target_type;
        $body['target']      = $target;
        $options['type']     = "txt";
        $options['msg']      = $content;
        $body['msg']         = $options;
        $body['from']        = $from;
        $body['ext']         = $ext;
        $b                   = json_encode($body);
        $header              = array($this->getToken());
        $result              = $this->postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 发送透传消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $action
     * @param array $ext
     */
    public function sendCmd($from, $target_type, $target, $action, $ext)
    {
        $url                 = $this->url . 'messages';
        
        $body['target_type'] = $target_type;
        $body['target']      = $target;
        $options['type']     = "cmd";
        $options['action']   = $action;
        $body['msg']         = $options;
        $body['from']        = $from;
        $body['ext']         = $ext;
        $b                   = json_encode($body);
        $header              = array($this->getToken());
        $result              = $this->postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 发图片消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filePath
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $filename
     * @param array $ext
     */
    public function sendImage($filePath, $from, $target_type, $target, $filename, $ext)
    {
        $result              = $this->uploadFile($filePath);
        
        $uri                 = $result['uri'];
        $uuid                = $result['entities'][0]['uuid'];
        $shareSecret         = $result['entities'][0]['share-secret'];
        $url                 = $this->url . 'messages';
        $body['target_type'] = $target_type;
        $body['target']      = $target;
        $options['type']     = "img";
        $options['url']      = $uri . '/' . $uuid;
        $options['filename'] = $filename;
        $options['secret']   = $shareSecret;
        $options['size']     = array(
            "width"  => 480,
            "height" => 720
        );
        $body['msg']         = $options;
        $body['from']        = $from;
        $body['ext']         = $ext;
        $b                   = json_encode($body);
        $header              = array($this->getToken());
        $result              = $this->postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 发语音消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filePath
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $filename
     * @param int $length
     * @param array $ext
     */
    public function sendAudio($filePath, $from, $target_type, $target, $filename, $length, $ext)
    {
        $result              = $this->uploadFile($filePath);
        
        $uri                 = $result['uri'];
        $uuid                = $result['entities'][0]['uuid'];
        $shareSecret         = $result['entities'][0]['share-secret'];
        $url                 = $this->url . 'messages';
        
        $body['target_type'] = $target_type;
        $body['target']      = $target;
        $options['type']     = "audio";
        $options['url']      = $uri . '/' . $uuid;
        $options['filename'] = $filename;
        $options['length']   = $length;
        $options['secret']   = $shareSecret;
        
        $body['msg']         = $options;
        $body['from']        = $from;
        $body['ext']         = $ext;
        $b                   = json_encode($body);
        
        $header              = array($this->getToken());
        $result              = $this->postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 发视频消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filePath
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $filename
     * @param int $length
     * @param string $thumb
     * @param string $thumb_secret
     * @param array $ext
     */
    public function sendVedio($filePath, $from, $target_type, $target, $filename, $length, $thumb, $thumb_secret, $ext)
    {
        $result                  = $this->uploadFile($filePath);
        
        $uri                     = $result['uri'];
        $uuid                    = $result['entities'][0]['uuid'];
        $shareSecret             = $result['entities'][0]['share-secret'];
        $url                     = $this->url . 'messages';
        
        $body['target_type']     = $target_type;
        $body['target']          = $target;
        
        $options['type']         = "video";
        $options['url']          = $uri . '/' . $uuid;
        $options['filename']     = $filename;
        $options['thumb']        = $thumb;
        $options['length']       = $length;
        $options['secret']       = $shareSecret;
        $options['thumb_secret'] = $thumb_secret;
        
        $body['msg']             = $options;
        $body['from']            = $from;
        $body['ext']             = $ext;
        $b                       = json_encode($body);
        
        $header                  = array($this->getToken());
        $result                  = $this->postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 发文件消息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $filePath
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $filename
     * @param int $length
     * @param array $ext
     */
    public function sendFile($filePath, $from, $target_type, $target, $filename, $length, $ext)
    {
        $result              = $this->uploadFile($filePath);
        $uri                 = $result['uri'];
        $uuid                = $result['entities'][0]['uuid'];
        $shareSecret         = $result['entities'][0]['share-secret'];
        $url                 = $GLOBALS['base_url'] . 'messages';
        
        $body['target_type'] = $target_type;
        $body['target']      = $target;
        
        $options['type']     = "file";
        $options['url']      = $uri . '/' . $uuid;
        $options['filename'] = $filename;
        $options['length']   = $length;
        $options['secret']   = $shareSecret;
        
        $body['msg']         = $options;
        $body['from']        = $from;
        $body['ext']         = $ext;
        $b                   = json_encode($body);
        
        $header              = array(getToken());
        $result              = postCurl($url, $b, $header);
        
        return $result;
    }

    /**
     * Introduction 获取app中的所有群组----不分页
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param int $limit
     */
    public function getGroups($limit = 0)
    {
        if (!empty($limit))
        {
            $url = $this->url . 'chatgroups?limit=' . $limit;
        }
        else
        {
            $url = $this->url . 'chatgroups';
        }

        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");
        
        return $result;
    }
    
    /**
     * Introduction 获取app中的所有群组---分页
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param int $limit
     * @param string $cursor
     */
    public function getGroupsForPage($limit = 0, $cursor = '')
    {
        $url    = $this->url . 'chatgroups?limit=' . $limit . '&cursor=' . $cursor;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");

        if (!empty($result["cursor"]))
        {
            $cursor = $result["cursor"];
            $this->writeCursor("groupfile.txt", $cursor);
        }
        
        return $result;
    }

    /**
     * Introduction 获取一个或多个群组的详情
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_ids
     */
    public function getGroupDetail($group_ids)
    {
        $g_ids  = implode(',', $group_ids);
        
        $url    = $this->url . 'chatgroups/' . $g_ids;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 创建一个群组
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param array $options
     * @Tips 
     * {
     *      "groupname":"testrestgrp12", //群组名称，此属性为必须的
     *      "desc":"server create group", //群组描述，此属性为必须的
     *      "public":true, //是否是公开群，此属性为必须的
     *      "maxusers":300, //群组成员最大数（包括群主），值为数值类型，默认值200，最大值2000，此属性为可选的
     *      "approval":true, //加入公开群是否需要批准，默认值是false（加入公开群不需要群主批准），此属性为必选的，私有群必须为true
     *      "owner":"jma1", //群组的管理员，此属性为必须的
     *      "members":["jma2","jma3"] //群组成员，此属性为可选的，但是如果加了此项，数组元素至少一个（注：群主jma1不需要写入到members里面）
     * }
     */
    public function createGroup($options)
    {
        $url    = $this->url . 'chatgroups';
        $header = array($this->getToken());
        $body   = json_encode($options);
        $result = $this->postCurl($url, $body, $header);
        return $result;
    }

    /**
     * Introduction 修改群组信息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $options
     */
    public function modifyGroupInfo($group_id, $options)
    {
        $url    = $this->url . 'chatgroups/' . $group_id;
        
        $body   = json_encode($options);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header, 'PUT');
        
        return $result;
    }
    
    /**
     * Introduction 删除群组
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     */
    public function deleteGroup($group_id)
    {
        $url    = $this->url . 'chatgroups/' . $group_id;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 获取群组中的成员
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     */
    public function getGroupUsers($group_id)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/users';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 群组单个加人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param string $username
     */
    public function addGroupMember($group_id, $username)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/users/' . $username;
        
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header);
        
        return $result;
    }

    /**
     * Introduction 群组批量加人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $usernames
     */
    public function addGroupMembers($group_id, $usernames)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/users';
        
        $body   = json_encode($usernames);
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 群组单个减人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param string $username
     */
    public function deleteGroupMember($group_id, $username)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 群组批量减人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $usernames
     */
    public function deleteGroupMembers($group_id, $usernames)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/users/' . $usernames;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 获取一个用户参与的所有群组
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function getGroupsForUser($username)
    {
        $url    = $this->url . 'users/' . $username . '/joined_chatgroups';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 群组转让
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $options
     */
    public function changeGroupOwner($group_id, $options)
    {
        $url    = $this->url . 'chatgroups/' . $group_id;
        
        $body   = json_encode($options);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header, 'PUT');
        
        return $result;
    }
    
    /**
     * Introduction 查询一个群组黑名单用户名列表
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     */
    public function getGroupBlackList($group_id)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/blocks/users';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 群组黑名单单个加人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param string $username
     */
    public function addGroupBlackMember($group_id, $username)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/blocks/users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header);
        
        return $result;
    }

    /**
     * Introduction 群组黑名单批量加人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $usernames
     */
    public function addGroupBlackMembers($group_id, $usernames)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/blocks/users';
        
        $body   = json_encode($usernames);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 群组黑名单单个减人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param string $username
     */
    public function deleteGroupBlackMember($group_id, $username)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/blocks/users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 群组黑名单批量减人
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $group_id
     * @param array $usernames
     */
    public function deleteGroupBlackMembers($group_id, $usernames)
    {
        $url    = $this->url . 'chatgroups/' . $group_id . '/blocks/users';
        $body   = json_encode($usernames);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header, 'DELETE');
        return $result;
    }

    /**
     * Introduction 创建聊天室
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param array $options
     */
    public function createChatRoom($options)
    {
        $url    = $this->url . 'chatrooms';
        
        $header = array($this->getToken());
        $body   = json_encode($options);
        $result = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 修改聊天室信息
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     * @param array $options
     */
    public function modifyChatRoom($chatroom_id, $options)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id;
        
        $body   = json_encode($options);
        $result = $this->postCurl($url, $body, $header, 'PUT');
        
        return $result;
    }

    /**
     * Introduction 删除聊天室
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     */
    public function deleteChatRoom($chatroom_id)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 获取app中所有的聊天室
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     */
    public function getChatRooms()
    {
        $url    = $this->url . 'chatrooms';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, "GET");
        
        return $result;
    }

    /**
     * Introduction 获取一个聊天室的详情
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     */
    public function getChatRoomDetail($chatroom_id)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 获取一个用户加入的所有聊天室
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $username
     */
    public function getChatRoomJoined($username)
    {
        $url    = $this->url . 'users/' . $username . '/joined_chatrooms';
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'GET');
        
        return $result;
    }

    /**
     * Introduction 聊天室单个成员添加
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     * @param string $username
     */
    public function addChatRoomMember($chatroom_id, $username)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id . '/users/' . $username;
        
        $header = array($this->getToken(), 'Content-Type:application/json');
        $result = $this->postCurl($url, '', $header);
        
        return $result;
    }

    /**
     * Introduction 聊天室批量成员添加
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     * @param array $usernames
     */
    public function addChatRoomMembers($chatroom_id, $usernames)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id . '/users';
        
        $body   = json_encode($usernames);
        $header = array($this->getToken());
        $result = $this->postCurl($url, $body, $header);
        
        return $result;
    }

    /**
     * Introduction 聊天室单个成员删除
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     * @param string $username
     */
    public function deleteChatRoomMember($chatroom_id, $username)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id . '/users/' . $username;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction 聊天室批量成员删除
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $chatroom_id
     * @param array $usernames
     */
    public function deleteChatRoomMembers($chatroom_id, $usernames)
    {
        $url    = $this->url . 'chatrooms/' . $chatroom_id . '/users/' . $usernames;
        
        $header = array($this->getToken());
        $result = $this->postCurl($url, '', $header, 'DELETE');
        
        return $result;
    }

    /**
     * Introduction Http请求
     *
     * @author 刘维中
     * @email liu.wz@qq.com
     * @since 1.0
     * @date 2017-12-05
     * @param string $url
     * @param string $body
     * @param array $header
     * @param string $type
     */
    public function postCurl($url, $body, $header, $type = "POST")
    {
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url); //设置url
        //1)设置请求头
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //2)设备请求体
        if (count($body) > 0)
        {
            //$b=json_encode($body,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body); //全部数据使用HTTP协议中的"POST"操作来发送。
        }
        //设置请求头
        if (count($header) > 0)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算
        //3)设置提交方式
        switch ($type)
        {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请									                     求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器
        //5)
        //3.抓取URL并把它传递给浏览器
        $res = curl_exec($ch);

        $result = json_decode($res, true);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);
        if (empty($result))
        {
            return $res;
        }
        else
        {
            return $result;
        }
    }
}
