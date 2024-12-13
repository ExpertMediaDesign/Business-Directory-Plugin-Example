<?php
/*
Plugin Name: Business Directory Plugin
Plugin URI: http://www.expertmedia.design
Author: Expert Media Design
Author URI: http://www.expertmedia.design
Description: Creating business directory CPT & integrating with PMPro & WooCommerce. Dependedant on PaidMembershipsPro, WooCommerce & ContactForm7.
Version: 1.0
*/
if ( ! function_exists('emd_bd_sites_custom_post_type') ) {

	// Register Custom Post Type
	function emd_bd_sites_custom_post_type() {

		$labels = array(
			'name'                  => _x( 'Profile', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Profiles', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Profiles', 'text_domain' ),
			'name_admin_bar'        => __( 'Profiles', 'text_domain' ),
			'archives'              => __( 'Item Archives', 'text_domain' ),
			'attributes'            => __( 'Item Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
			'all_items'             => __( 'All Items', 'text_domain' ),
			'add_new_item'          => __( 'Add New Item', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Item', 'text_domain' ),
			'edit_item'             => __( 'Edit Item', 'text_domain' ),
			'update_item'           => __( 'Update Item', 'text_domain' ),
			'view_item'             => __( 'View Item', 'text_domain' ),
			'view_items'            => __( 'View Items', 'text_domain' ),
			'search_items'          => __( 'Search Item', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
			'items_list'            => __( 'Items list', 'text_domain' ),
			'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Profiles', 'text_domain' ),
			'description'           => __( 'Customer sites created after payment and form.', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'author' ),
			'taxonomies'            => array( 'post_tag' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 23,
			'menu_icon'             => 'dashicons-images-alt',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => true,
			'show_in_rest' 			=> true,
			// 'rewrite' 				=> array('slug' => '.','with_front' => false),
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'emd_bd_sites', $args );

	}
	add_action( 'init', 'emd_bd_sites_custom_post_type', 0 );

}




/**
 * Add Profiles to frontend queries.
 */
add_action( 'pre_get_posts', 'add_emd_bd_sites_to_query' );
function add_emd_bd_sites_to_query( $query ) {

    if ( is_home() && $query->is_main_query() )
        $query->set( 'post_type', array( 'post', 'emd_bd_sites' ) );
    return $query;

}


add_action( 'init', 'add_emd_bd_sites_taxonomies' );
function add_emd_bd_sites_taxonomies() {

	register_taxonomy(
		'group', 
		'emd_bd_sites', 
		array(
			'hierarchical' => true,
			'labels' => array(
				'name' => __( 'Group' ),
				'singular_name' => __( 'Group' ),
				'all_items' => __( 'All Groups' ),
				'add_new_item' => __( 'Add Group' )
				),
				'public' => true,
			'query_var' => true,
			'show_in_rest' => true,
			'rewrite' => array( 
				'slug' => 'group' 
			),
		)
	);

}




/*
 * Rewriting the custom post type page url: 
 * https://stackoverflow.com/questions/41308652/rewrite-url-for-custom-post-type
 */
add_filter( 'post_type_link', 'emd_bd_remove_cpt_slug', 10, 3 );
function emd_bd_remove_cpt_slug( $post_link, $post, $leavename ) {
 
    if ( 'emd_bd_sites' != $post->post_type || 'publish' != $post->post_status ) {
        return $post_link;
    }
 
    $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
 
    return $post_link;

}
add_action( 'pre_get_posts', 'emd_bd_parse_request_trick' );
function emd_bd_parse_request_trick( $query ) {

    // Only noop the main query
    if ( ! $query->is_main_query() )
        return;

    // Only noop our very specific rewrite rule match
    if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
        return;
    }

    // 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
    if ( ! empty( $query->query['name'] ) ) {
        $query->set( 'post_type', array( 'post', 'page', 'emd_bd_sites' ) );
    }

}





/**
 * 
 * Add our CPT page template
 *
 **/
add_filter('single_template', 'emd_bd_custom_template');
function emd_bd_custom_template($single) {

    global $post;

    /* Checks for single template by post type */
    if ( $post->post_type == 'emd_bd_sites' ) {
        if ( file_exists( plugin_dir_path( __FILE__ ) . '/template/business_directory.php' ) ) {
            return plugin_dir_path( __FILE__ ) . '/template/business_directory.php';
        }
    }

    return $single;

}









/*
 * Processing form data
 * Creating the new profile after purchase on form submission
 *
 * If we switch from Contact Form 7, we should only need to adjust our hook & our form data.
 */
add_action('wpcf7_before_send_mail','emd_bd_profile_creation',10,1);
function emd_bd_profile_creation($contact_form) {
    
    $form_id = $contact_form->id();

    // Copy & paste the form ID from Contact Form 7 here
    if ($form_id == 187):

			$wpcf7 = WPCF7_ContactForm::get_current();
		    $submission = WPCF7_Submission::get_instance();
		    //Below statement will return all data submitted by form.
		    $data = $submission->get_posted_data();


            // Our hidden field for the user email
            $user_email =           $data['user-email'];
            $user = get_user_by( 'email', $user_email );

			// Format form data
            $business_name =        $data['business-name'];
		    $business_phone = 		$data['business-phone'];
			$business_email = 		$data['business-email'];
            $business_website =     $data['business-website'];
			$business_location = 	$data['business-location'];
			$business_desc =	 	$data['business-desc'];

			$format_phone = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $business_phone);

			// Throw form data into 1 array so we can serialize this in wp_postmeta
			$business_meta = array(
		        'business_name' => $business_name,
		        'business_phone' => $format_phone,
		        'business_email' => $business_email,
		        'business_website' => $business_website,
		        'business_location' => $business_location,
		        'business_desc' => $business_desc,
			);

			


			// Creating our page - since it's a CPT, might be wierd
		    $new_page_id = wp_insert_post( array(
                'post_title'     => $business_name,
                // 'post_name'      => $page_link,
		        'post_type'      => 'emd_bd_sites',
		        'comment_status' => 'closed',
		        'ping_status'    => 'closed',
		        'post_content'   => '',
		        'post_status'    => 'publish',
		        'post_author'    => $user->ID,
		        'meta_input' 	 => array(
			        // '_wp_page_template' => $template_choice,
			        'business_meta' => $business_meta,
			    ),
		    ) );

		    // Photo upload deal
		   	// Need to figure out what to do with photo
			$user_photo = 		$data['user-photo'];

		    if (!empty($user_photo)) {
	            $uploaded_files = $submission->uploaded_files();
		        if ($uploaded_files) {
		            foreach ($uploaded_files as $fieldName => $filepath) {
		                //cf7 5.4
		                if (is_array($filepath)) {
		                    foreach ($filepath as $key => $value) {
		                        $attachment_data = emd_bd_create_attachment($value);
		                        set_post_thumbnail( $new_page_id, $attachment_data );
		                        $new_photo_link = wp_get_attachment_url($attachment_data);
		                    }
		                } else {
		                    $attachment_data = emd_bd_create_attachment($filepath);
		                    set_post_thumbnail( $new_page_id, $attachment_data );
		                    $new_photo_link = wp_get_attachment_url($attachment_data);
		                }
		            }
		        }

	        }


		 //    // Update the user's info with extra info
		 //    if (!empty($user_fullname) || $user_fullname !== null ) {
		 //    	update_user_meta( $user->ID, 'first_name', $user_fullname );
		 //    }



	endif;   

    
}

// Change our CPT slugs to numbers instead
// add_action('wp_insert_post', 'emd_bd_change_cpt_slug');
function emd_bd_change_cpt_slug( $post_id ) {

	// Making sure this runs only when a 'eduation' post type is created
	$slug = 'emd_bd_sites';
	if ( $slug != $_POST['post_type'] ) {
		return;
	}

	wp_update_post( array(
		'ID' => $post_id,
		'post_name' => $post_id // slug
	));

}


/*
 * Processing form data
 * Editing the user's profile after creation on form submission
 *
 * If we switch from Contact Form 7, we should only need to adjust our hook & our form data.
 */
add_action('wpcf7_before_send_mail','emd_bd_profile_edit_processing',10,1);
function emd_bd_profile_edit_processing($contact_form) {
    
    $form_id = $contact_form->id();

    // Copy & paste the form ID for the Edit form here
    if ($form_id == 196):

			$wpcf7 = WPCF7_ContactForm::get_current();
		    $submission = WPCF7_Submission::get_instance();
		    //Below statement will return all data submitted by form.
		    $data = $submission->get_posted_data();


            // Our hidden field for the user email
            $user_email =           $data['user-email'];
            $user = get_user_by( 'email', $user_email );

			// Format form data
            $business_name =        $data['business-name'];
		    $business_phone = 		$data['business-phone'];
			$business_email = 		$data['business-email'];
            $business_website =     $data['business-website'];
			$business_location = 	$data['business-location'];
			$business_desc =	 	$data['business-desc'];

			$format_phone = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $business_phone);




			// Get the user's authored profiles
			$args = array(
			    'post_type'  => 'emd_bd_sites',
			    'author'     => $user->ID,
			);
			$authored_posts = get_posts($args);
			// Get the ID from the first (later we can foreach & allow multiple profiles)
			$profile_id = $authored_posts[0]->ID;

			// Get the current Business meta & organize it
			$currentmeta = get_post_meta( $profile_id, 'business_meta', true );
			$formattedmeta = maybe_unserialize( $currentmeta );


			// Update each field in the array if the field wasn't submitted empty
		    // if (!empty($business_name) || $business_name !== null ) {
			if (!empty($business_name)) {
		    	$formattedmeta['business_name'] = $business_name;
		    }
		    if (!empty($business_phone)) {
		    	$formattedmeta['business_phone'] = $format_phone;
		    }
		    if (!empty($business_email)) {
		    	$formattedmeta['business_email'] = $business_email;
		    }
		    if (!empty($business_website)) {
		    	$formattedmeta['business_website'] = $business_website;
		    }
		    if (!empty($business_location)) {
		    	$formattedmeta['business_location'] = $business_location;
		    }
		    if (!empty($business_desc)) {
		    	$formattedmeta['business_desc'] = $business_desc;
		    }
			


			// Updating our post
		    $new_page_id = wp_update_post( array(
		    	'ID' => $profile_id,
                'post_title'     => $formattedmeta['business_name'],
		        'post_type'      => 'emd_bd_sites',
		        // 'post_content'   => implode(" ",$formattedmeta),
		        'meta_input' 	 => array(
			        // '_wp_page_template' => $template_choice,
			        'business_meta' => $formattedmeta,
			    ),
		    ) );

		    // Photo upload deal
		   	// Need to figure out what to do with photo
			$user_photo = 		$data['user-photo'];

		    if (!empty($user_photo)) {
	            $uploaded_files = $submission->uploaded_files();
		        if ($uploaded_files) {
		            foreach ($uploaded_files as $fieldName => $filepath) {
		                //cf7 5.4
		                if (is_array($filepath)) {
		                    foreach ($filepath as $key => $value) {
		                        $attachment_data = emd_bd_create_attachment($value);
		                        set_post_thumbnail( $new_page_id, $attachment_data );
		                        $new_photo_link = wp_get_attachment_url($attachment_data);
		                    }
		                } else {
		                    $attachment_data = emd_bd_create_attachment($filepath);
		                    set_post_thumbnail( $new_page_id, $attachment_data );
		                    $new_photo_link = wp_get_attachment_url($attachment_data);
		                }
		            }
		        }

	        }

	endif;   

}
/*
 * Processing form data
 * Remove the user's listing on form submission
 *
 * If we switch from Contact Form 7, we should only need to adjust our hook & our form data.
 */
