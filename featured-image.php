<?php
/*
Plugin Name: Featured Image
Plugin URI: https://github.com/hyptx/hyp-featured-image
Description: Add a default featured image or grab first attachment
Version: 1.0
Author: Adam J Nowak
Author URI: http://hyperspatial.com
License: 
*/

function create_fi_menu(){
	add_submenu_page('upload.php','Featured Image','Featured Image','administrator','fi-settings','fi_admin_page');
	add_action('admin_init', 'register_fi_settings');
}
add_action('admin_menu', 'create_fi_menu');

function register_fi_settings(){	
	$input_field_names = array(
	'fi_default_image',
	'fi_include_cats',
	'fi_do_pages',
	);
	foreach($input_field_names as $field_name){ register_setting('fi_settings',$field_name); }
}

function fi_admin_page(){?>
	<style type="text/css">
	.wrap p{margin:20px 0;}
	.wrap label{font-weight:bold;}
	</style>
	<?php
	$fi_default_image = get_option('fi_default_image');
	$fi_include_cats = get_option('fi_include_cats');
	$fi_do_pages = get_option('fi_do_pages');
	?>


	<div class="wrap">
		<div id="wpvp-form-container">
    		<h2>Featured Image Settings</h2>
          <p style="margin-top:-6px;">Use these settings to control default featured images for your Wordpress Install. Below is the order in which images are used:</p>
            <ol>
            <li>Use Featured image</li>
            <li>If no featured image use the first image attached to the post</li>
            <li>If no attachments, use the default image specified below</li>
            </ol>
           
            <form id="fi-form" name="FiForm" method="post" action="options.php">
				<?php settings_fields('fi_settings'); ?>
                <p>
                <label>Default Image ID:</label><br />
                <input name="fi_default_image" type="text" value="<?php echo $fi_default_image ?>" size="10" /><br />
                <span class="help-description">Enter an image id from the <a href="<?php echo get_bloginfo('wpurl')?>/wp-admin/upload.php">media library</a></span>
                </p>
                <p>
                <label>Include Categories:</label><br />
                <input name="fi_include_cats" type="text" value="<?php echo $fi_include_cats ?>" size="100" /><br />
                <span class="help-description">Enter a comma separated list of category ID's or enter 'all' </span>
                </p>
                <p>
                <input name="fi_do_pages" type="checkbox" <?php if($fi_do_pages == 'yes') echo 'checked="checked"' ?> value="yes"/> Apply to Pages (not just posts) 
                </p>
                <p>
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
          </form>
    	</div>
    </div><!-- /.wrap -->
    <?php
                
}

function fi_check_for_featured($post){
	$fi_default_image = get_option('fi_default_image');
	$fi_include_cats =  get_option('fi_include_cats');
	$fi_do_pages =  get_option('fi_do_pages');
	$include_array = $array = explode(',', $fi_include_cats);
	
	if($fi_do_pages != 'yes'){
		if(is_admin() || is_page())	return;
	}
	
	if($fi_include_cats != 'all'){
		foreach($include_array as $include){
			if(!in_category($include,$post->ID)) return;
		}
	}
	
	if(!has_post_thumbnail($post->ID)){
		$args = array('post_type' => 'attachment','numberposts' => 1,'post_parent' => $post->ID); 
		$attachments = get_posts($args);
		if($attachments) add_post_meta($post->ID, '_thumbnail_id', $attachments[0]->ID);
		elseif($fi_default_image) add_post_meta($post->ID, '_thumbnail_id', $fi_default_image);
	}
}

add_action('the_post','fi_check_for_featured');
?>
