<?php
if (!defined('R_PILOT')) {
    exit();
}
error_reporting(-1);
if (!empty($_POST['sidebar_name'])) {
    $sidebar_id = $_POST['sidebar_id'];
    $sidebar_name = $_POST['sidebar_name'];
    $sidebar_type = $_POST['sidebar_type'];
    $sidebar_category_tags = $_POST['sidebar_category_tags'];
    $sidebar_custom_link = $_POST['sidebar_custom_link'];
    $sidebar_icon = $_POST['sidebar_icon'];
    $sidebar_ordering = $_POST['sidebar_ordering'] < 1 ? 0 : $_POST['sidebar_ordering'];
    if (!($sidebar_type == 'category' || $sidebar_type == 'tags')) {
        $sidebar_category_tags = 'none';
    }
    if ($sidebar_category_tags != 'none') {
        $sidebar_category_tags = explode("-", $sidebar_category_tags);
        if (count($sidebar_category_tags) > 1) {
            if ($sidebar_category_tags[0] != $sidebar_type) {
                $data['error_message'] = 'Sidebar type unmatched.';
            } else {
                $insert = update_sidebar($sidebar_id, $sidebar_name, $sidebar_type, $sidebar_category_tags[1], $sidebar_custom_link, $sidebar_icon, $sidebar_ordering);
                if ($insert === true) {
                    $data['status'] = 200;
                    $data['href'] = siteUrl() . "/admin/sidebar";
                    $data['success_message'] = 'Success';
                } else {
                    $data['error_message'] = $insert;
                }
            }
        } else {
            $data['error_message'] = $lang['error_message'];
        }
    }else if ($sidebar_type == 'custom'){
        if($sidebar_custom_link != ""){
            $insert = update_sidebar($sidebar_id, $sidebar_name, $sidebar_type, $sidebar_category_tags[1], $sidebar_custom_link, $sidebar_icon, $sidebar_ordering);
    
            if ($insert === true) {
                $data['status'] = 200;
                $data['href'] = siteUrl()."/admin/sidebar";
                $data['success_message'] = 'Success';
            } else {
                $data['error_message'] = $insert;
            }
        }else{
            $data['error_message'] = "Custom link required";
        }
    } else {
        $insert = update_sidebar($sidebar_id, $sidebar_name, $sidebar_type, $sidebar_category_tags[1], $sidebar_custom_link, $sidebar_icon, $sidebar_ordering);

        if ($insert === true) {
            $data['status'] = 200;
            $data['href'] = siteUrl()."/admin/sidebar";
            $data['success_message'] = 'Success';
        } else {
            $data['error_message'] = $insert;
        }
    }
} else {
    $data['error_message'] = $lang['error_message'];
}


function update_sidebar($id, $name, $type, $category_tags, $custom_link, $icon, $ordering)
{
    global $GameMonetizeConnect, $lang;
    $category_tags = $category_tags == 'none' ? 0 : $category_tags;

    $insert = $GameMonetizeConnect->query("UPDATE " . SIDEBAR . " SET name = '{$name}', type = '{$type}', category_tags_id = '{$category_tags}', custom_link = '{$custom_link}', icon = '{$icon}', ordering = '{$ordering}' WHERE id = {$id} ");
    if ($insert) {
        return true;
    }

    var_dump($GameMonetizeConnect->error);die;

    if ($GameMonetizeConnect->errno == '1062') {
        return "The sidebar already exists.";
    }
    return $lang['error_message'];
}