add_action('wpcf7_before_send_mail','emd_bd_profile_remove_processing',10,1);
function emd_bd_profile_remove_processing($contact_form) {
    
    $form_id = $contact_form->id();

    // Copy & paste the form ID for the Edit form here
    if ($form_id == 224):

			$wpcf7 = WPCF7_ContactForm::get_current();
		    $submission = WPCF7_Submission::get_instance();
		    //Below statement will return all data submitted by form.
		    $data = $submission->get_posted_data();


            // Our hidden field for the user email
            $user_email =           $data['user-email'];
            $user = get_user_by( 'email', $user_email );

			// Format form data
            $profile_id =  $data['selected-post'];

            // Check if selected post matches a post that the user authored
            $author_id = get_post_field( 'post_author', $profile_id );
            if ( $author_id == $user->ID ) {
            	// If so, delete the post itself
            	wp_delete_post( $profile_id, true);

            }

	endif;   

}


/**
 * When registering a new WP user, add as a member to a specific membership level.
 * 
 * This way, when users register, they can create a profile page.
 * 
 **/

//Disables the pmpro redirect to levels page when user tries to register
add_filter("pmpro_login_redirect", "__return_false");

add_action('user_register', 'emd_bd_pmpro_default_registration_level');
function emd_bd_pmpro_default_registration_level($user_id) {
	//Give all members who register membership level 1
	pmpro_changeMembershipLevel(1, $user_id);
}



