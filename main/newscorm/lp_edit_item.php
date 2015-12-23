<?php
/* For licensing terms, see /license.txt */

/**
 * This is a learning path creation and player tool in Chamilo - previously learnpath_handler.php
 *
 * @author Patrick Cool
 * @author Denes Nagy
 * @author Roan Embrechts, refactoring and code cleaning
 * @author Yannick Warnier <ywarnier@beeznest.org> - cleaning and update for new SCORM tool
 * @author Julio Montoya  - Improving the list of templates
 * @package chamilo.learnpath
*/

$this_section = SECTION_COURSES;

api_protect_course_script();

/* Libraries */

include 'learnpath_functions.inc.php';
//include '../resourcelinker/resourcelinker.inc.php';
include 'resourcelinker.inc.php';

/* Header and action code */

$htmlHeadXtra[] = '
<script>'.$_SESSION['oLP']->get_js_dropdown_array().

'function load_cbo(id){' ."\n" .
  'if (!id) {return false;}'.
  'var cbo = document.getElementById(\'previous\');' .
  'for(var i = cbo.length - 1; i > 0; i--) {' .
    'cbo.options[i] = null;' .
  '}' ."\n" .
  'var k=0;' .
  'for(var i = 1; i <= child_name[id].length; i++){' ."\n" .
  '  cbo.options[i] = new Option(child_name[id][i-1], child_value[id][i-1]);' ."\n" .
  '  k=i;' ."\n" .
  '}' ."\n" .
  //'if( typeof cbo != "undefined" ) {'."\n" .
  'cbo.options[k].selected = true;'."\n" .
   //'}'."\n" .

   '$(\'#previous\').selectpicker(\'refresh\');' .
'}

$(document).on("ready", function() {
    CKEDITOR.on("instanceReady", function (e) {
        showTemplates("content_lp");
    });
});
</script>';

/* Constants and variables */

$is_allowed_to_edit = api_is_allowed_to_edit(null, true);
$tbl_lp = Database::get_course_table(TABLE_LP_MAIN);

$isStudentView = isset($_REQUEST['isStudentView']) ? intval($_REQUEST['isStudentView']) : null;
$learnpath_id = (int) $_REQUEST['lp_id'];
$submit = isset($_POST['submit_button']) ? $_POST['submit_button'] : null;

/* MAIN CODE */

// Using the resource linker as a tool for adding resources to the learning path.
if ($action == 'add' && $type == 'learnpathitem') {
     $htmlHeadXtra[] = "<script language='JavaScript' type='text/javascript'> window.location=\"../resourcelinker/resourcelinker.php?source_id=5&action=$action&learnpath_id=$learnpath_id&chapter_id=$chapter_id&originalresource=no\"; </script>";
}
if ((!$is_allowed_to_edit) || ($isStudentView)) {
    error_log('New LP - User not authorized in lp_add_item.php');
    header('location:lp_controller.php?action=view&lp_id='.$learnpath_id);
}
// From here on, we are admin because of the previous condition, so don't check anymore.

$course_id = api_get_course_int_id();
$sql = "SELECT * FROM $tbl_lp
        WHERE c_id = $course_id AND id = $learnpath_id";
$result = Database::query($sql);
$therow = Database::fetch_array($result);

/*
    Course admin section
    - all the functions not available for students - always available in this case (page only shown to admin)
*/

/* SHOWING THE ADMIN TOOLS */

if (isset($_SESSION['gradebook'])) {
    $gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
    $interbreadcrumb[] = array (
        'url' => '../gradebook/'.$_SESSION['gradebook_dest'],
        'name' => get_lang('ToolGradebook')
    );
}
$interbreadcrumb[] = array('url' => 'lp_controller.php?action=list', 'name' => get_lang('LearningPaths'));
$interbreadcrumb[] = array('url' => api_get_self()."?action=build&lp_id=$learnpath_id", 'name' => stripslashes("{$therow['name']}"));
$interbreadcrumb[] = array('url' => api_get_self()."?action=add_item&type=step&lp_id=$learnpath_id", 'name' => get_lang('NewStep'));

// Theme calls.
$show_learn_path = true;
$lp_theme_css = $_SESSION['oLP']->get_theme();

Display::display_header(get_lang('Edit'),'Path');
$suredel = trim(get_lang('AreYouSureToDeleteJS'));

?>
<script>
/* <![CDATA[ */
function stripslashes(str) {
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    str=str.replace(/\\\\/g,'\\');
    str=str.replace(/\\0/g,'\0');
    return str;
}
function confirmation(name) {
    name=stripslashes(name);
    if (confirm("<?php echo $suredel; ?> " + name + " ?")) {
        return true;
    } else {
        return false;
    }
}

$(document).ready(function() {
    $('.lp-btn-associate-forum').on('click', function (e) {
        var associate = confirm('<?php echo get_lang('ConfirmAssociateForumToLPItem') ?>');

        if (!associate) {
            e.preventDefault();
        }
    });

    $('.lp-btn-dissociate-forum').on('click', function (e) {
        var dissociate = confirm('<?php echo get_lang('ConfirmDissociateForumToLPItem') ?>');

        if (!dissociate) {
            e.preventDefault();
        }
    });
});
</script>
<?php


/* DISPLAY SECTION */

echo $_SESSION['oLP']->build_action_menu();

echo '<div class="row">';
echo '<div class="col-md-3">';

$path_item = isset($_GET['path_item']) ? $_GET['path_item'] : 0;
$path_item = Database::escape_string($path_item);
$tbl_doc = Database :: get_course_table(TABLE_DOCUMENT);
$sql_doc = "SELECT path FROM " . $tbl_doc . "
            WHERE c_id = $course_id AND id = '". $path_item."' ";

$res_doc = Database::query($sql_doc);
$path_file = Database::result($res_doc, 0, 0);
$path_parts = pathinfo($path_file);

if (Database::num_rows($res_doc) > 0 && $path_parts['extension'] == 'html') {
    echo $_SESSION['oLP']->return_new_tree();

    // Show the template list
    echo '<div id="frmModel" class="lp-add-item"></div>';
} else {
    echo $_SESSION['oLP']->return_new_tree();
}

echo '</div>';
echo '<div class="col-md-9">';

if (isset($is_success) && $is_success === true) {
    $msg = '<div class="lp_message" style="margin-bottom:10px;">';
    $msg .= 'The item has been edited.';
    $msg .= '</div>';
    echo $_SESSION['oLP']->display_item($_GET['id'], $msg);
} else {
    echo $_SESSION['oLP']->display_edit_item($_GET['id']);
}

echo '</div>';
echo '</div>';

/* FOOTER */
Display::display_footer();
