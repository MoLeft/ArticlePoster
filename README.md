# ArticlePoster
免费的Typecho文章海报插件，基于GD库，使用效果欢迎前往 -> [我的博客](https://www.moleft.cn)
# 安装说明
1.将插件上传到**/usr/plugins/**，并重命名为**ArticlePoster**
2.修改post.php，在合适的位置加入挂载点
```php
<?php ArticlePoster_Plugin::button($this->cid); ?>
```
3.在后台插件设置填写好信息，一定要填**自定义分享按钮样式**，并且在class里面加入**article-poster-button**
4.如果你的模板没有引入jquery或者上述过程都设置好了点击按钮无响应，可以开启加载jquery
5.修改图标部分可以找到`/usr/plugins/ArticlePoster/js/core.js`，修改注释部分图标样式
# pjax适配
自1.0.6之后重新调整对于pjax的适配方案，如果主题有pjax回调可以直接填下以下代码，如果没有那么推荐你使用[Cuckoo](https://github.com/bhaoo/cuckoo)主题
```js
$('.article-poster-button').on('click',function(){
	create_poster();
});
$('[data-event=\'poster-close\']').on('click', function(){
	$('.article-poster, .poster-popover-mask, .poster-popover-box').fadeOut()
});
$('[data-event=\'poster-download\']').on('click', function(){
	download_poster();
});
```
# 更新日志
2020-05-14更新说明：
* 新增本地节点，可以自己魔改了
* 修复计算文章阅读时间不准确
* 修复文章中有markdown语法
* 自定义头像和自定义头图懒得写
* 懒得修改按钮样式的可以直接引入mdui了

2020-04-06更新说明：
* 不出意外这是最近一段时间内最后一次更新
* 又㕛叒叕重写了一下适配pjax主题的部分
* 为了考虑国外主机的小伙伴，特意增加了节点选择，可以选择速度快的服务器了
* 填了一下之前留下的坑，并且现在不需要去申请token了
* ~~关于很多人提到的自定义封面图再次推迟~~

2020-03-24更新说明：
* 修复本插件在设置了自定义文章路径的网站获取不到链接的bug

2020-03-20临时更新：
* 修复无法启用插件(这是我的锅，写代码的时候没有注意先后顺序)
* 使用不了时可以按照Plugin.php第6行的方法来反馈bug

2020-03-20更新说明：
* 修复未开启页面重写无法生成海报(无伪静态孩纸的福音)
* 新增检查更新功能，麻麻再也不用担心我用旧版本了
* 新增QQ防举报，全屏防举报功能
* 重写部分逻辑，效果更稳定

2020-03-19临时更新：
* 为防止插件被别有用心的人收费，特加入鉴权机制，需要申请token之后才可以使用(免费哒)
* 增加模态框展示海报，直接下载太丑了
* 再再再次修复无法在pjax主题中使用
* 自定义按钮样式不再是`article-poster`，请改成`article-poster-button`
* ~~下一个版本再加检测更新~~

2020-03-19更新说明:
* 修复无法在pjax主题中使用
* 将js保存到一个单独文件

2020-03-18更新说明:
* 使用GD库生成海报
* 支持自定义引入jquery
* 支持自定义按钮样式
* 海报默认保存到本地
