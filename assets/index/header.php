<?php

if (is_logged()) {
	$themeData['header_user_avatar'] = getAvatar($userData['avatar_id'], $userData['gender'], 'thumb');
	$themeData['user_panel_xp'] = numberFormat($userData['xp']);
	$themeData['csrf_logout_token'] = \GameMonetize\CSRF::set(3, 3600);
}
$themeData['website_name'] = $_SERVER['HTTP_HOST'];
$date =  date('Ymdms');
$date = strtotime($date);
$themeData['cms'] = "<script src='https://api.gamemonetize.com/cms.js?" . $date . "'></script>";
$themeData['cookie'] = ($config['ads_status']) ? '<script type="text/javascript">
window.cookieconsent_options = { "message":"This website uses cookies to ensure you get the best experience on our website.","dismiss":"Got it!","learnMore":"Learn more","link":"/privacy","target":"_blank","theme":"dark-bottom" };
</script>
<script type="text/javascript" src="' . $config['theme_path'] . '/js/cookieconsent.min.js"></script>' : '';

$themeData['header_class_access_menu'] = (is_logged()) ? '_rP5' : '';

$themeData['header_panel_menu_admin'] = (is_logged() && $userData['admin'] == 1) ? \GameMonetize\UI::view('header/header_panel_menu_admin') : '';


if ($_GET['p'] != 'login') {
	if (
		$userData['admin'] == 0
		|| $_GET['p'] == 'play'
		|| $_GET['p'] == 'new-games'
		|| $_GET['p'] == 'search'
		|| $_GET['p'] == 'terms'
		|| $_GET['p'] == 'privacy'
		|| $_GET['p'] == 'about'
		|| $_GET['p'] == 'categories'
		|| $_GET['p'] == 'best-games'
		|| $_GET['p'] == 'featured-games'
		|| $_GET['p'] == 'played-games'
		|| $_GET['p'] == 'tagspage'
		|| $_GET['p'] == 'contact'
		|| $_GET['p'] == 'blogs'
		|| is_page('home')
	) {

		$json = file_get_contents('https://api.gamemonetize.com/cms.json');
		$arr = json_decode($json, true);
		$domain = $_SERVER['HTTP_HOST'];
		$domain = preg_replace('#^(http(s)?://)?w{3}\.#', '$1', $domain);

		try {
			foreach ($arr['response']['games'] as $game) {
				if ($game['domain'] === $domain) {
					header("Location: https://gamemonetize.com?utm_source=blockedcms&domain=" . $domain);
					break;
				}
			}
		} catch (Exception $e) {
		}

		$sql_cat_query = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES);
		$ct_r = '';
		while ($category = $sql_cat_query->fetch_array()) {
			$themeData['category_id'] = $category['id'];
			$themeData['category_name'] = $category['name'];

			$numbergames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES . " where category=" . $category['id']);
			$numbergames = $numbergames->fetch_array()[0];

			$themeData['category_number'] = $numbergames;
			$themeData['category_url'] = siteUrl() . '/category/'	. slugify($category['name']);
			$ct_r .= \GameMonetize\UI::view('category/categories-list-2');
		}

		$themeData['categories_list_2'] = $ct_r;
		$themeData['category_content'] = \GameMonetize\UI::view('category/categories-2');

		$themeData['config_this_year'] =  date("Y");

		$whitelist = array(
			'127.0.0.1',
			'::1'
		);
		$themeData['load_more_url'] = "";
		if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
			$themeData['load_more_url'] = "";
		} else {
			$themeData['load_more_url'] = siteUrl();
		}

		$themeData['footer_bar'] = \GameMonetize\UI::view('footer/footer_bar');
		$themeData['footer_content'] = \GameMonetize\UI::view('footer/content');
		$themeData['header'] = \GameMonetize\UI::view('header/content');
		
		// Get setting data
		$settingDataQuery = "SELECT * FROM " . SETTING . " LIMIT 1";
		$settingData = $GameMonetizeConnect->query($settingDataQuery);
		$settingData = $settingData->fetch_array();
		
		if ($settingData['is_sidebar_enabled']) {
			$themeData['header'] = "";

			// Get sidebar data
			$sidebarItems = "";
			$sidebarDataQuery = "SELECT * FROM " . SIDEBAR . " ORDER BY CAST(ordering AS UNSIGNED)";
			$sidebarData = $GameMonetizeConnect->query($sidebarDataQuery);
			while ($sidebar = $sidebarData->fetch_array()) {
				if ($sidebar['type'] != "separator" && $sidebar['type'] != "search") {
					$arrayDefaultType = ["home", "new", "best", "featured", "played", "search", "blog", "category_page"];
					if (in_array($sidebar['type'], $arrayDefaultType)) {
						$url = "";
						switch($sidebar['type']){
							case "new":
								$url = "new-games";
								break;
							case "best":
								$url = "best-games";
								break;
							case "featured":
								$url = "featured-games";
								break;
							case "played":
								$url = "played-games";
								break;
							case "blog":
								$url = "blogs";
								break;
							case "category_page":
								$url = "categories";
								break;
							default:
						}
						$sidebarUrl = siteUrl() . "/" . $url;
					}
	
					if ($sidebar['type'] == "category") {
						$categoryData = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$sidebar['category_tags_id']} LIMIT 1");
						if ($categoryData !== null) {
							$categoryData = $categoryData->fetch_array();
							$sidebarUrl = siteUrl() . "/category/" . $categoryData['category_pilot'];
						}
					}
	
					if ($sidebar['type'] == "tags") {
						$tagsData = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$sidebar['category_tags_id']} LIMIT 1");
						if ($tagsData !== null) {
							$tagsData = $tagsData->fetch_array();
							$sidebarUrl = siteUrl() . "/tag/" . $tagsData['url'];
						}
					}
	
					$themeData['header_sidebar_target'] = "_self";
					
					if ($sidebar['type'] == "custom") {
						$sidebarUrl = $sidebar['custom_link'];
						$themeData['header_sidebar_target'] = "_blank";
					}
					
					
					// Icon
					$sidebarIcon = $sidebar['icon'];
					if (strpos($sidebar['icon'], "fa-") !== false) {
						$sidebarIcon = "<i class='" . $sidebar['icon'] . "'></i>";
					}
					$themeData['header_sidebar_url'] = $sidebarUrl;
					$themeData['header_sidebar_icon'] = $sidebarIcon;
					$themeData['header_sidebar_name'] = $sidebar['name'];
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/item');
				} else if ($sidebar['type'] == "search") {
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/search');
				} else {
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/separator');
				}
			}
			
			$sidebarItems .= \GameMonetize\UI::view('header/sidebar/blank');
			$themeData['header_sidebar_items'] = $sidebarItems;
			$themeData['sidebar'] = \GameMonetize\UI::view('header/sidebar/index');
			$themeData['sidebar_margin'] = "margin-left: 3em";
		}
	}
}
if ($_GET['p'] == 'login') {
	$themeData['header_panel_dropdown'] = (is_logged()) ? \GameMonetize\UI::view('header/header_user_panel') : '';
	// $themeData['footer_content'] = \GameMonetize\UI::view('footer/content_admin');
}
