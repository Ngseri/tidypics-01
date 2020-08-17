<?php
/**
 * Upload images
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

elgg_gatekeeper();

$album_guid = elgg_extract('guid', $vars);
if (!$album_guid) {
	forward('', '404');
}

$album = get_entity($album_guid);
if (!($album instanceof TidypicsAlbum)) {
	forward('', '404');
}

if (!$album->getContainerEntity()->canWriteToContainer()) {
	forward('', '404');
}

// set page owner based on container (user or group)
elgg_set_page_owner_guid($album->getContainerGUID());
$owner = elgg_get_page_owner_entity();
elgg_entity_gatekeeper($album_guid, 'object', $album->subtype);

$title = elgg_echo('album:addpix');

// set up breadcrumbs
elgg_push_breadcrumb(elgg_echo('photos'), 'photos/siteimagesall');
elgg_push_breadcrumb(elgg_echo('tidypics:albums'), 'photos/all');
elgg_push_breadcrumb($owner->name, "photos/owner/$owner->username");
elgg_push_breadcrumb($album->getTitle(), $album->getURL());
elgg_push_breadcrumb(elgg_echo('album:addpix'));

$uploader = elgg_extract('uploader', $vars);
if ($uploader == 'basic') {
	$form_vars = [
		'action' => 'action/photos/image/upload',
		'enctype' => 'multipart/form-data',
		'class' => 'elgg-form-settings',
	];
	$body_vars = [
		'entity' => $album,
	];
	$content = elgg_view_form('photos/basic_upload', $form_vars, $body_vars);
} else {
	// Load the JavaScript and CSS libs
	elgg_require_js(elgg_get_simplecache_url('tidypics/js/plupload/i18n/' . tidypics_get_plugload_language() . '.js'));

	//Seri 8/11/2020 - replaced elgg_require_{css,js} with elgg_load_external_file
	elgg_load_external_file('js', 'plupload-js');
	elgg_load_external_file('js', 'jquery-plupload');
	elgg_load_external_file('css', 'jqueryui-theme');
	elgg_load_external_file('css', 'plupload-css');

	elgg_require_js('tidypics/uploading');
	$content = elgg_view('forms/photos/ajax_upload', ['entity' => $album]);
}

$body = elgg_view_layout('content', [
	'content' => $content,
	'title' => $title,
	'filter' => '',
	'sidebar' => elgg_view('photos/sidebar_im', ['page' => 'upload']),
]);

echo elgg_view_page($title, $body);
