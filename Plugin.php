<?php
/**
 * slack推送评论通知
 * 
 * @package Comment2Slack
 * @author kaze
 * @version 0.0.1
 * @link #
 */
class Comment2Slack_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Comment2Slack_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Comment2Slack_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Comment2Slack_Plugin', 'sc_send');
        
        return _t('请配置此插件, 以使您的slack推送生效');
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
        $webhook = new Typecho_Widget_Helper_Form_Element_Text('webhook', NULL, NULL, _t('WEBHOOK'), _t('slack webhook地址'));
        $channel = new Typecho_Widget_Helper_Form_Element_Text('channel', NULL, NULL, _t('CHANNEL'), _t('slack channel'));
        $username = new Typecho_Widget_Helper_Form_Element_Text('username', NULL, NULL, _t('USERNAME'), _t('slack username'));

        $form->addInput($webhook->addRule('required', _t('您必须填写一个正确的slack webhook地址')));
        $form->addInput($channel->addRule('required', _t('您必须填写一个正确的channel')));
        $form->addInput($username->addRule('required', _t('您必须填写一个正确的username')));

    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * slack推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $webhook = $options->plugin('Comment2Slack')->webhook;
        $channel = $options->plugin('Comment2Slack')->channel;
        $username = $options->plugin('Comment2Slack')->username;

        $text = "有人在您的博客发表了评论";
        $desp = "**".$comment['author']."** 在 [「".$post->title."」](".$post->permalink." \"".$post->title."\") 中说到: \n\n > ".$comment['text'];

        $postdata = array('text' => $text.":\n".$desp,'channel' => $channel,'username' => $username);

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => json_encode($postdata),
                'timeout' => 3
                )
            );
        $context  = stream_context_create($opts);
        $result = file_get_contents($webhook, false, $context);
        return  $comment;
    }
}
