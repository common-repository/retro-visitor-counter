<?php
/*
Plugin Name: Retro Visitor Counter
Description: Super-easy visitor counter. Just add the widget to your sidebar and enjoy.
Version: 1.0.0
Author: Moki-Moki Ios
Author URI: http://mokimoki.net/
Text Domain: retro-visitor-counter
License: GPL3
*/

/*
Copyright (C) 2017 Moki-Moki Ios http://mokimoki.net/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Retro Visitor Counter
 * Just add the widget to your sidebar and enjoy.
 *
 * @version 1.0.0
 */

if (!defined('ABSPATH')) return;

add_action('init', array(RetroVisitorCounter::get_instance(), 'initialize'));
add_action('admin_notices', array(RetroVisitorCounter::get_instance(), 'plugin_activation_notice'));
add_action('widgets_init', 'retro_visitor_counter_widget_register');
register_activation_hook(__FILE__, array(RetroVisitorCounter::get_instance(), 'setup_plugin_on_activation')); 

/**
 * Main class of the plugin.
 */
class RetroVisitorCounter {
	
	const PLUGIN_NAME = "Retro Visitor Counter";
	const VERSION = '1.0.0';
	const TEXT_DOMAIN = 'retro-visitor-counter';
	
	private static $instance;
	
	private function __construct() {}
		
	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function initialize() {
		load_plugin_textdomain(self::TEXT_DOMAIN, FALSE, basename(dirname( __FILE__ )) . '/languages');		
	}
	
	public function setup_plugin_on_activation() {		
		set_transient('retro_visitor_counter_activation_notice', TRUE, 5);
		add_action('admin_notices', array($this, 'plugin_activation_notice'));
	}	
	
	public function plugin_activation_notice() {
		if (get_transient('retro_visitor_counter_activation_notice')) {
			echo '<div class="notice updated"><p><strong>'.__('Retro visitor counter activated. Just <a href="'.admin_url('widgets.php').'">add the widget to your sidebar</a> and enjoy.', self::TEXT_DOMAIN).'</strong></p></div>';	
		}		
	}
}

/**
 * Widget 
 */
class RetroVisitorCounterWidget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'retro_visitor_counter_widget',
			__( 'Retro Visitor Counter', RetroVisitorCounter::TEXT_DOMAIN),
			array()
		);
	}

	public function form($instance) {	
		$defaults = array(
			'title'    => 'Visitors',
			'color_mode'   => ''
		);
		
		extract(wp_parse_args((array)$instance, $defaults)); ?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Widget Title', RetroVisitorCounter::TEXT_DOMAIN); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title') ); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('color_mode'); ?>"><?php _e('Color Mode', RetroVisitorCounter::TEXT_DOMAIN); ?></label>
			<select name="<?php echo $this->get_field_name('color_mode'); ?>" id="<?php echo $this->get_field_id('color_mode'); ?>" class="widefat">
			<?php
				$options = array(
					'' => __('Default', RetroVisitorCounter::TEXT_DOMAIN),
					'dark' => __('Dark', RetroVisitorCounter::TEXT_DOMAIN),
					'light' => __('Light', RetroVisitorCounter::TEXT_DOMAIN)
				);

				foreach ($options as $key => $name) {
					echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" '. selected($color_mode, $key, FALSE) . '>'. $name . '</option>';
				} 
			?>
			</select>
		</p>		
	<?php	
	}

	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = isset($new_instance['title']) ? wp_strip_all_tags( $new_instance['title'] ) : '';		
		$instance['color_mode'] = isset($new_instance['color_mode']) ? wp_strip_all_tags($new_instance['color_mode']) : '';
		return $instance;		
	}

	public function widget($args, $instance) {
		extract($args);
		
		$title = isset($instance['title']) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$color_mode = isset($instance['color_mode']) ? $instance['color_mode'] : '';
	
		echo $before_widget;
		
		echo '<div class="widget-text wp_widget_plugin_box">';

		if ($title) {
			echo $before_title . $title . $after_title;
		}
		
		$parameters = '?ver=' . hash_file('sha1', __FILE__);
		if ($color_mode == 'light') {
			$parameters .= '&mode=light';
		}
		
		echo '<img src="https://mokimoki.net/counter/counter.php'.$parameters.'" alt="Retro WordPress Visitor Counter"/>';
		echo '</div>';

		echo $after_widget;
	}
}

function retro_visitor_counter_widget_register() {
	register_widget('RetroVisitorCounterWidget');
}