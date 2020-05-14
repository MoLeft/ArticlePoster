<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

//define('__TYPECHO_DEBUG__', true); //使用插件出现问题时，可取消此行注释启用debug模式，将错误信息反馈给作者

/**
 * 为文章生成海报，使用效果可查看 >> <a href="http://www.moleft.cn">演示效果</a>
 *
 * @package ArticlePoster
 * @author MoLeft
 * @version 1.0.7
 * @link http://www.moleft.cn/
 */
 
class ArticlePoster_Plugin implements Typecho_Plugin_Interface
{
    
	/* 激活插件方法 */
    public static function activate()
    {
    	Helper::addRoute('ArticlePosterAction_make', '/ArticlePoster/make', 'ArticlePoster_Action','make');
    	Typecho_Plugin::factory('Widget_Archive')->header = array('ArticlePoster_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('ArticlePoster_Plugin', 'footer');
    	return '插件已激活,请设置相关信息';
    }
    
    /* 禁用插件方法 */
    public static function deactivate()
    {
    	Helper::removeRoute('ArticlePosterAction_make');
        return '插件已禁用';
    }
    
    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    	$update = @file_get_contents('http://api.moleft.cn/poster/update.php?version='.self::get_plugins_version());
    	if(!$update){
    		$moleft = array('version'=>'获取失败','notice'=>'获取失败');
    	}else{
    		$moleft = json_decode($update,true);
    	}
    	echo '<div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:10px;" width="100%">';
    	echo '<b>作者的话：</b>'.$moleft['notice'].'<br>';
    	echo '<b>版本更新：</b>'.self::get_plugins_version().' ('.$moleft['version'].')<br>';
    	echo '<b>联系作者：</b><a href="http://wpa.qq.com/msgrd?v=3&uin=1805765171&site=qq&menu=yes">腾讯QQ</a> | <a href="http://github.com/MoLefts">GitHub</a> | <a href="http://t.me/moleft_cn">Telegram</a> | <a href="mailto:admin@moleft.cn">电子邮箱</a> | <a href="https://www.moleft.cn">辣鸡小站</a>';
    	echo '</div>';
        $options = Helper::options();
        $default = array(Helper::options()->pluginUrl.'/ArticlePoster/service/api.php' => _t('本地节点(速度最快，需服务器支持GD库)'));
        $more = json_decode(@file_get_contents('http://api.moleft.cn/poster/service.php'),true);
        if(!$more){
        	$more = array();	
        }
        $service_list = array_merge($default,$more);
        $service = new Typecho_Widget_Helper_Form_Element_Select('service',$service_list,Helper::options()->pluginUrl.'/ArticlePoster/service/api.php',_t('服务器节点'),_t('请根据自己服务器的地区来选择最优节点'));
        $form->addInput($service);
        $sitename = new Typecho_Widget_Helper_Form_Element_Text('sitename', null, $options->title, '网站名称', _t('请填写网站名称，避免海报排版错误请控制长度'));
        $form->addInput($sitename);
        $introduction = new Typecho_Widget_Helper_Form_Element_Text('introduction', null, $options->description, '网站介绍', _t('请填写网站介绍，避免海报排版错误请控制长度'));
        $form->addInput($introduction);
        $author = new Typecho_Widget_Helper_Form_Element_Text('author', null, '', '博主名称', _t('请填写博主名称'));
        $form->addInput($author);
        $qq = new Typecho_Widget_Helper_Form_Element_Text('qq', null, '', '博主扣扣', _t('请填写博主扣扣，以显示头像'));
        $form->addInput($qq);
        $button = new Typecho_Widget_Helper_Form_Element_Textarea('button', null, '<button class="article-poster-button mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-theme-accent mdui-ripple"><i class="mdui-icon mdui-icon-left material-icons">file_download</i>下载海报</button>', '自定义按钮样式', '根据自己模板的按钮样式来自定义分享按钮的样式，在class里面加入<b style="color: #ff0000;">article-poster-button</b>即可使用');
        $form->addInput($button);
        $qq_setting = new Typecho_Widget_Helper_Form_Element_Radio(
            'qq_setting' ,
            array(
                'close' => _t('无操作'),
                'type1' => _t('防举报'),
                'type2' => _t('全屏防举报'),
            ),
            'close' ,
            _t('在QQ里的操作')
        );
        $form->addInput($qq_setting);
        $mdui = new Typecho_Widget_Helper_Form_Element_Radio(
            'mdui',
            array(
              'true' => _t('是'),
              'false' => _t('否'),
            ),
            'true',
            _t('是否加载mdui')
        );
        $form->addInput($mdui);
        $jquery = new Typecho_Widget_Helper_Form_Element_Radio(
            'jquery' ,
            array(
                'true' => _t('是'),
                'false' => _t('否'),
            ),
            'true' ,
            _t('是否加载jquery')
        );
        $form->addInput($jquery);
    }
    
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /* 插件实现方法 */
    public static function render(){}
    
    /* 按钮 */
    public static function button($cid){
    	$options = Helper::options();
    	$config = $options->plugin('ArticlePoster');
    	echo '<!-- ArticlePoster -->';
        echo $config->button;
        echo '<div data-id="'.$cid.'" class="article-poster action action-poster"><div class="poster-popover-mask" data-event="poster-close"></div><div class="poster-popover-box"><a class="poster-download" data-event="poster-download" data-url="">下载海报</a><img class="article-poster-images"/></div></div>';
    }
    
    /* 顶部 */
    public static function header(){
    	echo '<link rel="stylesheet" href="'.Helper::options()->pluginUrl.'/ArticlePoster/css/core.css">';
    	if($options->mdui=='true'){
    	    echo '<link href="https://cdn.bootcdn.net/ajax/libs/mdui/0.4.3/css/mdui.min.css" rel="stylesheet">';
    	}
    }
    
    /* 底部 */
    public static function footer(){
       $options = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster');
       if($options->jquery=='true'){
        	echo '<script src="https://cdn.bootcss.com/jquery/1.10.2/jquery.js"></script>';
       }
       echo '<script src="'.Helper::options()->pluginUrl.'/ArticlePoster/js/core.js"></script>';
    }
    
    public static function get_plugins_version(){
    	Typecho_Widget::widget('Widget_Plugins_List@activated', 'activated=1')->to($activatedPlugins);
    	$activatedPlugins = json_decode(json_encode($activatedPlugins),true);
    	$plugins_list = $activatedPlugins['stack'];
    	$plugins_info = array();
    	for ($i=0;$i<count($plugins_list);$i++){
    		if($plugins_list[$i]['title'] == 'ArticlePoster'){
    			$plugins_info = $plugins_list[$i];
    			break;
    		}
    	}
    	if(count($plugins_info)<1){
    		return false;
    	}else{
    		return $plugins_info['version'];
    	}
    }
}