<?php
/**
 * @file MyErrorHandler.php
 * @author lurenzhong@baidu.com
 * @date 16/1/10
 * @brief 利用set_error_handler($error_handler[,$error_types = E_ALL | E_STRICT ])来自定义错误处理器(接管错误处理)
 * @version
 */

class MyErrorHandler
{

    public $message = '';
    public $filename = '';
    public $line = 0;
    public $vars = array();

    //protected $_noticeLog = '/var/log/notice/notice.log';//保存notice错误日志
    protected $_noticeLog = '/Users/baidu/notice.log';

    /**
     * @param string $message 传入的错误信息
     * @param string $filename 发生错误的位置
     * @param int $line 发生错误的行号
     * @param array $vars 额外信息
     */
    public function __construct($message,$filename,$line,$vars){
        $this->message = $message;
        $this->filename = $filename;
        $this->line = $line;
        $this->vars = $vars;
    }


    /**
     * 传入自定义错误处理器的方法（核心）
     * @param int $errno
     * @param string $errmsg
     * @param string $filename
     * @param int $line
     * @param array $vars
     */
    public static function deal($errno,$errmsg,$filename,$line,$vars){
        $self = new self($errmsg,$filename,$line,$vars);
        switch($errno){
            //set_error_handler()无法接管E_ERROR错误
            case E_USER_ERROR:
                return $self->dealError();
                break;

            case E_USER_WARNING:
            case E_WARNING:
                return $self->dealWarning();
                break;

            case E_USER_NOTICE:
            case E_NOTICE:
                return $self->dealNotice();
                break;

            default:
                return false;
        }
    }

    /**
     * 处理fatal error错误的方法
     */
    public function dealError(){
        //开启内存缓冲
        ob_start();
        debug_print_backtrace();
        $backtrace = ob_get_flush();

        $errorMsg = <<<EOF
    出现了致命错误，如下：
    产生错误的文件：{$this->filename}
    产生错误的信息：{$this->message}
    产生错误的行号：{$this->line}
    追踪信息：{$backtrace}
EOF;

        return error_log($errorMsg,1,'505051975@qq.com'); //发送错误邮件给管理员
    }


    /**
     * 处理warning警告级别的错误
     * @return bool
     */
    public function dealWarning(){
        $errorMsg = <<<EOF
    出现了警告错误，如下：
    产生警告的文件：{$this->filename}
    产生警告的信息：{$this->message}
    产生警告的行号：{$this->line}
EOF;
        return error_log($errorMsg,1,'505051975@qq.com'); //发送错误邮件给管理员
    }


    /**
     * 处理notice通知级别的错误
     */
    public function dealNotice(){
        $datetime = date("Y-m-d H:i:s",time());
        $errorMsg = <<<EOF
    出现了通知错误，如下：
    产生通知的文件：{$this->filename}
    产生通知的信息：{$this->message}
    产生通知的行号：{$this->line}
    产生通知的时间：{$datetime}
EOF;
        return error_log($errorMsg,3,$this->_noticeLog); //将notice错误记录到文件中
    }

}

/*
该自定义错误处理器使用方法：
require_once 'MyErrorHandler.php';

set_error_handler(array('MyErrorHandler','deal'));//设置自定义错误处理器

trigger_error('我是手动抛出的致命错误',E_USER_ERROR); //测试error错误
settype($var,'king'); //测试warning错误
echo $name; //测试notice错误
*/

