<?php

/**
 * Fired during plugin deactivation.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */
// Exit if accessed directly
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * SVP_Deactivator class.
 *
 * @since 1.0.0
 */
class SVP_Deactivator
{
    /**
     * Called when the plugin is deactivated.
     *
     * @since 1.0.0
     */
    public static function deactivate()
    {
        delete_option( 'rewrite_rules' );
    }

}