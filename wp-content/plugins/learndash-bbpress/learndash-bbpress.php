<?php
/**
 * @package LearnDash & BBPress
 * @version 2.0.2
 */
/*
/*
Plugin Name: LearnDash & bbPress Integration
Plugin URI: http://www.learndash.com
Description: LearnDash integration with the bbPress plugin that allows to create private forums for user's enrolled to a course.
Version: 2.0.2
Author: LearnDash
Author URI: http://www.learndash.com
Text Domain: ld-bbpress
Domain Path: languages
*/

add_action('admin_init','wdm_activation_dependency_check');
function wdm_activation_dependency_check(){

	//check if learndash is active
	$is_bbpress_active = is_plugin_active('bbpress/bbpress.php');

	//check if learndash is active
	$is_learndash_active = is_plugin_active('sfwd-lms/sfwd_lms.php');


	if (!$is_bbpress_active || !$is_learndash_active ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		unset( $_GET['activate'] );
		add_action('admin_notices', 'wdm_activation_dependency_check_notices');
	}
	else{
		//new Learndash_BBPress();
	}
}

function wdm_activation_dependency_check_notices() {
	echo "<div class='notice notice-error'><p>" . __( 'Please activate BBPress & Learndash plugins before activation LearnDash - BBPress integration.', 'ld-bbpress' ) . "</p></div>";
}

