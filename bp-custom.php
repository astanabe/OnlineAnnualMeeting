<?php

// Disable adminbar menu
function ast_remove_adminbar_menu( $wp_admin_bar ) {
    if ( current_user_can( 'administrator' ) ) {
        return;
    }
    $wp_admin_bar->remove_menu( 'wp-logo' );      // WPロゴ
    $wp_admin_bar->remove_menu( 'bp-register' );  // 登録メニュー
    $wp_admin_bar->remove_menu( 'bp-login' );     // ログインメニュー
    $wp_admin_bar->remove_menu( 'Protection' );   // Protection
    $wp_admin_bar->remove_menu( 'site-name' );    // サイト名
    $wp_admin_bar->remove_menu( 'view-site' );    // サイト名 -> サイトを表示
    $wp_admin_bar->remove_menu( 'dashboard' );    // サイト名 -> ダッシュボード (公開側)
    $wp_admin_bar->remove_menu( 'themes' );       // サイト名 -> テーマ (公開側)
    $wp_admin_bar->remove_menu( 'customize' );    // サイト名 -> カスタマイズ (公開側)
    $wp_admin_bar->remove_menu( 'comments' );     // コメント
    $wp_admin_bar->remove_menu( 'updates' );      // 更新
    $wp_admin_bar->remove_menu( 'view' );         // 投稿を表示
    $wp_admin_bar->remove_menu( 'edit' );         // 編集
    $wp_admin_bar->remove_menu( 'new-content' );  // 新規
    $wp_admin_bar->remove_menu( 'new-post' );     // 新規 -> 投稿
    $wp_admin_bar->remove_menu( 'new-media' );    // 新規 -> メディア
    $wp_admin_bar->remove_menu( 'new-link' );     // 新規 -> リンク
    $wp_admin_bar->remove_menu( 'new-page' );     // 新規 -> 固定ページ
    $wp_admin_bar->remove_menu( 'new-user' );     // 新規 -> ユーザー
    $wp_admin_bar->remove_menu( 'search' );       // 検索 (公開側)
}
add_action('admin_bar_menu', 'ast_remove_adminbar_menu', 300);

// Enable page category
function ast_add_pagecategory(){
    register_taxonomy_for_object_type('category', 'page');
}
add_action('init','ast_add_pagecategory');

function ast_add_pagecategoryarchive( $query ) {
    if ( $query->is_category== true && $query->is_main_query() ) {
        $query->set('post_type', array( 'post', 'page' ));
    }
}
add_action( 'pre_get_posts', 'ast_add_pagecategoryarchive' );

// Set E-mail notifications
function ast_set_email_notifications_preference( $user_id ) {
    $settings = array(
        'notification_activity_new_mention'         => 'no',
        'notification_activity_new_reply'           => 'no',
        'notification_messages_new_message'         => 'yes',
        'notification_friends_friendship_accepted'  => 'no',
        'notification_friends_friendship_request'   => 'no',
        'notification_groups_invite'                => 'yes',
        'notification_groups_group_updated'         => 'no',
        'notification_groups_admin_promotion'       => 'yes',
        'notification_groups_membership_request'    => 'yes',
        'notification_membership_request_completed' => 'yes',
    );
    foreach( $settings as $setting => $preference ) {
        bp_update_user_meta( $user_id,  $setting, $preference );
    }
}
add_action( 'bp_core_activated_user', 'ast_set_email_notifications_preference');

// Redirect pages of BuddyPress for not-login users to permission-denied
function ast_guest_redirect() {
    global $bp;
    // not logged in user will be redirected to front page
    if ( bp_is_activity_component() || bp_is_groups_component() || bp_is_blogs_component() ||  bp_is_members_component() || is_bbpress() ) {
        if ( !is_user_logged_in() ) { 
            wp_redirect( get_option('siteurl') . '/permission-denied' );
        } 
    }
}
add_filter( 'get_header', 'ast_guest_redirect', 1 );

// Disable group join/leave/request-membership button
function ast_disable_group_join_button() {
    global $groups_template;
    if ( bp_is_active(group) ) return '';
}
add_filter( 'bp_get_group_join_button', 'ast_disable_group_join_button' );

// Increase the number of members per page
function ast_change_numberofmembers_perpage_group( $loop ) {
    if ( bp_is_groups_directory() ) $loop['per_page'] = 50;
    return $loop;
}
add_filter( 'bp_after_has_groups_parse_args', 'ast_change_numberofmembers_perpage_group' );

function ast_change_numberofmembers_perpage_groupmembers( $loop ) {
    if ( bp_is_group_members() ) $loop['per_page'] = 50;
    return $loop;
}
add_filter( 'bp_after_group_has_members_parse_args', 'ast_change_numberofmembers_perpage_groupmembers' );

