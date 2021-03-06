<?php
if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( !class_exists( 'LearnDash_Settings_Section_Translations_Refresh' ) ) ) {
	class LearnDash_Settings_Section_Translations_Refresh extends LearnDash_Settings_Section {

		function __construct() {
		
			$this->settings_page_id					=	'learndash_lms_translations';
		
			// This is the 'option_name' key used in the wp_options table
			$this->setting_option_key 				= 	'submitdiv';
		
			// Section label/header
			$this->settings_section_label			=	__( 'Refresh Translations', 'learndash' );
		
			$this->metabox_context					=	'side';
			$this->metabox_priority					=	'high';
			
			parent::__construct(); 
			
			// We override the parent value set for $this->metabox_key because we want the div ID to match the details WordPress
			// value so it will be hidden.
			$this->metabox_key = 'submitdiv';
		}
		
		function show_meta_box() {
			?>
			<div id="submitpost" class="submitbox">

				<div id="major-publishing-actions">
					<div id="publishing-action">
						<span class="spinner"></span>
						<input type="hidden" name="translations" value="refresh" />

						<?php
							if ( Learndash_Translations::is_language_directory_writable() !== true ) {
								?><p class="error"><?php _e('The language directory is not writable: <code>'. Learndash_Translations::get_language_directory() .'</code>', 'learndash' ); ?></p><?php
							}

							$last_update_time = Learndash_Translations::get_last_update();

						?>
						<?php if ( !is_null( $last_update_time ) ) { ?>
							<p class="learndash-translations-last-update"><span class="label"><?php echo __('Updated', 'learndash' ) . '</span>: <span class="value">' . learndash_adjust_date_time_display( $last_update_time, 'M d, Y h:ia' ); ?></span></p>
						<?php }  ?>
						<?php //submit_button( esc_attr( __( 'Refresh', 'learndash' ) ), 'primary', 'submit', false );?>
						<a id="learndash-translation-refresh" class="button button-primary learndash-translations-refresh" href="<?php echo Learndash_Translations::get_action_url( 'refresh' );?> "><?php esc_html_e( 'Refresh', 'learndash' ); ?></a>
					</div>

					<div class="clear"></div>

				</div><!-- #major-publishing-actions -->

			</div><!-- #submitpost -->
			<?php
		}
	
		// This is a requires function
		function load_settings_fields() {
			
		}
	}
}
add_action( 'learndash_settings_sections_init', function() {
	LearnDash_Settings_Section_Translations_Refresh::add_section_instance();
} );
