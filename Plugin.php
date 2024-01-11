<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 为文章生成海报，原作者 MoLeft：http://www.moleft.cn/，本插件由浅梦修改
 *
 * @package ArticlePoster
 * @author MoLeft
 * @author 浅梦
 * @version 1.0.8
 * @link https://letanml.xyz/
 */
class ArticlePoster_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        Helper::addRoute('ArticlePosterAction_make', '/ArticlePoster/make', 'ArticlePoster_Action', 'make');
        Typecho_Plugin::factory('Widget_Archive')->header = array('ArticlePoster_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('ArticlePoster_Plugin', 'footer');
        return '插件已激活，请设置相关信息';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
        Helper::removeRoute('ArticlePosterAction_make');
        return '插件已禁用';
    }

    /**
     * 获取插件配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $options = Helper::options();
        $service_list = array(
            $options->pluginUrl . '/ArticlePoster/service/api.php' => _t('本地节点(速度最快，需服务器支持GD库)')
        );

        $service = new Typecho_Widget_Helper_Form_Element_Select(
            'service',
            $service_list,
            $options->pluginUrl . '/ArticlePoster/service/api.php',
            _t('服务器节点'),
            _t('本地节点提供最快的服务速度，确保您的服务器支持GD库')
        );
        $form->addInput($service);

        $sitename = new Typecho_Widget_Helper_Form_Element_Text(
            'sitename',
            null,
            $options->title,
            _t('网站名称'),
            _t('请填写网站名称，避免海报排版错误请控制长度')
        );
        $form->addInput($sitename);

        $introduction = new Typecho_Widget_Helper_Form_Element_Text(
            'introduction',
            null,
            $options->description,
            _t('网站介绍'),
            _t('请填写网站介绍，避免海报排版错误请控制长度')
        );
        $form->addInput($introduction);

        $author = new Typecho_Widget_Helper_Form_Element_Text(
            'author',
            null,
            '',
            _t('博主名称'),
            _t('请填写博主名称')
        );
        $form->addInput($author);

        $qq = new Typecho_Widget_Helper_Form_Element_Text(
            'qq',
            null,
            '',
            _t('博主扣扣'),
            _t('请填写博主扣扣，以显示头像')
        );
        $form->addInput($qq);

        $button = new Typecho_Widget_Helper_Form_Element_Textarea(
            'button',
            null,
            '<button class="article-poster-button mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-theme-accent mdui-ripple"><i class="mdui-icon mdui-icon-left material-icons">file_download</i>下载海报</button>',
            _t('自定义按钮样式'),
            _t('根据自己模板的按钮样式来自定义分享按钮的样式，在class里面加入<b style="color: #ff0000;">article-poster-button</b>即可使用')
        );
        $form->addInput($button);

        $qq_setting = new Typecho_Widget_Helper_Form_Element_Radio(
            'qq_setting',
            array('close' => _t('无操作'), 'type1' => _t('防举报'), 'type2' => _t('全屏防举报')),
            'close',
            _t('在QQ里的操作')
        );
        $form->addInput($qq_setting);

        $jquery = new Typecho_Widget_Helper_Form_Element_Radio(
            'jquery',
            array('true' => _t('是'), 'false' => _t('否')),
            'true',
            _t('是否加载jquery')
        );
        $form->addInput($jquery);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     */
    public static function render()
    {
    }

    public static function button($cid)
    {
        $options = Helper::options();
        $config = $options->plugin('ArticlePoster');
        echo '<!-- ArticlePoster -->';
        echo $config->button;
        echo '<div data-id="' . $cid . '" class="article-poster action action-poster"><div class="poster-popover-mask" data-event="poster-close"></div><div class="poster-popover-box"><a class="poster-download" data-event="poster-download" data-url="">下载海报</a><img class="article-poster-images"/></div></div>';
    }

    public static function header()
    {
        $options = Helper::options();
        echo '<link rel="stylesheet" href="' . $options->pluginUrl . '/ArticlePoster/css/core.css">';
        echo '<link rel="stylesheet" href="' . $options->pluginUrl . '/ArticlePoster/css/iconfont.css">';
    }

    public static function footer()
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster');
        if ($options->jquery == 'true') {
            echo '<script src="' . Helper::options()->pluginUrl . '/ArticlePoster/js/jquery.min.js"></script>';
        }
        echo '<script src="' . Helper::options()->pluginUrl . '/ArticlePoster/js/core.js"></script>';
    }
}
