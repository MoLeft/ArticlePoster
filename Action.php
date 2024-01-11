<?php
class ArticlePoster_Action extends Typecho_Widget implements Widget_Interface_Do
{

    private $db;
    private $res;
    private $info;

    public function __construct($request, $response, $params = NULL)
    {
        $this->info['sitename'] = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->sitename;
        $this->info['introduction'] = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->introduction;
        $this->info['author'] = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->author;
        $this->info['qq'] = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->qq;
        $this->db  = Typecho_Db::get();
        $this->res = new Typecho_Response();
        parent::__construct($request, $response, $params);
        if (method_exists($this, $this->request->type)) {
            call_user_func(array(
                $this,
                $this->request->type
            ));
        } else {
            $this->defaults();
        }
    }

    public function make()
    {
        if (empty($_GET['cid'])) {
            $this->export("请填写cid", -100);
        }
        $cid = self::GET('cid');
        $array = $this->get_artcle($cid);
        if (!$array) {
            $this->export("获取文章失败", -100);
        }
        $folder = dirname(__FILE__) . '/poster/';
        is_dir($folder) or mkdir($folder, 0777, true);
        if (file_exists($folder . 'cid-' . $cid . '.png')) {
            $this->export(Helper::options()->pluginUrl . '/ArticlePoster/poster/cid-' . $cid . '.png');
        }
        $this->info['title'] = $array['title'];
        $this->info['content'] = $array['content'];
        $this->info['time'] = $array['time'];
        $qq_setting = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->qq_setting;
        $this->info['link'] = urlencode($array['link']);
        foreach ($this->info as $v) {
            if (empty($v) || count($this->info) != 8) {
                $this->export("请联系网站管理员配置相关信息！", -100);
            }
        }
        $plugins_info = $this->get_plugins_info();
        if ($plugins_info) {
            $timestamp = md5($plugins_info['author'] . $plugins_info['package']);
        } else {
            $timestamp = md5(date("Y-m-d H:i:s"));
        }
        $token = 0 + mt_rand() / mt_getrandmax() * (1 - 0);
        $api = Typecho_Widget::widget('Widget_Options')->plugin('ArticlePoster')->service;
        $result = $this->get_curl($api . "?t=" . $token, "sitename=" . $this->info['sitename'] . "&introduction=" . $this->info['introduction'] . "&link=" . $this->info['link'] . "&title=" . $this->info['title'] . "&content=" . strip_tags($this->info['content']) . "&time=" . $this->info['time'] . "&author=" . $this->info['author'] . "&qq=" . $this->info['qq'] . "&type=" . $qq_setting . "&timestamp={$timestamp}");
        //file_put_contents(dirname(__FILE__).'/run.log',$result);
        if (empty($result)) {
            $this->export('当前节点不可用，请联系站长更换节点！', -100);
        }
        $res = json_decode($result, true);
        if ($res['code'] != 1) {
            $this->export($res['msg'], -100);
        }
        $a = file_put_contents($folder . 'cid-' . $cid . '.png', base64_decode($res['img']));
        if ($a) {
            $this->export(Helper::options()->pluginUrl . '/ArticlePoster/poster/cid-' . $cid . '.png');
        } else {
            $this->export("海报保存失败!", -100);
        }
    }

    public function get_plugins_info()
    {
        Typecho_Widget::widget('Widget_Plugins_List@activated', 'activated=1')->to($activatedPlugins);
        $activatedPlugins = json_decode(json_encode($activatedPlugins), true);

        // 确保 $plugins_list 是一个数组
        $plugins_list = is_array($activatedPlugins['stack']) ? $activatedPlugins['stack'] : [];
        $plugins_info = array();

        foreach ($plugins_list as $plugin) {
            if (isset($plugin['title']) && $plugin['title'] == 'ArticlePoster') {
                $plugins_info = $plugin;
                break;
            }
        }

        // 使用 !empty 来检查 $plugins_info 是否非空
        return !empty($plugins_info) ? $plugins_info : false;
    }

    public function get_artcle($cid)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $select = $this->db->select('cid', 'title', 'created', 'text', 'type')->from('table.contents')->where('status = ?', 'publish')->where('created < ?', time())->where('cid = ?', $cid);
        $posts  = $this->db->fetchAll($select);
        if (!$posts) {
            return false;
        }
        $posts[0]['created'] = date("Y-m-d H:i:s", $posts[0]['created']);
        $posts[0]['title'] = $posts[0]['title'];
        $posts[0]['text'] = $posts[0]['text'];
        Typecho_Widget::widget('Widget_Archive', 'pageSize=1&type=post', 'cid=' . $cid)->to($link);
        return array('title' => $posts[0]['title'], 'content' => $posts[0]['text'], 'time' => $posts[0]['created'], 'link' => $link->permalink);
    }

    public function export($data = array(), $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        $json = json_encode(
            array(
                'status' => $status,
                'data' => $data
            ),
            JSON_UNESCAPED_UNICODE
        );
        echo $json;
        exit;
    }

    private static function GET($key, $default = '')
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function action()
    {
        $this->on($this->request);
    }

    function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobody = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept:*/*";
        $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
        $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
        $httpheader[] = "Connection:close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
        }
        if ($nobody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
