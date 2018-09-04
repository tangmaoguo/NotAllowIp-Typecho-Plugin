<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 网站访问IP黑名单
 * 
 * @package NotAllowIp
 * @author BlackStyle
 * @version 1.0.0
 * @link http://www.phalcon.xyz/
 */
class NotAllowIp_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 插件版本号
     * @var string
     */
    const _VERSION = '1.0.0';
    
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array('NotAllowIp_Plugin', 'header');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 不允许访问网站ip */
        $not_allow_ip = new Typecho_Widget_Helper_Form_Element_Text('not_allow_ip', NULL, NULL, _t('IP黑名单'),'请输入ip地址，如果有多个请使用逗号隔开');
        $form->addInput($not_allow_ip);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    public function header(){
      $returnVal = self::check();
      if(empty($returnVal)){
          # 页面自己自定义
          echo '<body background="usr/plugins/NotAllowIp/bkg.jpg" style="background-size:cover;">';
          echo '<span style="text-align: center;display: block;margin: 20px auto;font-size: 5em;color:#CC0000;">您的IP访问异常，已被封</span>';
          exit;
      }
    }
    /**
     * 检测ip黑名单
     * 
     * @access public
     * @return bool
     */
    public static function check()
    {

        static $realip = NULL;
        //判断服务器是否允许$_SERVER
        if(isset($_SERVER)) {
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        }else{
            //不允许就使用getenv获取
            if(getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv( "HTTP_X_FORWARDED_FOR");
            }elseif(getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            }else {
                $realip = getenv("REMOTE_ADDR");
            }
        }

        if($realip !== NULL){
            $config = json_decode(json_encode(unserialize(Helper::options()->plugin('NotAllowIp'))));
            $not_allow_ip_arr = str_replace('，',',',$config->not_allow_ip);
            $not_allow_ip = explode(',', $not_allow_ip_arr);

            if(!empty($config->not_allow_ip)){
                if(in_array($realip,$not_allow_ip)){
                   return false;
                }
            }
            return true;
        }else{
            return false;
        }
    }
}
