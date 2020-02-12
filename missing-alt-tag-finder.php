<?php
/*
Plugin Name: Missing Alt Tag Finder
Plugin URI: http://wordpress.org/extend/plugins/missing-alt-tag-finder/
Version: 0.1
Author: Chris Steurer
Description: Find images on your website that are missing alt tags.
Text Domain: cs_missing-alt-tag-finder
License: GPLv3
*/



/**
 * Create The Admin Menu Page
 */
add_action('admin_menu', 'test_plugin_setup_menu');
function test_plugin_setup_menu(){
	add_menu_page( 'Missing Alt Tag Finder', 'Missing Alt Tags', 'manage_options', 'missing-alt-tag-finder', 'showAllImagesMissingAltTags' );
}



/**
 * Load Styles for the Plugin
 */
add_action('admin_enqueue_scripts', 'cs_mat_load_styles');
function cs_mat_load_styles($hook) {

	$current_screen = get_current_screen();

	if ( strpos($current_screen->base, 'missing-alt-tag-finder') === false) {
		return;
	} else {
		wp_enqueue_style('cs_mat_css', plugins_url('assets/css/style.css',__FILE__ ));
	}
}



/**
 * Get All Attachments
 *
 * @desc Get all of the Media Attachments.
 * @return $all_media (array) An array with details for the media attachments.
 */
function getAllMediaAttachments() {
	// Set a blank array to store the media attachments
	$all_media = array();
	
	// Set the arguements for the get_posts function
	$args = array(
		'post_type'=>'attachment',
		'numberposts'=> -1,
		'post_status'=> null
	);

	// Get the the attachment post types
	$attachments = get_posts($args);
	
	// If there are any attachements, add them to the $all_media array
	if( $attachments ) {
		foreach( $attachments as $attachment ) {

			// If the type of media attachment is an image, get the details and add it to the $all_media array
			if( substr( $attachment->post_mime_type, 0, 5 ) === "image" ) {

				$alt_text = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

				$media_item = array(
					'id' => $attachment->ID,
					'name' => $attachment->post_title,
					'type' => $attachment->post_mime_type,
					'guid' => $attachment->guid,
					'alt_text' => $alt_text
				);

				$all_media[] = $media_item;

			}

		}
	}

	// If the array is not empty
	if( !empty($all_media) ) {
		// Return the array of media attachment objects
		return $all_media;
	} else {
		return false;
	}

}



/**
 * Find Images With Missing Alt Tags
 *
 * @desc Filter a list of all the media to find the ones that are missing alt tags.
 * @return $imagesMissingAltTags (array) An array containing details for the images that are missing alt tags.
 */
function findMissingAltTags() {
	$attachments = getAllMediaAttachments();
	$imagesMissingAltTags = array();
	foreach ( $attachments as $key => $value ) {
		if( $value['alt_text'] === '' ) {
			$imagesMissingAltTags[] = $value;
		}
	}

	return $imagesMissingAltTags;

}



/**
 * Show All Images Missing Alt Tags
 *
 * @desc Show all of the Media Attachments that are missing alt tags
 * @return HTML Echo the HTML to display the missing image cards
 */
function showAllImagesMissingAltTags() {

	$attachments = findMissingAltTags();

	if( $attachments ) {

		echo '<h1>Total Images Missing Alt Tags: <span class="cs_mat_count">' . count($attachments) . '</span></h1>';

		echo '<ul class="media-missing-alt-tags">';
		foreach ($attachments as $key => $value) {
			$thumbnail = wp_get_attachment_image_src($value['id'], 'thumbnail');
			?>
			<li class="media-item">
				<header>
					<img src="<?php echo $thumbnail[0]; ?>">
				</header>
				

				<h3 class="image-name"><?php echo $value['name']; ?></h3>
				<div class="media-type"><?php echo substr($value['type'], 6); ?></div>

				<a class="btn btn-default" href="<?php echo $value['guid']; ?>" target="_blank">Full Size</a>
				<a class="btn btn-primary" href="http://10.220.0.4/~csteurer/bang/wp-admin/upload.php?item=<?php echo $value['id']; ?>" target="_blank">Edit</a>
				
			</li>
			<?php
		}
		echo '</ul>';

	}

}