/*
 * Reworking the WooCommerce account dashboard
 *
 * New site link, helper text for users, etc.
 */
add_action( 'woocommerce_account_dashboard', 'emd_bd_adding_site_to_woo_dashboard' );
function emd_bd_adding_site_to_woo_dashboard() {

	global $current_user;

	$user_id = get_current_user_id();

	// Count up the user's authored profiles
	$args = array(
	    'post_type'  => 'emd_bd_sites',
	    'author'     => $user_id,
	);
	$authored_posts = get_posts($args);



	$dashboard = '';
	$dashboard .= '<div class="dash-welcome">';

	// If the user has created profiles before
	if (count($authored_posts)) {

		echo '<h3>Your Business Profile\'s Public Link:</h3>';

        foreach ( $authored_posts as $profile ) {
        	$link = get_permalink($profile->ID);
		    $dashboard .= '<p class="business-profile-link"><a href="'. $link .'">'. $profile->post_title .'</a></p>';
		}

	// If no profiles authored by user
	} else {
	    $dashboard .= 'You have not made your business profile yet!<br><a href="/create-new-listing"><div class="button dash-button-big">Create it now!</div></a>';
	}
	$dashboard .= '</div>';
	echo $dashboard;
}



/**
* Edit the account menu - we are hiding this now
*/
add_filter ( 'woocommerce_account_menu_items', 'emd_bd_my_account_menu_order', 100, 2 );
function emd_bd_my_account_menu_order() {
	
	$menuOrder = array(
		'dashboard'          => __( 'Dashboard', 'woocommerce' ),
		'editprofile'             => __( 'Your Listing', 'woocommerce' ),
		'edit-account'    	=> __( 'Edit Your Profile', 'woocommerce' ),
		'removeprofile'             => __( 'Remove Listing', 'woocommerce' ),
		'customer-logout'    => __( 'Logout', 'woocommerce' ),
	);

	unset($menuOrder['orders']);
    unset($menuOrder['subscriptions']);

	return $menuOrder;
}
/*
 * Step 2. Register Permalink Endpoint
 */
