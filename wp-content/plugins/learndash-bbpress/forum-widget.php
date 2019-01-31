<?php

//smart widget displayed on single course page.
//displayed only if a course has associated forum.

class LearnDash_Associated_Forum_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_ldcourseprogress', 'description' => __('Course Forum', 'ld-bbpress'));
		$control_ops = array();//'width' => 400, 'height' => 350);
		parent::__construct('ldcourseforum', __('Course Forum', 'ld-bbpress'), $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		global $wpdb;
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );

		if(!is_singular())
		return;
		
		$course_id = learndash_get_course_id( get_the_ID() );

		if ( is_bbpress() ) {
			$forum_associated_courses = get_post_meta( get_the_ID(), '_ld_associated_courses', true );
		}

		$forums = new WP_Query( array(
			'post_type'           => bbp_get_forum_post_type(),
			'post_status'         => bbp_get_public_status_id(),
			'posts_per_page'      => get_option( '_bbp_forums_per_page', 50 ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'menu_order title',
			'order'               => 'ASC',
		) );

		// Bail if no posts
		if ( ! $forums->have_posts() ) {
			return;
		}

		$content_widget = "<div style='padding:10px;'>";
		
		$content_widget .= '<ul style="margin-bottom:0;">';
		
		$associated_courses = array();
		while ( $forums->have_posts() ) {
			$forums->the_post();

			$associated_courses = get_post_meta( $forums->post->ID, '_ld_associated_courses', true );

			if ( ! is_bbpress() ) {
				if ( empty( $associated_courses ) ) {
					continue;
				} elseif ( is_array( $associated_courses ) && ! in_array( $course_id, $associated_courses ) ) {
					continue;
				}
			} else {
				if ( empty( $forum_associated_courses ) || empty( $associated_courses ) ) {
					continue;
				} elseif ( is_array( $associated_courses ) && is_array( $forum_associated_courses ) ) {
					$intersect = array_intersect( $associated_courses, $forum_associated_courses );

					if ( empty( $intersect ) ) {
						continue;
					}
				}
			}

			if ( ! is_user_logged_in() ){
				if ( ! in_array( bbp_get_forum_visibility( $forums->post->ID ), array( 'hidden', 'private' ) ) ) {
					$content_widget .= "<li><a  href='#' onClick='return false;'>". $forums->post->post_title ."</a></li>";
				}
			} else {
				if ( ! in_array( bbp_get_forum_visibility( $forums->post->ID ), array( 'hidden', 'private' ) ) ){
					$content_widget .= "<li><a  href='".get_permalink( $forums->post->ID )."'>". $forums->post->post_title ."</a></li>";
				}
			}
		}

		wp_reset_query();

		$content_widget .= '</ul>';
		$content_widget .= "</div>";
		
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 
		
		echo $content_widget;
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = strip_tags($instance['title']);
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'ld-bbpress' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("LearnDash_Associated_Forum_Widget");'));