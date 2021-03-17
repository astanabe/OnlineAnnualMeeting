<?php

/**
 * Use:
 * 1. Install in wp-content/mu-plugins (create directory if it does not exist)
 * 2. Log in as an administrator
 * 3. Visit [your-site]/wp-admin/?change_sendmail_all=1
 */

add_action(
    'admin_init',
    function() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( empty( $_GET['change_sendmail_all'] ) ) {
            return;
        }
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
            'notification_membership_request_completed' => 'yes'
        );
        $allusers = get_users();
        foreach ( $allusers as $eachuser ) {
            foreach( $settings as $setting => $preference ) {
                bp_update_user_meta( $eachuser->ID, $setting, $preference );
            }
        }
    }
);