add_action( 'init', 'emd_bd_add_endpoints' );
function emd_bd_add_endpoints() {

	add_rewrite_endpoint( 'editprofile', EP_PAGES );
	add_rewrite_endpoint( 'removeprofile', EP_PAGES );

}
/*
 * Step 3. Content for the new pages in My Account
 * 
 * For actions, use woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action( 'woocommerce_account_editprofile_endpoint', 'emd_bd_editprofile_my_account_endpoint_content' );
function emd_bd_editprofile_my_account_endpoint_content() {

	// $user_id = get_current_user_id();

	// // Count up the user's authored profiles
	// $args = array(
	//     'post_type'  => 'emd_bd_sites',
	//     'author'     => $user_id,
	// );
	// $authored_posts = get_posts($args);

	// $profile_id = $authored_posts[0]->ID;

	echo '<h4>Your Listing:</h4>';
	echo do_shortcode( '[contact-form-7 id="196" title="Edit Business Listing"]' ); 

}add_action( 'woocommerce_account_removeprofile_endpoint', 'emd_bd_removeprofile_my_account_endpoint_content' );
function emd_bd_removeprofile_my_account_endpoint_content() {

	echo '<h4>Remove Your Listing:</h4>';
	echo do_shortcode( '[contact-form-7 id="224" title="Request Removal of Listing"]' ); 

}
// Add a new CF7 field for selecting a user's listing
add_action( 'wpcf7_init', 'emd_bd_listing_select' );
function emd_bd_listing_select() {
    wpcf7_add_form_tag( 'postlist', 'emd_custom_post_select2', true ); //If the form-tag has a name part, set this to true.
}
function emd_custom_post_select2( $tag ) {

    $user_id = get_current_user_id();

	// Count up the user's authored profiles
	$args = array(
	    'post_type'  => 'emd_bd_sites',
	    'author'     => $user_id,
	);
	$authored_posts = get_posts($args);

	// $profile_id = $authored_posts[0]->ID;
    // $posts = get_posts( $args );

    if( !count($authored_posts) ) {
    	return;
    }

    $output = "<select name='" . $tag['name'] . "' id='" . $tag['name'] . "' onchange='document.getElementById(\"" . $tag['name'] . "\").value=this.value;'><option></option>";
    // if you've set the name part to true in wpcf7_add_form_tag use $tag['name'] instead of $posttype as the name of the select.
    foreach ( $authored_posts as $post ) {
            $postid = $post->ID;
            $posttitle = get_the_title( $postid );
            $postslug = get_post_field( 'post_name', $postid );
            $postdate = tribe_get_start_date($post, false, $date_format='m/d/y');
        $output .= '<option value="' . $postid . '">' . $posttitle . '&nbsp;' . $postdate . '</option>';
    } // close foreach
    $output .= "</select>";

    return $output;
}
/*
 * Step 4
 */
