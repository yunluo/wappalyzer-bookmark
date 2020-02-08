<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }


if (gdk_option('gdk_cdn'))                  add_action('wp_loaded', 'gdk_cdn_start');//七牛CDN
if (gdk_option('gdk_link_go'))     add_filter('the_content','gdk_link_go',999);// 外链GO跳转
if (gdk_option('gdk_smtp'))         add_action('phpmailer_init', 'gdk_smtp');//SMTP
if (gdk_option('gdk_cdn_water'))     add_filter('the_content', 'gdk_cdn_water');//CDN水印

//文章首尾添加自定义内容
function gdk_add_content($content) {
	$before = gdk_option('gdk_artical_top');
	$after = gdk_option('gdk_artical_bottom');
	$content = $before.'<br>'.$content.'<br>'.$after;
	return $content;
}
add_filter('the_content', 'gdk_add_content');

//社交头像
function gdk_wx_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	$user = false;
	if ( is_numeric( $id_or_email ) ) {
		$id = (int) $id_or_email;
		$user = get_user_by( 'id' , $id );
	} elseif ( is_object( $id_or_email ) ) {
		if ( ! empty( $id_or_email->user_id ) ) {
			$id = (int) $id_or_email->user_id;
			$user = get_user_by( 'id' , $id );
		}
	} else {
		$user = get_user_by( 'email', $id_or_email );
	}
	if ( $user && is_object( $user ) ) {
		if( get_user_meta($user->data->ID,'wx_avatar',true) ) {
			$avatar = get_user_meta($user->data->ID,'wx_avatar',true);
			$avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		}
	}
	return $avatar;
}
add_filter('get_avatar', 'gdk_wx_avatar' , 19 , 5);

//头像解决方案
function gdk_switch_get_avatar( $avatar ) {
	switch (gdk_option('gdk_switch_get_avatar')) {
		case 1:
		  $rand_avatar = 'https://cdn.jsdelivr.net/gh/yunluo/GitCafeApi/avatar/' . mt_rand(1, 1999) . '.jpg';
		$avatar = "<img src=\"$rand_avatar\" class='avatar rand_avatar photo' />";
		break;
		case 2:
		  $avatar = preg_replace("/http[s]{0,1}:\/\/(secure|www|\d).gravatar.com\/avatar\//","//cdn.v2ex.com/gravatar/",$avatar);
		break;
		default:
		  $avatar = preg_replace("/http[s]{0,1}:\/\/(secure|www|\d).gravatar.com\/avatar\//","//dn-qiniu-avatar.qbox.me/avatar/",$avatar);
	}
	return $avatar;
}
add_filter('get_avatar', 'gdk_switch_get_avatar');

//懒加载
if(gdk_option('gdk_lazyload')){
function gdk_lazyload($content) {
	if (!is_feed() || !is_robots()) {
		$content = preg_replace('/<img(.+)src=[\'"]([^\'"]+)[\'"](.*)>/i', "<img\$1data-original=\"\$2\" \$3>\n<noscript>\$0</noscript>", $content);
	}
	return $content;
}
add_filter('the_content', 'gdk_lazyload');
}

//强制兼容<pre>,和下面转义代码搭配使用
function gdk_prettify_replace($text) {
    $replace = array(
        '<pre>' => '<pre class="prettyprint linenums">'
    );
    $text = str_replace(array_keys($replace) , $replace, $text);
    return $text;
}
add_filter('content_save_pre', 'gdk_prettify_replace');

//强制阻止WordPress代码转义,适用于<pre class="prettyprint linenums"> </pre>
function gdk_esc_html($content) {
    $regex = '/(<pre\s+[^>]*?class\s*?=\s*?[",\'].*?prettyprint.*?[",\'].*?>)(.*?)(<\/pre>)/sim';
    return preg_replace_callback($regex, 'gdk_esc_callback', $content);
}
function gdk_esc_callback($matches) {
    $tag_open = $matches[1];
    $content = $matches[2];
    $tag_close = $matches[3];
    $content = esc_html($content);
    return $tag_open . $content . $tag_close;
}
add_filter('the_content', 'gdk_esc_html', 2);
add_filter('comment_text', 'gdk_esc_html', 2);

