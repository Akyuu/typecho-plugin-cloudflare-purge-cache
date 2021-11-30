<?php
/**
 * Cloudflare-Purge-Cache
 *
 * @package CloudflarePurgeCache
 * @author Alice
 * @version 1.0
 * @link https://blog.sandtears.com/
 */

class CloudflarePurgeCache_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件
     */
    public static function activate()
    {
        // 文章更新
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('CloudflarePurgeCache_Plugin', 'purge_cache');
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishDelete = array('CloudflarePurgeCache_Plugin', 'purge_cache');
        
        // 页面更新
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('CloudflarePurgeCache_Plugin', 'purge_cache');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishDelete = array('CloudflarePurgeCache_Plugin', 'purge_cache');

        // 评论更新
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('CloudflarePurgeCache_Plugin', 'purge_cache');
        Typecho_Plugin::factory('Widget_Comments_Edit')->finishDelete = array('CloudflarePurgeCache_Plugin', 'purge_cache');
    }

    /**
     * 禁用插件
     */
    public static function deactivate()
    {}

    /**
     * 插件设置
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
        $element = new Typecho_Widget_Helper_Form_Element_Text('zone_id', NULL, '', _t('Zone ID'), '');
        $form->addInput($element);

        $element = new Typecho_Widget_Helper_Form_Element_Text('email', NULL, '', _t('E-mail'), '');
        $form->addInput($element);

        $element = new Typecho_Widget_Helper_Form_Element_Text('api_token', NULL, '', _t('API Token'), '');
        $form->addInput($element);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    /* 插件实现方法 */
    public static function purge_cache() {
        $zone_id = Typecho_Widget::widget('Widget_Options')->plugin('CloudflarePurgeCache')->zone_id;
        $email = Typecho_Widget::widget('Widget_Options')->plugin('CloudflarePurgeCache')->email;
        $api_token = Typecho_Widget::widget('Widget_Options')->plugin('CloudflarePurgeCache')->api_token;
        
        $headers = array("X-Auth-Email: $email", "Authorization: Bearer $api_token", "Content-Type: application/json");
        $url = "https://api.cloudflare.com/client/v4/zones/$zone_id/purge_cache";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,'{"purge_everything":true}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }
}