// Go to Settings > Permalinks and just push "Save Changes" button.









/**
 * 
 * Helper Functions:
 * 
 **/

/**
 * Add Shortcode to grab the post's meta for business profiles
 *  [emd_bd_postmeta tag="business_phone"]
 * Add this shortcode to templates where it makes sense
 **/
add_shortcode( 'emd_bd_postmeta', 'emd_bd_postmeta_shortcode' );
function emd_bd_postmeta_shortcode( $atts ) {

	$post_id = get_the_ID();

	$business_meta = get_post_meta( $post_id, 'business_meta', true);
	$formatted_data = maybe_unserialize($business_meta);
 
    // override default attributes with user attributes
    $a = shortcode_atts( array(
        'tag' => 'business_name',
        'href'  =>  '#'
    ), $atts );

	if( !empty($atts) ) {
		// Use the selected "tag" attribute as the key to our business data array 
		$selected = $formatted_data[$a['tag']];
		return $selected;
	} else {
		return implode(" ", $formatted_data);
	}
	
	return implode(" ", $formatted_data);
}

/**
 * Add Shortcode to grab all Listings
 **/
add_shortcode( 'emd_bd_listings', 'emd_bd_listings_shortcode' );
function emd_bd_listings_shortcode( $atts ) {

	$loop = new WP_Query( array( 'post_type' => 'emd_bd_sites', 'posts_per_page' => 10 ) ); 

	$html = '<div class="emd-bd-listings">';

	while ( $loop->have_posts() ) : $loop->the_post();


		// Format our business meta
		$business_meta = get_post_meta( get_the_ID(), 'business_meta', true);
		$formatted_data = maybe_unserialize($business_meta);

		$html .=  '<div class="emd-bd-entry"><a href="' . get_the_permalink() . '">';

		$html .=  '<div class="emd-bd-photo">';
			$html .= get_the_post_thumbnail();
	    $html .=  '</div>';

		$html .=  '<div class="emd-bd-headline">';
			$html .= '<h4>' . get_the_title() . '</h4>';
	    $html .=  '</div>';


	    $html .=  '</a></div>';


	endwhile;

	$html .=  '</div>';

	return $html;

}


/**
 * Processing Image and Video Attachments 
 * Debug for CF7
 */
// Debug for Contact Form 7 since we use the hook so much
add_action( 'wpcf7_before_send_mail', 'he_debug_cf7_output'); 
function he_debug_cf7_output( $contact_form ) {
    $submission = WPCF7_Submission::get_instance();
    if ( $submission ) {
        $posted_data = $submission->get_posted_data();
        // Dump output to error log
        ob_start();
        var_export($posted_data);
        error_log(ob_get_clean());
    }
    return;
}
// Clean an URL input from form to remove http://
function emd_bd_remove_http($url) {
   $disallowed = array('http://', 'https://');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return $url;
}
// Helper function for image saving to media library
function emd_bd_create_attachment($filename) {
    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype(basename($filename), null);

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    $attachFileName = $wp_upload_dir['path'] . '/' . basename($filename);
    copy($filename, $attachFileName);
    // Prepare an array of post data for the attachment.
    $attachment = array(
        'guid'           => $attachFileName,
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment($attachment, $attachFileName);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $attachFileName);
    wp_update_attachment_metadata($attach_id, $attach_data);
    // return $attach_data;
    return $attach_id;
}

