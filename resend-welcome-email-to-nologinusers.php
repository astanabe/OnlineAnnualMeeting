<?php

/**
 * Use:
 * 1. Install in wp-content/mu-plugins (create directory if it does not exist)
 * 2. Log in as an administrator
 * 3. Visit [your-site]/wp-admin/?resend_welcomeemail_nologin=1
 */

add_action(
    'admin_init',
    function() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( empty( $_GET['resend_welcomeemail_nologin'] ) ) {
            return;
        }
        $allusers = get_users();
        foreach ( $allusers as $eachuser ) {
            $lastactivity = bp_get_user_last_activity( $eachuser->ID );
            if ( empty( $lastactivity ) ) {
                wp_new_user_notification( $eachuser->ID, null, 'both' );
            }
        }
    }
);
