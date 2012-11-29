<?php
 
/*  Copyright 2012 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 *
 * @package WPEngine Clear URL Cache
 * @since 0.1
 */
class CFTP_Clear_URL_Cache extends CFTP_Clear_URL_Cache_Plugin {
	
	/**
	 * A version for cache busting, DB updates, etc.
	 *
	 * @var string
	 **/
	public $version;
	
	/**
	 * Let's go!
	 *
	 * @return void
	 **/
	public function __construct() {
		$this->setup( 'clear-url-cache', 'plugin' );

		$this->add_action( 'admin_menu' );
		$this->add_action( 'load-tools_page_cftp_clear_url', 'load_tools_page' );
		$this->add_action( 'admin_notices' );

		$this->version = 1;
		$this->errors = array();
	}
	
	// HOOKS
	// =====

	/**
	 * Hooks the WP admin_notices action to show a 
	 * warning if the plugin is active on a site which
	 * isn't hosted by WPEngine.
	 * 
	 * @return void
	 */
	function admin_notices() {
		if ( ! defined( 'WPE_PLUGIN_VERSION' ) )
			$this->render_admin_error( '<strong>WARNING:</strong> The “Clear URL Cache on WPEngine” plugin will ONLY work on sites hosted at WPEngine. You should deactivate it, no good will come of this.' );
	}
	
	/**
	 * Hooks the WP admin_menu action to add in our menu.
	 * 
	 * @return void 
	 */
	function admin_menu(){
		add_management_page( 'Clear URL Cache', 'Clear URL Cache', 'publish_posts', 'cftp_clear_url', array( $this, 'tools_page' ) );
	}
	
	/**
	 * Hooks the dynamic WP load action for the tools page.
	 * 
	 * @return void
	 */
	function load_tools_page(){
        global $wpe_all_domains;
		if ( ! isset( $_POST[ '_cftp_clear_url_nonce' ] ) )
			return;
		check_admin_referer( 'clear_url_cache', '_cftp_clear_url_nonce' );
		
		$url = esc_url( $_POST[ 'cached_url' ] );

		// A lot of the following code is cribbed from 
		// the WpeCommon::purge_varnish_cache method. A LOT.
		
		$post_parts = parse_url( $url );
		$post_uri   = $post_parts['path'];
		if ( ! empty( $post_parts['query'] ) )
			$post_uri .= "?" . $post_parts['query'];
		$path = $post_uri;
		// SW/CFTP: Ensure the path at least contains a slash
		if ( ! $path )
			$path = '/';

		// Hostname appears to be required without the protocol prefix, e.g.
		// no 'http://'.
		$hostname = $post_parts[ 'host' ];
		
        // Purge Varnish cache.
        if ( WPE_CLUSTER_TYPE == "pod" )
            $wpe_varnish_servers = array( "localhost" );
        else if ( ! isset( $wpe_varnish_servers ) ) {
            if ( WPE_CLUSTER_TYPE == "pod" )
                $lbmaster            = "localhost";
            else if ( ! defined( 'WPE_CLUSTER_ID' ) || ! WPE_CLUSTER_ID )
                $lbmaster            = "lbmaster";
            else if ( WPE_CLUSTER_ID >= 4 )
                $lbmaster            = "localhost"; // so the current user sees the purge
            else
                $lbmaster            = "lbmaster-" . WPE_CLUSTER_ID;
            $wpe_varnish_servers = array( $lbmaster );
        }

        // Debugging
        if ( false ) {
            $msg_key = rand();
            $msg     = "Varnishes # $msg_key:\n" . "\nHostname:\n" . var_export( $hostname, true ) . "\nPath:\n" . var_export( $path, true );
			var_dump( $wpe_varnish_servers );
            var_dump( $hostname );
            var_dump( $path );
        }
		// SW/CFTP: Assume we're not using EC…
		foreach ( $wpe_varnish_servers as $varnish ) {
			error_log( "CFTP: PURGE, $varnish, 9002, $hostname, $path, array( ), 0" );
			WpeCommon::http_request_async( "PURGE", $varnish, 9002, $hostname, $path, array( ), 0 );
		}
		
		$this->set_admin_notice( "Issued a cache clearance for <var>$url</var>" );
		wp_redirect( admin_url( '/tools.php?page=cftp_clear_url' ) );
		exit;
	}
	
	// CALLBACKS
	// =========
	
	/**
	 * Callback function, providing HTML for the admin page]
	 * under the tools menu.
	 * 
	 * @return void 
	 */
	function tools_page() {
		$vars = array();
		$this->render_admin( 'tools-page.php', $vars );
	}
	
}

$GLOBALS[ 'cftp_clear_url_cache' ] = new CFTP_Clear_URL_Cache;