// Helper function for video saving to media library
// Mainly just not using wp_generate_attachment_metadata() since that requires an image
function emd_bd_create_video_attachment($filename) {
    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype(basename($filename), null);

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    $attachFileName = $wp_upload_dir['path'] . '/' . basename($filename);
    copy($filename, $attachFileName);
    // Prepare an array of post data for the attachment.
    $attachment = array(
        'guid'           => $attachFileName,
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment($attachment, $attachFileName);
    return $attach_id;
}
// Get user by display (used in the recommendations form processing)
function get_user_id_by_display_name( $display_name ) {
    global $wpdb;

    if ( ! $user = $wpdb->get_row( $wpdb->prepare(
        "SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $display_name
    ) ) )
        return false;

    return $user->ID;
}
/*
 * Change radio options to images on Edit Style Contact Form 7 form
 */
add_action( 'wpcf7_init', 'emd_bd_add_shortcode_imageradio' );
function emd_bd_add_shortcode_imageradio() {
    wpcf7_add_shortcode( 'imageradio', 'emd_bd_imageradio_handler', true );
}
function emd_bd_imageradio_handler( $tag ){
    $tag = new WPCF7_FormTag( $tag );

    $atts = array(
    'type' => 'radio',
    'name' => $tag->name,
    'list' => $tag->name . '-options' );

    $input = sprintf(
    '<input %s />',
    wpcf7_format_atts( $atts ) );
    $datalist = '';
    $datalist .= '<div class="imgradio">';
    foreach ( $tag->values as $val ) {
    list($radiovalue,$imagepath) = explode("!", $val);

    $datalist .= sprintf( '<label><input type="radio" name="%s" value="%s" class="hideradio" /><img src="%s"></label>', $tag->name, $radiovalue, $imagepath );

    }
    $datalist .= '</div>';

    return $datalist;
}


// Add our custom fields to Edit Account page
// add_action( 'woocommerce_edit_account_form', 'add_custom_fields_to_edit_account_form' );
function add_custom_fields_to_edit_account_form() {
    $user = wp_get_current_user();
    ?>

        <!-- User Title -->
        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
        <label for="user_title"><?php _e( 'Your Title / Subheader', 'woocommerce' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="user_title" id="user_title" value="<?php echo esc_attr( $user->user_title ); ?>" />
        </p>

        <!-- Phone -->
        <p class="woocommerce-form-row woocommerce-form-row--second form-row form-row-second">
        <label for="billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
        </p>

        <!-- User Location -->
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="user_location"><?php _e( 'Location', 'woocommerce' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="user_location" id="user_location" value="<?php echo esc_attr( $user->user_location ); ?>" />
        </p>

        <!-- User Description -->
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="user_desc"><?php _e( 'Description (What Makes You Great!)', 'woocommerce' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="user_desc" id="user_desc" value="<?php echo esc_attr( $user->user_desc ); ?>" />
        </p>

        <!-- Link to Pro Tip PDF -->
        <!-- <div class="order-bt-row">
        <a href="/my-account/orders"><div class="button dash-button-pdf">Download the Highlight Link Pro Tip PDF</div></a>
        </div> -->


    <?php
}

// Save the custom fields from above
// add_action( 'woocommerce_save_account_details', 'save_my_new_account_details', 12, 1 );
function save_my_new_account_details( $user_id ) {

    // For User Title
    if( isset( $_POST['user_title'] ) ) {
        update_user_meta( $user_id, 'user_title', sanitize_text_field( $_POST['user_title'] ) );
    }

    // For User Location
    if( isset( $_POST['user_location'] ) ) {
        update_user_meta( $user_id, 'user_location', sanitize_text_field( $_POST['user_location'] ) );
    }

    // For User Description
    if( isset( $_POST['user_desc'] ) ) {
        update_user_meta( $user_id, 'user_desc', sanitize_text_field( $_POST['user_desc'] ) );
    }

    // For User Phone
    if( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }

    // Updating or Creating the VCard file

    // Need latest user info for the VCard
    $user_fullname = get_user_meta( $user_id, 'first_name', true );
    $user_title = get_user_meta( $user_id, 'user_title', true );
    $user_location = get_user_meta( $user_id, 'user_location', true );
    $user_phone = get_user_meta( $user_id, 'billing_phone', true );
    $format_phone = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $user_phone);

    $site_id = get_user_meta( $user_id, 'user_page', true);
    
    // Email's not meta, so we need to grab full user data
    $user_info = get_userdata($user_id);
    $user_email = $user_info->user_email;




}