//fancybox图片灯箱效果
if(gdk_option('gdk_lazyload')){
function gdk_fancybox($content) {
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\\/a>/i";
	$replacement = '<a$1href=$2$3.$4$5 data-fancybox="gallery" rel="box" class="fancybox"$6>$7</a>';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}
add_filter('the_content', 'gdk_fancybox');
}

//GO跳转
function gdk_link_go($content) {
	if(file_exists(ABSPATH.'go.php')) {
		$gourl = home_url().'/go.php';
	} else {
		$gourl = GDK_BASE_URL.'public/go.php';
	}
	preg_match_all('/<a(.*?)href="(.*?)"(.*?)>/',$content,$matches);
	if($matches) {
		foreach($matches[2] as $val) {
			if(in_string($val,'://') && !in_string($val,home_url()) && !preg_match('/\.(jpg|jepg|png|ico|bmp|gif|tiff)/i',$val) && !preg_match('/(ed2k|thunder|Flashget|flashget|qqdl):\/\//i',$val)) {
				$content=str_replace("href=\"$val\"", "href=\"".$gourl."?url=$val\" ",$content);
			}
		}
	}
	return $content;
}

//邮箱SMTP设置
function gdk_smtp( $phpmailer ) {
	$phpmailer->FromName = gdk_option('gdk_smtp_mail'); //邮箱地址
	$phpmailer->Host = gdk_option('gdk_smtp_host');//服务器地址
	$phpmailer->Port = gdk_option('gdk_smtp_port'); //端口
	$phpmailer->Username = gdk_option('gdk_smtp_username'); //昵称
	$phpmailer->Password = gdk_option('gdk_smtp_password'); //密码
	$phpmailer->From = gdk_option('gdk_smtp_mail'); //邮箱地址
	$phpmailer->SMTPAuth = true; 
	$phpmailer->SMTPSecure = 'ssl';
	$phpmailer->IsSMTP();
}

// CDN
function gdk_cdn_start() {
	ob_start('gdk_cdn_replace');
}
function gdk_cdn_replace($html) {
	$local_host = home_url();//博客域名
	$cdn_host = gdk_option('gdk_cdn_host');//cdn域名
	$cdn_exts = gdk_option('gdk_cdn_ext');//扩展名（使用|分隔）
	$cdn_dirs = gdk_option('gdk_cdn_dir');//目录（使用|分隔）
	$cdn_dirs = str_replace('-', '\-', $cdn_dirs);
	if ($cdn_dirs) {
		$regex = '/' . str_replace('/', '\/', $local_host) . '\/((' . $cdn_dirs . ')\/[^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
            $html = preg_replace($regex, $cdn_host . '/$1$4', $html);
        } else {
            $regex = '/' . str_replace('/', '\/', $local_host) . '\/([^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
            $html = preg_replace($regex, $cdn_host . '/$1$3', $html);
        }
        return $html;
    }


//CDN水印
function gdk_cdn_water($content) {
	if (get_post_type() == 'post') {
		$pattern = "/<img(.*?)src=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
		$replacement = '<img$1src=$2$3.$4!water.jpg$5$6>';
		$content = preg_replace($pattern, $replacement, $content);
	}
	return $content;
}