function ast_change_numberofmembers_perpage_members( $loop ) {
    if ( bp_is_members_directory() ) $loop['per_page'] = 50;
    return $loop;
}
add_filter( 'bp_after_has_members_parse_args', 'ast_change_numberofmembers_perpage_members' );

// Disable changing name
function ast_disable_name_change( $data ) {
    if ( $data->field_id == 1 ) $data->field_id = false;
    return $data;
}
add_action( 'xprofile_data_before_save', 'ast_disable_name_change' );

function ast_hide_profile_field_group( $retval ) {
    if ( bp_is_active( 'xprofile' ) ) {
        if ( !is_super_admin() ) {
            $retval['exclude_fields'] = '1';
            $retval['exclude_groups'] = '1';
        }
        return $retval;
    }
}
add_filter( 'bp_after_has_profile_parse_args', 'ast_hide_profile_field_group' );

// Automatically link images to image files in bbPress forums
function ast_automatic_image_link( $content = '' ) {
   $content = preg_replace( '/(<img src=[\'"])([^\'"]+)([\'"][^>]*>)/i', '<a href="$2" rel="nofollow">$1$2$3</a>', $content );
   $content = preg_replace( '/(<a [^>]+>)<a [^>]+>/i', '$1', $content );
   $content = preg_replace( '/<\/a><\/a>/i', '</a>', $content );
   return $content;
}
add_filter( 'bbp_get_topic_content', 'ast_automatic_image_link', 1 );
add_filter( 'bbp_get_reply_content', 'ast_automatic_image_link', 1 );

//code to add presenter, commentator, exhibitor and adjudicator role
function ast_add_new_roles( $bbp_roles )
{
    /* Add a role called presenter */
    $bbp_roles['bbp_presenter'] = array(
        'name' => 'Presenter',
        'capabilities' => ast_custom_capabilities( 'bbp_presenter' )
        );
    /* Add a role called commentator */
    $bbp_roles['bbp_commentator'] = array(
        'name' => 'Commentator',
        'capabilities' => ast_custom_capabilities( 'bbp_commentator' )
        );
    /* Add a role called exhibitor */
    $bbp_roles['bbp_exhibitor'] = array(
        'name' => 'Exhibitor',
        'capabilities' => ast_custom_capabilities( 'bbp_exhibitor' )
        );
    /* Add a role called adjudicator */
    $bbp_roles['bbp_adjudicator'] = array(
        'name' => 'Adjudicator',
        'capabilities' => ast_custom_capabilities( 'bbp_adjudicator' )
        );
    return $bbp_roles;
}
add_filter( 'bbp_get_dynamic_roles', 'ast_add_new_roles', 1 );

function ast_add_role_caps_filter( $caps, $role )
{
    /* Only filter for roles we are interested in! */
    if( $role == 'bbp_presenter' )
        $caps = ast_custom_capabilities( $role );
    if( $role == 'bbp_commentator' )
        $caps = ast_custom_capabilities( $role );
    if( $role == 'bbp_exhibitor' )
        $caps = ast_custom_capabilities( $role );
    if( $role == 'bbp_adjudicator' )
        $caps = ast_custom_capabilities( $role );
    return $caps;
}
add_filter( 'bbp_get_caps_for_role', 'ast_add_role_caps_filter', 10, 2 );

