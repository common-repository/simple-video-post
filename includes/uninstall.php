<?php

/**
 * Fired during plugin uninstallation.
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
 * SVP_Uninstall class.
 *
 * @since 1.0.0
 */
class SVP_Uninstall
{
    /**
     * Called when the plugin is uninstalled.
     *
     * @since 1.0.0
     */
    public static function uninstall()
    {
        if ( !defined( 'SVP_UNINSTALL_PLUGIN' ) ) {
            define( 'SVP_UNINSTALL_PLUGIN', true );
        }
        $general_settings = get_option( 'svp_general_settings' );
        if ( empty($general_settings['delete_plugin_data']) ) {
            return;
        }
        global  $wpdb ;
        // Delete all the custom post types
        $svp_post_types = array( 'svp_videos' );
        foreach ( $svp_post_types as $post_type ) {
            $items = get_posts( array(
                'post_type'   => $post_type,
                'post_status' => 'any',
                'numberposts' => -1,
                'fields'      => 'ids',
            ) );
            if ( count( $items ) ) {
                foreach ( $items as $item ) {
                    wp_delete_post( $item, true );
                }
            }
        }
        // Delete all the terms & taxonomies
        $svp_taxonomies = array( 'svp_categories', 'svp_tags' );
        foreach ( $svp_taxonomies as $taxonomy ) {
            $terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
            // Delete terms
            if ( count( $terms ) ) {
                foreach ( $terms as $term ) {
                    $wpdb->delete( $wpdb->term_taxonomy, array(
                        'term_taxonomy_id' => $term->term_taxonomy_id,
                    ) );
                    $wpdb->delete( $wpdb->terms, array(
                        'term_id' => $term->term_id,
                    ) );
                }
            }
            // Delete taxonomies
            $wpdb->delete( $wpdb->term_taxonomy, array(
                'taxonomy' => $taxonomy,
            ), array( '%s' ) );
        }
        // Delete the plugin pages
        if ( $svp_created_pages = get_option( 'svp_page_settings' ) ) {
            foreach ( $svp_created_pages as $page => $id ) {
                if ( $id > 0 ) {
                    wp_delete_post( $id, true );
                }
            }
        }
        // Delete all the plugin options
        $svp_settings = array(
            'svp_player_settings',
            'svp_brand_settings',
            'svp_videos_settings',
            'svp_categories_settings',
            'svp_video_settings',
            'svp_image_settings',
            'svp_socialshare_settings',
            'svp_permalink_settings',
            'svp_general_settings',
            'svp_page_settings',
            'svp_privacy_settings',
            'svp_version'
        );
        foreach ( $svp_settings as $settings ) {
            delete_option( $settings );
        }
        // Delete capabilities
        $roles = new SVP_Roles();
        $roles->remove_caps();
    }

}