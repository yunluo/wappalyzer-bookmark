<?php
/**
 * Git 主题后台选项
 */

 if ( ! defined( 'WPINC' ) ) {
	 die;
 }

$current_theme = wp_get_theme();
$gdk_default_options = [];
$gdk_options = [];
include('options-config.php');
$gdk_current_options = get_option('gdk_options_setup');

function gdk_update_options() {
	global $gdk_default_options, $gdk_options, $gdk_current_options;
	foreach ($gdk_options as $panel) {
		foreach ($panel as $option) {
			$id = isset( $option['id'] ) ? $option['id'] : '';
			$type = isset( $option['type'] ) ? $option['type'] : '';
			$std = isset( $option['std'] ) ? $option['std'] : '';
			if ( !$id ) continue;
			$gdk_default_options[$id] = $std;
			if ( isset($gdk_current_options[$id]) ) continue;
			$gdk_current_options[$id] = $std;
		}
	}
}
gdk_update_options();

//获取设置选项
function gdk_get_option($id, $returnDefault = false) {
	global $gdk_default_options, $gdk_current_options;
	return stripslashes( $returnDefault ? $gdk_default_options[$id] : $gdk_current_options[$id] );
}

//设置页面模板
function gdk_options_page() {
	global $gdk_options;
?>

<div class="wrap">
	<h2>GDK选项</h2>
	<div class="wp-filter">
	</div>
<?php
	if (isset($_GET['update'])) echo '<div class="updated"><p><strong>设置已保存。</strong></p></div>';
	if (isset($_GET['reset'])) echo '<div class="updated"><p><strong>设置已重置。</strong></p></div>';
	if (isset($_GET['test'])) echo '<div class="updated"><p><strong>如果您的邮箱收到测试邮件，则证明您的SMTP设置是没问题的。</strong></p></div>';
?>

	<div class="wp-filter">
		<ul class="filter-links">
<?php
$activePanelIdx = empty($_GET['panel']) ? 0 : $_GET['panel'];
foreach ( array_keys($gdk_options) as $i => $name ) {
	echo '<li><a href="#panel_' . $i . '" data-panel="' . $i . '" ' . ( $i == $activePanelIdx ? 'class="current"' : '' ) . '>' . $name . '</a></li>';
}
?>
			<li><a href="#panel_about" data-panel="about">关于主题</a></li>
		</ul>
<div class="search-form"><label class="screen-reader-text" for="wp-filter-search-input">筛选主题选项…</label><input placeholder="筛选主题选项…" type="search" id="wp-filter-search-input" class="wp-filter-search"></div>
</div>

<form method="post">
<?php
$index = 0;
foreach ( $gdk_options as $panel ) {
	echo '<div class="panel" id="panel_' . $index . '" ' . ( $index == $activePanelIdx ? ' style="display:block"' : '' ) . '><table class="form-table">';
	foreach ( $panel as $option ) {
		$type = $option['type'];
		if ( $type == 'title' ) {
?>
<tr class="title">
	<th colspan="2">
		<h3><?php echo $option['title']; ?></h3>
		<?php if ( isset( $option['desc'] ) ) echo '<p>' . $option['desc'] . '</p>'; ?>
	</th>
</tr>
<?php
			continue;
		}
		$id = $option['id'];
?>
<tr id="row-<?php echo $id; ?>">
	<th><label for="<?php echo $id; ?>"><?php echo $option['name']; ?></label></th>
	<td>
<?php
switch ( $type ) {
	case 'text':
?>
		<label>
		<input name="<?php echo $id; ?>" class="regular-text" id="<?php echo $id; ?>" type="text" value="<?php echo esc_attr(gdk_get_option( $id )) ?>" />
		</label>
		<p class="description"><?php echo $option['desc']; ?></p>
<?php
	break;
	case 'number':
?>
		<label>
		<input name="<?php echo $id; ?>" class="small-text" id="<?php echo $id; ?>" type="number" value="<?php echo esc_attr(gdk_get_option( $id )) ?>" />
		<span class="description"><?php echo $option['desc']; ?></span>
		</label>
<?php
	break;
	case 'textarea':
?>
		<p><label for="<?php echo $id; ?>"><?php echo $option['desc']; ?></label></p>
		<p><textarea name="<?php echo $id; ?>" id="<?php echo $id; ?>" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(gdk_get_option( $id )) ?></textarea></p>
<?php
	break;
	case 'radio':
?>
		<fieldset>
		<?php foreach ($option['options'] as $val => $name) : ?>
		<label>
			<input type="radio" name="<?php echo $id; ?>" id="<?php echo $id . '_' . $val; ?>" value="<?php echo $val; ?>" <?php checked( gdk_get_option( $id ), $val); ?>>
			<?php echo $name; ?>
		</label>
		<?php endforeach; ?>
		</fieldset>
		<p class="description"><?php echo $option['desc']; ?></p>
<?php
	break;
	case 'checkbox':
?>
		<label>
			<input type='checkbox' name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="1" <?php echo checked(gdk_get_option($id)); ?> />
			<span><?php echo $option['desc']; ?></span>
		</label>
<?php
	break;
	case 'checkboxs':
?>
		<fieldset>
		<?php $checkboxValues = gdk_get_option( $id );
		if ( !is_array($checkboxValues) ) $checkboxValues = [];
		foreach ( $option['options'] as $id => $name ) : ?>
		<label>
			<input type="checkbox" name="<?php echo $id; ?>[]" id="<?php echo $id; ?>[]" value="<?php echo $id; ?>" <?php checked( in_array($id, $checkboxValues), true); ?>>
			<?php echo $name; ?>
		</label>
		<?php endforeach; ?>
		</fieldset>
		<p class="description"><?php echo $option['desc']; ?></p>
<?php
	break;
	default:
?>
		<label>
		<input name="<?php echo $id; ?>" class="regular-text" id="<?php echo $id; ?>" type="<?php echo $type; ?>" value="<?php echo esc_attr(gdk_get_option( $id )) ?>" />
		</label>
		<p class="description"><?php echo $option['desc']; ?></p>
<?php
	break;
}
	echo '</td></tr>';
	}
		echo '</table></div>';
		$index++;
}
?>

	<div class="panel" id="panel_about">
		<table class="form-table">
			<tr>
				<th><h4>云落小贴士</h4></th>
				<td>
					<p>哈哈</p>
				</td>
			</tr>
			<tr>
				<th><h4>联系方式</h4></th>
				<td>
					<ul>
						<li>ＱＱ：865113728（推荐）</li>
						<li>邮箱：<a href="mailto:sp91@qq.com">sp91@qq.com</a></li>
						<li><p style="font-size:14px;color:#72777c">* 和主题无关的问题恕不回复</p></li>
					</ul>
				</td>
			</tr>
			<tr>
				<th><h4>相关链接</h4></th>
				<td>
					<ul>
						<li>主题发布页面：<a target="_blank" href="https://gitcafe.net/archives/3589.html">https://gitcafe.net/archives/3589.html</a></li>
						<li>使用文档页面：<a target="_blank" href="https://gitcafe.net/archives/3275.html">https://gitcafe.net/archives/3275.html</a></li>
						<li>代码托管页面：<a target="_blank" href="https://dev.tencent.com/u/googlo/p/Git/git">https://dev.tencent.com/u/googlo/p/Git/git</a></li>
						<li>更新日志页面：<a target="_blank" href="https://gitcafe.net/tool/gitrss.php">https://gitcafe.net/tool/gitrss.php</a></li>
						<li>主题反馈页面：<a target="_blank" href="https://support.qq.com/products/51158">https://support.qq.com/products/51158</a></li>
					</ul>
				</td>
			</tr>
			<tr>
				<th><h4>第三方支持</h4></th>
				<td>
					<ul>
						<li>感谢以下组织或个人：</li>
						<li>PayJs 、Eapay、WeAuth小程序、Cloud9 、Cloud Studio、Coding 、Gitee 、Github、Server酱、jsDelivr、V2EX</li>
						<li>露兜、畅萌、小影、大前端、知更鸟、yusi等等</li>
					</ul>
				</td>
			</tr>
		</table>
	</div>
	<p class="submit">
		<input name="submit" type="submit" class="button button-primary" value="保存更改"/>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="panel" value="<?php echo $activePanelIdx; ?>" id="active_panel_name" />
	</p>
</form>
<form method="post">
	<p class="submit">
		<input name="test" type="submit" class="button button-secondary" value="SMTP测试" onclick="return confirm('点击后网站将向邮箱【<?php echo get_bloginfo( 'admin_email' );?>】发送测试邮件，如果网站卡死或者邮箱未收到测试邮件就是SMTP邮箱未设置好，未卡死并邮箱【<?php echo get_bloginfo( 'admin_email' );?>】收到邮件证明SMTP功能完好');"/>
		<input type="hidden" name="action" value="test" />
	</p>
</form>
<form method="post">
	<p class="submit">
		<input name="reset" type="submit" class="button button-secondary" value="重置选项" onclick="return confirm('你确定要重置选项吗？');"/>
		<input type="hidden" name="action" value="reset" />
	</p>
</form>
</div>

<!-- 静态资源css&js -->
<style>
.panel {
	display: none;
	margin: 0 20px;
}
.panel h3 {
	margin: 0;
	border-bottom: 1px solid #d2d3e0;
	padding-bottom: 5px;
}
.panel th {
	font-weight: normal;
}

.wp-filter {
	padding: 0 20px;
	margin-bottom: 0;
}

.filter-links .current {
    border-bottom: 4px solid #6b48ff;
}

.wp-filter .drawer-toggle:before {
	content: "\f463";
	color: #fff!important;
	background: #e14d43;
	border-radius: 50%;
	box-shadow: inset 0 0 0 2px #e14d43, 0 0 0 2px #e14d43;
}

.wrap.searching .nav-tab-wrapper a,
.wrap.searching .panel tr,
body.show-filters .wrap form {
	display: none
}

.wrap.searching .panel {
	display: block!important;
}

.filter-drawer {
	padding-top: 0;
	padding-bottom: 0;
}
.filter-drawer ul {
	list-style: disc inside;
}

</style>
<style id="theme-options-filter"></style>
<script>
/* global wp */
jQuery(function ($) {
	var $body = $("body");
	var $themeOptionsFilter = $("#theme-options-filter");
	var $wpFilterSearchInput = $("#wp-filter-search-input");

	$(".filter-links a").click(function () {
		$(this).addClass("current").parent().siblings().children(".current").removeClass("current");
		$(".panel").hide();
		$($(this).attr("href")).show();
		$("#active_panel_name").val($(this).data("panel"));
		$body.removeClass("show-filters");
		return false;
	});

	if ($wpFilterSearchInput.is(":visible")) {
		var wrap = $(".wrap");

		$(".panel tr").each(function () {
			$(this).attr("data-searchtext", $(this).text().replace(/\r|\n|px/g, '').replace(/ +/g, ' ').replace(/^\s+|\s+$/g, '').toLowerCase());
		});

		$wpFilterSearchInput.on("input", function () {
			var text = $(this).val().trim().toLowerCase();
			if (text) {
				wrap.addClass("searching");
				$themeOptionsFilter.text(".wrap.searching .panel tr[data-searchtext*='" + text + "']{display:block}");
			} else {
				wrap.removeClass("searching");
				$themeOptionsFilter.text("");
			}
		});
	}

	$(".wrap form").submit(function(){
		$(".submit .button").prop("disabled", true);
		$(this).find(".submit .button").val("正在提交…");
	});
});
</script>
<?php
}



function gdk_add_options_page() {
	global $gdk_options;
	if ( isset($_POST['action']) && isset($_GET['page']) && $_GET['page'] == 'gdk-options' ) {
		$action = $_POST['action'];
		switch ( $action ) {
			case 'update':
				$_POST['uid'] = uniqid();
				update_option('gdk_options_setup', $_POST);
				gdk_update_options();
				header('Location: admin.php?page=gdk-options&update=true&panel=' . $_POST['panel']);
				break;
			case 'reset':
				delete_option('gdk_options_setup');
				gdk_update_options();
				header('Location: admin.php?page=gdk-options&reset=true&panel=' . $_POST['panel']);
				break;
			case 'test':
				wp_mail( get_bloginfo( 'admin_email' ) ,'[TEST]SMTP测试邮件','SMTP测试内容，当您收到这封邮件的时候，证明您的网站SMTP配置已成功！');
				header('Location: admin.php?page=gdk-options&test=true&panel=' . $_POST['panel']);
				break;
		}
		exit;
	}
	add_menu_page( 'GDK选项', 'GDK选项', 'manage_options', 'gdk-options', 'gdk_options_page','dashicons-buddicons-replies' );
}
add_action( 'admin_menu', 'gdk_add_options_page' );