function ast_custom_capabilities( $role )
{
    switch ( $role )
    {
        /* Capabilities for 'presenter' role */
        case 'bbp_presenter':
            return array(
                // Primary caps
                'spectate'              => true,
                'participate'           => true,
                'moderate'              => false,
                'throttle'              => false,
                'view_trash'            => false,
                // Forum caps
                'publish_forums'        => false,
                'edit_forums'           => false,
                'edit_others_forums'    => false,
                'delete_forums'         => false,
                'delete_others_forums'  => false,
                'read_private_forums'   => false,
                'read_hidden_forums'    => false,
                // Topic caps
                'publish_topics'        => true,
                'edit_topics'           => true,
                'edit_others_topics'    => false,
                'delete_topics'         => false,
                'delete_others_topics'  => false,
                'read_private_topics'   => false,
                // Reply caps
                'publish_replies'       => true,
                'edit_replies'          => true,
                'edit_others_replies'   => false,
                'delete_replies'        => false,
                'delete_others_replies' => false,
                'read_private_replies'  => false,
                // Topic tag caps
                'manage_topic_tags'     => false,
                'edit_topic_tags'       => false,
                'delete_topic_tags'     => false,
                'assign_topic_tags'     => true,
            );
        /* Capabilities for 'commentator' role */
        case 'bbp_commentator':
            return array(
                // Primary caps
                'spectate'              => true,
                'participate'           => true,
                'moderate'              => false,
                'throttle'              => false,
                'view_trash'            => false,
                // Forum caps
                'publish_forums'        => false,
                'edit_forums'           => false,
                'edit_others_forums'    => false,
                'delete_forums'         => false,
                'delete_others_forums'  => false,
                'read_private_forums'   => false,
                'read_hidden_forums'    => false,
                // Topic caps
                'publish_topics'        => false,
                'edit_topics'           => false,
                'edit_others_topics'    => false,
                'delete_topics'         => false,
                'delete_others_topics'  => false,
                'read_private_topics'   => false,
                // Reply caps
                'publish_replies'       => true,
                'edit_replies'          => true,
                'edit_others_replies'   => false,
                'delete_replies'        => false,
                'delete_others_replies' => false,
                'read_private_replies'  => false,
                // Topic tag caps
                'manage_topic_tags'     => false,
                'edit_topic_tags'       => false,
                'delete_topic_tags'     => false,
                'assign_topic_tags'     => true,
            );
        /* Capabilities for 'exhibitor' role */
        case 'bbp_exhibitor':
            return array(
                // Primary caps
                'spectate'              => true,
                'participate'           => true,
                'moderate'              => false,
                'throttle'              => false,
                'view_trash'            => false,
                // Forum caps
                'publish_forums'        => false,
                'edit_forums'           => false,
                'edit_others_forums'    => false,
                'delete_forums'         => false,
                'delete_others_forums'  => false,
                'read_private_forums'   => false,
                'read_hidden_forums'    => false,
                // Topic caps
                'publish_topics'        => true,
                'edit_topics'           => true,
                'edit_others_topics'    => false,
                'delete_topics'         => false,
                'delete_others_topics'  => false,
                'read_private_topics'   => false,
                // Reply caps
                'publish_replies'       => true,
                'edit_replies'          => true,
                'edit_others_replies'   => false,
                'delete_replies'        => false,
                'delete_others_replies' => false,
                'read_private_replies'  => false,
                // Topic tag caps
                'manage_topic_tags'     => false,
                'edit_topic_tags'       => false,
                'delete_topic_tags'     => false,
                'assign_topic_tags'     => true,
            );
        /* Capabilities for 'adjudicator' role */
        case 'bbp_adjudicatorp':
            return array(
                // Primary caps
                'spectate'              => true,
                'participate'           => true,
                'moderate'              => false,
                'throttle'              => false,
                'view_trash'            => false,
                // Forum caps
                'publish_forums'        => false,
                'edit_forums'           => false,
                'edit_others_forums'    => false,
                'delete_forums'         => false,
                'delete_others_forums'  => false,
                'read_private_forums'   => false,
                'read_hidden_forums'    => false,
                // Topic caps
                'publish_topics'        => false,
                'edit_topics'           => false,
                'edit_others_topics'    => false,
                'delete_topics'         => false,
                'delete_others_topics'  => false,
                'read_private_topics'   => false,
                // Reply caps
                'publish_replies'       => true,
                'edit_replies'          => true,
                'edit_others_replies'   => false,
                'delete_replies'        => false,
                'delete_others_replies' => false,
                'read_private_replies'  => false,
                // Topic tag caps
                'manage_topic_tags'     => false,
                'edit_topic_tags'       => false,
                'delete_topic_tags'     => false,
                'assign_topic_tags'     => true,
            );
            break;
        default :
            return $role;
    }
}

/*
Plugin Name: bbPress Topic Thumbnails
Plugin URI: http://shanegowland.com/wordpress/
Description: Gets the first image from each bbPress topic and displays it as a thumbnail.
Author: Shane Gowland
Version: 1.2
Author URI: http://shanegowland.com	
License: GPL2
*/

/*Display a warning if bbPress not installed*/
function bee_thumbs_admin_notice(){
	if (!is_plugin_active('bbpress/bbpress.php')) {
	 echo '<div class="updated"><p>bbPress has not been activated. Please disable <em>bbPress Topic Thumbnails</em>.</p></div>';
	}
}
add_action( 'admin_notices', 'bee_thumbs_admin_notice' );

/*Hooks into the loop-topic.php output to print image*/
function bee_insert_thumbnail() {

	if((!bee_catch_image() == '')){
	echo('<a href="' . bee_catch_image() . '"><img class="bbp-topic-thumbnail" width="100%" style="max-width: ' . get_option('medium_size_w') . 'px; max-height: ' . get_option('medium_size_h') . 'px; vertical-align:middle;" src="' . bee_catch_image() . '"/>' .'</a><br/>');
	}
}
add_action( 'bbp_theme_before_topic_title', 'bee_insert_thumbnail' );

/*Function that retrieves the first image associated with the topic*/
function bee_catch_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];
  return $first_img;
}