if(!class_exists('Learndash_BBPress')) {

class Learndash_BBPress{
	
	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
       	add_action( 'add_meta_boxes', array($this, 'ld_display_course_selector'));
		add_action( 'save_post_forum', array($this, 'ld_save_associated_course'));
		$this->ld_include_plugin_code();
   }

   /**
	 * Load text domain used for translation
	 *
	 * This function loads mo and po files used to translate text strings used throughout the 
	 * plugin.
	 *
	 * @since 1.3.0
	 */
	public function load_textdomain() {

		// Set filter for plugin language directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'ld_bbpress_languages_directory', $lang_dir );

		// Load plugin translation file
		load_plugin_textdomain( 'ld-bbpress', false, $lang_dir );
	}
	
	public function ld_include_plugin_code(){
		require_once( plugin_dir_path( __FILE__ ) . 'functions.php');
		require_once( plugin_dir_path( __FILE__ ) . 'forum-widget.php');
	}
	
	public function ld_display_course_selector() {
    	add_meta_box( 'ld_course_selector', __( 'LearnDash bbPress Settings', 'ld-bbpress' ), array($this, 'ld_display_course_selector_callback'), 'forum', 'advanced', 'high' );
	}
	
	public function ld_display_course_selector_callback(){
		
		$courses = $this->ld_get_course_list();
		$associated_courses     = get_post_meta( get_the_ID(), '_ld_associated_courses', true );
		$limit_post_access      = get_post_meta( get_the_ID(), '_ld_post_limit_access', true );
		$message_read_only      = get_post_meta( get_the_ID(), '_ld_message_read_only', true ) ?: __( 'You cannot create new topics.', 'ld-bbpress' );
		$allow_forum_view       = get_post_meta( get_the_ID(), '_ld_allow_forum_view', true );
		$message_without_access = get_post_meta( get_the_ID(), '_ld_message_without_access', true ) ?: __( 'This forum is restricted to members of the associated course(s).', 'ld-bbpress' );
		$selected = null;
		?>
		
			<script>
				jQuery( document ).ready( function( $ ){
					$( '#ld_clearcourse' ).click( function( e ) {
						e.preventDefault();
						$( "#ld_course_selector_dd option:selected" ).each( function() {
								$( this ).removeAttr( 'selected' ); //or whatever else
						} );
					} );
				});
			</script>
		
			<table class="form-table">
				<tbody>
				<tr>
				<td>
					<label for="ld_course_selector_dd"><strong><?php _e( 'Associated Course(s)', 'ld-bbpress' ); ?>: </strong></label>
					<br>
					<select name='ld_course_selector_dd[]' size="4" id='ld_course_selector_dd' multiple="multiple">
						<optgroup label="<?php _e( 'Select Courses', 'ld-bbpress' ); ?>">
						<?php if(is_array($courses)){
						foreach( $courses as $course ){
							$selected = null;
							if(is_array($associated_courses) && in_array($course->ID, $associated_courses)){
								$selected = "selected";
							} ?>
							<option value="<?php echo $course->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title($course->ID); ?></option>
						<?php } } ?>
						</optgroup>
					</select>
					<br>
					<a href="" id="ld_clearcourse" class="button" style="margin-top: 10px;"><?php _e( 'Clear All', 'ld-bbpress' ); ?></a>
				</td>
				</tr>
				<tr>
					<td>
						<label for="ld_post_limit_access"><strong><?php _e( 'Post Limit Access', 'ld-bbpress' ); ?>: </strong></label>
						<select name="ld_post_limit_access" id="ld_post_limit_access">
							<option value="all" <?php selected( 'all', $limit_post_access, true ); ?>><?php _e( 'All', 'ld-bbpress' ); ?></option>
							<option value="any" <?php selected( 'any', $limit_post_access, true ); ?>><?php _e( 'Any', 'ld-bbpress' ); ?></option>
						</select>
						<p class="desc"><?php _e( 'If you select ALL, then users must have access to all of the associated courses in order to post.', 'ld-bbpress' ); ?></p>
						<p class="desc"><?php _e( 'If you select ANY, then users only need to have access to any one of the selected courses in order to post.', 'ld-bbpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<td>
						<label for="ld_message_without_access"><strong><?php _e( 'Message shown to users without access', 'ld-bbpress' ); ?>: </strong></label>
						<br>
						<textarea cols="100" rows="5" name="ld_message_without_access"><?php echo esc_attr( $message_without_access ); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="ld_allow_forum_view"><strong><?php _e( 'Forum View', 'ld-bbpress' ); ?>: </strong></label>
						<br>
						<input type="hidden" name="ld_allow_forum_view" value="0">
						<input type="checkbox" name="ld_allow_forum_view" value="1" <?php checked( '1', $allow_forum_view, true ); ?>>&nbsp;<?php _e( 'Check this box to allow non-enrolled users to view forum threads and topics (they will not be able to post replies).', 'ld-bbpress' ); ?>
					</td>
				</tr>
				</tbody>
			</table>
		 <?php   
	}
	
	public function ld_save_associated_course($post_id){
		$old_associated_courses = get_post_meta($post_id, '_ld_associated_courses', true);
		
		if(isset($_POST['ld_course_selector_dd']) && !empty($_POST['ld_course_selector_dd'])){
			update_post_meta($post_id, '_ld_associated_courses', array_filter($_POST['ld_course_selector_dd']));
		} else{
			delete_post_meta($post_id, '_ld_associated_courses');
		}
		
		$new_associated_courses = get_post_meta($post_id, '_ld_associated_courses', true);
		
		if(!empty($old_associated_courses)){
			foreach($old_associated_courses as $old_course){
				delete_post_meta($old_course, '_ld_associated_forum_'.$post_id);
			}
		}
		
		if(!empty($new_associated_courses)){
			foreach($new_associated_courses as $new_course){
				update_post_meta($new_course, '_ld_associated_forum_'.$post_id, $post_id);
			}
		}

		// Save post limit access option
		update_post_meta( $post_id, '_ld_post_limit_access', sanitize_text_field( $_POST['ld_post_limit_access'] ) );

		update_post_meta( $post_id, '_ld_message_without_access', wp_kses_post( $_POST['ld_message_without_access'] ) );

		update_post_meta( $post_id, '_ld_allow_forum_view', sanitize_text_field( $_POST['ld_allow_forum_view'] ) );

		update_post_meta( $post_id, '_ld_message_read_only', wp_kses_post( $_POST['ld_message_read_only'] ) );
	}
	
	public function ld_get_course_list(){
		$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'sfwd-courses',
		'post_status'      => 'publish');
		
		$courses = get_posts( $args );
		return $courses;
	}
}

}
new Learndash_BBPress();
