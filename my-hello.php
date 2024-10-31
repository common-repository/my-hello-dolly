<?php
/**
 * Plugin Name: My Hello Dolly
 * Plugin URI: http://www.wphigh.com/portfolio/my-hello-dolly
 * Description:This plugin make you custom lyrics, quotes or any other words in the upper right of your admin screen on every page, like Hello Dolly plugin.
 * Version: 1.0.0
 * Author: wphigh
 * Author URI: http://www.wphigh.com
 * Text Domain: my_hello_dolly
 * License: GPLv2 or later
 */
 
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 or later, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Display my hello dolly.
 */
function my_hello_dolly() {
	/** These are the lyrics to Hello Dolly */
	$text = get_option( 'my_hello_dolly' );
	if ( empty( $text ) )
		return;

	// Here we split it into lines
	$texts = explode( "\n", $text );

	// And then randomly choose a line
	$chosen = wptexturize( trim( $texts[ mt_rand( 0, count( $texts ) - 1 ) ] ) );
	
	echo "<p id='my-hello'>$chosen</p>";
}

// Now we set that function up to execute when the admin_notices action is called
add_action( 'admin_notices', 'my_hello_dolly' );


/**
 * Set css
 */
function my_hello_dolly_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	$x = is_rtl() ? 'left' : 'right';

	echo "
	<style type='text/css'>
	#my-hello {
		float: $x;
		padding: 0 15px;		
		margin: 4px 10px 0;
		font-size: 11px;
		background-color: #000;
		color: white;
		border-radius: 5px;
		line-height: 200%;
	}
	</style>
	";
}

add_action( 'admin_head', 'my_hello_dolly_css', 2 );


/**
 * Add submenu into options menu
 */
function my_hello_dolly_menu() {
	add_options_page( 'My Hello Dolly Options', __( 'My Hello Dolly', 'my_hello_dolly' ), 'manage_options', 'my-hello-dolly.php', 'my_hello_dolly_page' );
}

add_action( 'admin_menu', 'my_hello_dolly_menu' );


/**
 * Menu page options 
 */
function my_hello_dolly_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Settings', 'my_hello_dolly' ); ?></h2>
		<form method="post" action="options.php">
			<?php
			 	settings_fields( 'my_hello_dolly_group' );
				do_settings_sections( 'my-hello-dolly-sections' );
				submit_button( __( 'Save Changes', 'my_hello_dolly' ), 'primary', 'submit', '' );
				echo '<input type="button" name="my_hello_dolly_clear" id="my-hello-dolly-clear" class="button button-delete" style="margin: 0 50px;" value="' . esc_attr( __( 'Clear', 'my_hello_dolly' ) ) . '">';
				wp_nonce_field( 'my_hello_dolly_clear', 'my_hello_dolly_clear_nonce', false );
			?>
		</form>
</div>
	<?php
}


/**
 * Register settings
 */
function register_my_hello_dolly_setting() {
 	// Add the section to reading settings so we can add our
	// fields to it
 	add_settings_section(
		'my_hello_dolly_section_id', 
		'',
		'__return_empty_string',
		'my-hello-dolly-sections'
	);
 	
 	// Add the field with the names and function to use for our new
 	// settings, put it in our new section
 	add_settings_field(
		'my_hello_dolly_field_id',
		__( 'Custom', 'my_hello_dolly' ),
		'my_hello_dolly_field_callback',
		'my-hello-dolly-sections',
		'my_hello_dolly_section_id'
	);
		
	// Register setting
	register_setting( 'my_hello_dolly_group', 'my_hello_dolly',  'strip_tags' ); 
}
 
add_action( 'admin_init', 'register_my_hello_dolly_setting' );


/**
 * Admin menu Field callback
 */
function my_hello_dolly_field_callback() {
	printf ( '<textarea class="large-text code" rows="25" name="%1$s">%2$s</textarea>',
		'my_hello_dolly',
		esc_textarea( get_option( 'my_hello_dolly' ) )
	);
	
	$des = __( 'Enter your favorite lyrics, quotes or any other words. These will display in the upper right of your admin screen on every page.<br>Each row write a sentence. Random show.', 'my_hello_dolly' );
	printf ( '<p class="description">%s</p>', $des );
}


/**
 * Admin menu print footer scripts
 */
function my_hello_dolly_menu_js(){
		global $hook_suffix;
		if ( 'settings_page_my-hello-dolly' != $hook_suffix )
			return;
			
		$confirm = __( 'Will clear all stored data.', 'my_hello_dolly' );
	?>
	<script type="text/javascript">
		jQuery(document).ready(function( $ ) {
			$( '#my-hello-dolly-clear' ).click(function() {
				if ( ! confirm( '<?php echo esc_attr( $confirm ); ?>' ) )
					return;
					
				$.post(
					ajaxurl,
					{
						action: 'my_hello_dolly_clear',
						nonce: $( '#my_hello_dolly_clear_nonce' ).val()
					},
					function( res ) {
						window.location.href=window.location.href;
					}
				);				
			});
		});
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'my_hello_dolly_menu_js' );


/**
 * Ajax to clear options
 */
function my_hello_dolly_clear() {
	if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['action'] ) || ! wp_verify_nonce( $_POST['nonce'], $_POST['action'] ) ) {
		die( 'Error!' );
	}
	
	delete_option( 'my_hello_dolly' );
	die();
} 
 
add_action( 'wp_ajax_my_hello_dolly_clear', 'my_hello_dolly_clear' );


/**
 * Add plugin action settings link
 */
function my_hello_dolly_action_links( $links ) {	
	$settings_link = '<a href="' . add_query_arg( 'page', 'my-hello-dolly.php', admin_url( 'admin.php' ) ) . '">'.__('Settings', 'my_hello_dolly').'</a>';
	array_unshift( $links, $settings_link ); 
	return $links; 
} 

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_hello_dolly_action_links' );


/**
* Loads the plugin's translated strings.
*/
function my_hello_dolly_textdomain() {
	load_plugin_textdomain( 'my_hello_dolly', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'my_hello_dolly_textdomain' );