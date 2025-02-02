<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_ajax_mlzrcp_reset_cpt', 'mlzrcp_reset_cpt');
add_action('wp_ajax_nopriv_mlzrcp_reset_cpt', 'mlzrcp_reset_cpt');
function mlzrcp_reset_cpt() {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'mlzrcp_reset_cpt_nonce' ) ) {
        wp_send_json_error(array('log' => __('Error: Nonce verification failed', 'reset-custom-post') ));
        wp_die();
    }
    $custom_post_type = isset($_POST['custom_post_type']) ? sanitize_text_field($_POST['custom_post_type']) : '';
    $delete_images = isset($_POST['delete_images']) ? intval($_POST['delete_images']) : 0;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $totalPosts = isset($_POST['totalPosts']) ? intval($_POST['totalPosts']) : 0;
    
    $all_posts = get_posts( array(
        'fields' => 'ids',
        'post_type' => $custom_post_type,
        'numberposts' => 1,
        'post_status' => 'any',
        //'offset' => $offset, 
    ) );

    if (empty($all_posts)) {
        // translators: %s represents the custom post type.
        $log_message = sprintf(
            __('Deletion  <strong>%s</strong> completed', 'reset-custom-post'),
            $custom_post_type
        );
        wp_send_json_success(array('progress' => 100, 'log' => __('Deletion completed', 'reset-custom-post')));
        wp_die();
    }
    $progressPercentage = (($offset / $totalPosts) * 100);
    $post_id = $all_posts[0];
    $image_ids = [];
    if ($delete_images) {
        $attachments = get_attached_media('image', $post_id);
        foreach ($attachments as $attachment) {
            $image_ids[] = $attachment->ID;
        }
    }
    $post_title = get_the_title($post_id);
    $res = wp_delete_post( $post_id, true );

    // translators: %1s represents the post title & %2s the post ID.
    $log_message = sprintf(
        __('The post <strong>%1s</strong> - ID : <strong>%2s</strong> is deleted', 'reset-custom-post'),
        $post_title,
        $post_id
    );
    wp_send_json_success(array( 'offset' => $offset, 'progress' => $progressPercentage, 'totalPosts' => $totalPosts, 'post_id' => $post_id , 'post_title' => $post_title, 'imagesIds' => $image_ids, 'log' => $log_message));

    ob_flush();
    flush();
    wp_die();
}

add_action('wp_ajax_mlzrcp_reset_cpt_image', 'mlzrcp_reset_cpt_image');
add_action('wp_ajax_nopriv_mlzrcp_reset_cpt_image', 'mlzrcp_reset_cpt_image');
function mlzrcp_reset_cpt_image() {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'mlzrcp_reset_cpt_nonce' ) ) {
        wp_send_json_error(array('log' => __('Error: Nonce verification failed', 'reset-custom-post') ));
        wp_die();
    }
    
    $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if ($image_id <= 0) {
        wp_send_json_error(array('log' => __('Invalid image ID', 'reset-custom-post') ));
        wp_die();
    }

    $delete_result = wp_delete_attachment($image_id, true);
    //$delete_result = true;

    if ($delete_result !== false) {
        // translators: %1s represents the image ID & %2s the post ID.
        $log_message = sprintf(
            __('Image with ID <strong>%1s</strong> from post <strong>%2s</strong> is deleted', 'reset-custom-post'),
            $image_id,
            $post_id
        );
        wp_send_json_success(array('post_id' => $post_id, 'image_id' => $image_id, 'image_title' =>  get_the_title($image_id), 'log' => $log_message));
    } else {
        wp_send_json_error(array('log' => __('Error deleting image', 'reset-custom-post') ));
    }

    wp_die();
}

add_action('wp_ajax_mlzrcp_get_total_posts', 'mlzrcp_get_total_posts_callback');
add_action('wp_ajax_nopriv_mlzrcp_get_total_posts', 'mlzrcp_get_total_posts_callback');

function mlzrcp_get_total_posts_callback() {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'mlzrcp_reset_cpt_nonce' ) ) {
        wp_send_json_error(array('log' => __('Error: Nonce verification failed', 'reset-custom-post')));
        wp_die();
    }

    $custom_post_type = isset($_POST['custom_post_type']) ? sanitize_text_field($_POST['custom_post_type']) : '';
    $args = array(
        'post_type' => $custom_post_type,
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    $total_posts = $query->found_posts;
    $post_type_object = get_post_type_object($custom_post_type);
    if ($post_type_object) {
        $cpt = $post_type_object->labels->singular_name;
    }
    $taxonomies = get_object_taxonomies($custom_post_type, 'objects');
    $list_taxo = [];
    foreach ($taxonomies as $key => $taxonomy) {
        $list_taxo[$key]['name'][] = $taxonomy->name;
        $list_taxo[$key]['count'][]  = wp_count_terms($taxonomy->name);
    }
    // translators: %1s represents the total of posts & %2s custom post type.
    $msg = sprintf(
        __('%1s %2s', 'reset-custom-post'),
        $total_posts,
        $cpt
    );
    // translators: %s represents the custom post type.
    $log_message = sprintf(
        __('Custom post changed to <strong>%s</strong>', 'reset-custom-post'),
        $cpt
    );

    wp_send_json_success(array('total' => $total_posts, 'msg' => $msg, 'log' => $log_message, 'taxonomies' => $list_taxo));

    wp_die();
}

add_action('wp_ajax_mlzrcp_delete_elements_taxonomy', 'mlzrcp_delete_elements_taxonomy_callback');
add_action('wp_ajax_nopriv_mlzrcp_delete_elements_taxonomy', 'mlzrcp_delete_elements_taxonomy_callback');
function mlzrcp_delete_elements_taxonomy_callback() {

    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'mlzrcp_reset_cpt_nonce' ) ) {
        wp_send_json_error(array('log' => __('Error: Nonce verification failed', 'reset-custom-post')));
        wp_die();    
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('log' => __('Invalid taxonomy name', 'reset-custom-post')));
        wp_die();
    }

    $taxonomy_name = isset($_POST['taxonomy_name']) ? sanitize_text_field($_POST['taxonomy_name']) : '';
    
    if (empty($taxonomy_name && taxonomy_exists($taxonomy_name))) {
        // translators: %s represents the taxonomy name.
        $log_message = sprintf(
            __('Invalid taxonomy name <strong>%s</strong>', 'reset-custom-post'),
            $taxonomy_name
        );
        wp_send_json_error(array('log' => $log_message));
        wp_die();
    }

    $terms = get_terms(array(
        'taxonomy' => $taxonomy_name,
        'hide_empty' => false
    ));
    foreach ($terms as $term_id) {
        wp_delete_term($term_id->term_id, $taxonomy_name);
    }
    // translators: %s represents the taxonomy name.
    $log_message = sprintf(
        __('Elements of taxonomy <strong>%s</strong> deleted successfully', 'reset-custom-post'),
        $taxonomy_name
    );

    wp_send_json_success(array('log' => $log_message, 'reset-custom-post'));

    wp_die();
}
