<?php
/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Framework\Container;

/**
 * @author Juan Carlos Trabado herodoto@telefonica.net
 * @package chamilo.social
 */

$cidReset = true;

api_block_anonymous_users();

if (api_get_setting('social.allow_social_tool') != 'true') {
    api_not_allowed();
}

if (api_get_setting('platform.allow_my_files') === 'false') {
    api_not_allowed(true);
}

$this_section = SECTION_SOCIAL;
$_SESSION['this_section'] = $this_section;

$interbreadcrumb[] = array(
    'url' => 'profile.php',
    'name' => get_lang('SocialNetwork')
);
$interbreadcrumb[] = array('url' => '#', 'name' => get_lang('MyFiles'));

$htmlHeadXtra[] = '
<script>

function denied_friend (element_input) {
	name_button=$(element_input).attr("id");
	name_div_id="id_"+name_button.substring(13);
	user_id=name_div_id.split("_");
	friend_user_id=user_id[1];
	 $.ajax({
		contentType: "application/x-www-form-urlencoded",
		beforeSend: function(objeto) {
		$("#id_response").html("<img src=\'../inc/lib/javascript/indicator.gif\' />"); },
		type: "POST",
		url: "' . api_get_path(WEB_AJAX_PATH) . 'social.ajax.php?a=deny_friend",
		data: "denied_friend_id="+friend_user_id,
		success: function(datos) {
		 $("div#"+name_div_id).hide("slow");
		 $("#id_response").html(datos);
		}
	});
}
function register_friend(element_input) {
    if(confirm("' . get_lang('AddToFriends') . '")) {
    	name_button=$(element_input).attr("id");
    	name_div_id="id_"+name_button.substring(13);
    	user_id=name_div_id.split("_");
    	user_friend_id=user_id[1];
    	 $.ajax({
    		contentType: "application/x-www-form-urlencoded",
    		beforeSend: function(objeto) {
    		$("div#dpending_"+user_friend_id).html("<img src=\'../inc/lib/javascript/indicator.gif\' />"); },
    		type: "POST",
    		url: "' . api_get_path(WEB_AJAX_PATH) . 'social.ajax.php?a=add_friend",
    		data: "friend_id="+user_friend_id+"&is_my_friend="+"friend",
    		success: function(datos) {  $("div#"+name_div_id).hide("slow");
    			$("form").submit()
    		}
    	});
    }
}

$(document).on("ready", function () {
    $("#el-finder").elfinder({
        url: "' . api_get_path(WEB_LIBRARY_PATH) . 'elfinder/php/connector.php",
        lang: "' . api_get_language_isocode() . '",
        height: 600,
        resizable: false,
        rememberLastDir: false,
    }).elfinder("instance");
});

</script>';

// Social Menu Block
$social_menu_block = SocialManager::show_social_menu('myfiles');
$actions = null;

if (isset($_GET['cidReq'])) {
    $actions = '<a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?cidReq=' . Security::remove_XSS(
            $_GET['cidReq']
        ) . '&amp;id_session=' . intval($_GET['id_session']) . '&amp;gidReq=' . intval($_GET['gidReq']) . '&amp;id=' . intval($_GET['parent_id']) . '">' . Display::return_icon(
            'back.png',
            get_lang('BackTo') . ' ' . get_lang('Documents') . ' (' . get_lang('Course') . ')'
        ) . '</a>';
}

$tpl = Container::getTwig();
SocialManager::setSocialUserBlock($tpl, api_get_user_id(), 'myfiles');

$tpl->addGlobal('course_id', '');
$tpl->addGlobal('session_id', '');
$tpl->addGlobal('social_right_content', '');
$tpl->addGlobal('social_menu_block', $social_menu_block);
$tpl->addGlobal('actions', $actions);

echo $tpl->render('@template_style/social/myfiles.html.twig');