//压缩html代码
if (gdk_option('gdk_compress')) {
    function gdk_compress_html(){
        function gdk_compress_html_callback($buffer){
            if ( substr( ltrim( $buffer ), 0, 5) == '<?xml' ) return $buffer;
            $initial = strlen($buffer);
            $buffer = explode("<!--wp-compress-html-->", $buffer);
			$count = count($buffer);
			$i = '';
            for ($i = 0; $i <= $count; $i++) {
                if (stristr($buffer[$i], '<!--wp-compress-html no compression-->')) {
                    $buffer[$i] = str_replace("<!--wp-compress-html no compression-->", " ", $buffer[$i]);
                } else {
                    $buffer[$i] = str_replace("\t", " ", $buffer[$i]);
                    $buffer[$i] = str_replace("\n\n", "\n", $buffer[$i]);
                    $buffer[$i] = str_replace("\n", "", $buffer[$i]);
                    $buffer[$i] = str_replace("\r", "", $buffer[$i]);
                    while (stristr($buffer[$i], '  ')) {
                        $buffer[$i] = str_replace("  ", " ", $buffer[$i]);
                    }
                }
                $buffer_out .= $buffer[$i];
            }
            $final = strlen($buffer_out);
            if ($initial !== 0) {
                $savings = ($initial - $final) / $initial * 100;
            } else {
                $savings = 0;
            }
            $savings = round($savings, 2);
            $buffer_out .= "\n<!--压缩前的大小: {$initial} bytes; 压缩后的大小: {$final} bytes; 节约：{$savings}% -->";
            return $buffer_out;
        }
            ob_start("gdk_compress_html_callback");
    }
	add_action('get_header', 'gdk_compress_html');
	
    function gdk_unCompress($content)
    {
        if (preg_match_all('/(crayon-|<?xml|script|textarea|<\\/pre>)/i', $content, $matches)) {
            $content = '<!--wp-compress-html--><!--wp-compress-html no compression-->' . $content;
            $content .= '<!--wp-compress-html no compression--><!--wp-compress-html-->';
        }
        return $content;
    }
    add_filter('the_content', 'gdk_unCompress');
}

//只搜索文章标题
function gdk_search_by_title($search, $wp_query) {
    if (!empty($search) && !empty($wp_query->query_vars['search_terms'])) {
        global $wpdb;
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = array();
        foreach ((array)$q['search_terms'] as $term) {
            $search[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $n . $wpdb->esc_like($term) . $n);
        }
        if (!is_user_logged_in()) {
            $search[] = "{$wpdb->posts}.post_password = ''";
        }
        $search = ' AND ' . implode(' AND ', $search);
    }
    return $search;
}
add_filter('posts_search', 'gdk_search_by_title', 10, 2);

//评论地址更换
function gdk_comment_author( $query_vars ) {
	if ( array_key_exists( 'author_name', $query_vars ) ) {
		global $wpdb;
		$author_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'first_name' AND meta_value = %s", $query_vars['author_name'] ) );
		if ( $author_id ) {
			$query_vars['author'] = $author_id;
			unset( $query_vars['author_name'] );
		}
	}
	return $query_vars;
}
add_filter( 'request', 'gdk_comment_author' );

function gdk_comment_author_link( $link, $author_id, $author_nicename ) {
	$my_name = get_user_meta( $author_id, 'first_name', true );
	if ( $my_name ) {
		$link = str_replace( $author_nicename, $my_name, $link );
	}
	return $link;
}
add_filter( 'author_link', 'gdk_comment_author_link', 10, 3 );

//文章目录,来自露兜,云落修改
if (gdk_option('gdk_article_list')) {
    function article_index($content) {
        $matches = array();
        $ul_li = '';
        $r = "/<h2>([^<]+)<\/h2>/im";
        if (is_single() && preg_match_all($r, $content, $matches)) {
            foreach ($matches[1] as $num => $title) {
                $title = trim(strip_tags($title));
                $content = str_replace($matches[0][$num], '<h2 id="title-' . $num . '">' . $title . '</h2>', $content);
                $ul_li.= '<li><a href="#title-' . $num . '">' . $title . "</a></li>\n";
            }
            $content = '<div id="article-index">
                            <strong>文章目录<a class="hidetoc">[隐藏]</a></strong>
                            <ul id="index-ul">' . $ul_li . '</ul>
                        </div>' . $content;
        }
        return $content;
    }
    add_filter('the_content', 'article_index');
}