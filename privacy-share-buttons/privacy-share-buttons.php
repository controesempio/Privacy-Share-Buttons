<?php
/*
Plugin Name: Privacy Share Buttons
Plugin URI: http://cavallette.noblogs.org
Description: Enables the well-known "Share this" buttons for different social networks, but with respect toward's your user privacy and data.
Version: 0.1
Author: lucha <lucha@paranoici.org>
Author URI: http://autistici.org
Tags: privacy, social, twitter, facebook, identica, googleplus
License: GPL2

Copyright (C) 2012 lucha <lucha@paranoici.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class ShareButton {
	function ShareButton() {
		$this->__construct(func_get_args());
	}

	function __construct() {
		// we need to load the textdomain now, because we are creating the default
		// values for the help text and we want them localized.
		load_plugin_textdomain('privacy-share-buttons', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		
		$this->url = plugins_url(basename(dirname(__FILE__)));
		$this->css = $this->url .'/css/socialshareprivacy.css';
		$this->js = $this->url .'/js/jquery.privacysharebuttons.min.js';
		$this->jquery_cookie = $this->url .'/js/jquery.cookie.min.js';
		$this->jquery_ui_css = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/base/jquery-ui.css';
		$this->images = $this->url .'/images/';
		$this->services = array(
		'twitter' => array(
			'name' => 'Twitter',
			'specific-settings' => true,
			'username' => 'reply_to',
			'jsconf' => array(
					'language' => 'en',
					'txt_info' => __('Click here to enable the button','privacy-share-buttons')
					),
			),
		'identica' => array(
			'name' => 'Identi.ca',
			'specific-settings' => true,
			'username' => 'reply_to',
			'jsconf' => array(
				'txt_info'	=> __('Click here to enable the button','privacy-share-buttons')
				
				)
			),
		'facebook' => array(
			'name' => 'Facebook',
			'jsconf' => array(
				'action' => 'recommend',
				'dummy_img' => $this->images . 'dummy_facebook_recommend.png',
				'txt_info'	=> __('Click here to enable the button','privacy-share-buttons')				
				)
			),
		'gplus' => array(
			'name' => 'Googleplus',
			'jsconf' => array(
				'txt_info'	=> __('Click here to enable the button','privacy-share-buttons')
				)
			)
/*		,'flattr' => array(
			'name' => 'Flattr',
			'specific-setings' => true,
			'username' => 'uid'
			)
			*/
		);
		$this->js_conf_default = array(
			'txt_help' => __('When you activate these buttons by clicking on them, some of your personal data will be transferred to third parties and can be stored by them. More information  <em> <a href="https://github.com/controesempio/Privacy-Share-Buttons">	here</a></em>.','privacy-share-buttons'),
			'settings_perma' => __('Permanently enable data transfer for:','privacy-share-buttons'),
		);
		$this->settings = new ShareButtonSettings($this->services);
		$this->settings = $this->settings->settings;
		$this->content_class = 'privacy_share_buttons_post';
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		$activate = false;
		foreach ($this->services as $service => $info){
			if ($this->settings[$service.'-status'])
				$activate = true;
		}
		if (!$activate)
			return;
				
		add_action('wp_enqueue_scripts',array(&$this, 'enqueue_styles'));
		add_action('wp_footer',array(&$this, 'enqueue_scripts'));
		
		add_shortcode('share_buttons',array(&$this,'short_code'));
		add_filter('the_content', array(&$this, 'filter_content'), 8);
		
	}
	
	function enqueue_scripts() {
		wp_register_script('jquery-cookie',$this->jquery_cookie,array('jquery'));
		wp_enqueue_script('social-share-privacy',$this->js,array('jquery','jquery-cookie','jquery-ui-core','jquery-ui-button'));
		wp_localize_script('social-share-privacy','socialshareprivacy_settings',$this->jsconf());
	}
	
	function enqueue_styles() {
		wp_enqueue_style('jquery-ui',$this->jquery_ui_css);
		wp_enqueue_style('privacy-share-buttons',$this->css);
	}
	
	function short_code($atts) {
		if ($this->settings['position'] == 'manual')
			return $this->generate_html();
		return '';
	}
	
	function filter_content($content){
		if (!$this->settings['index'] && is_home())
			return $content;
		if (!$this->settings['pages'] && is_page())
			return $content;
			
		$newcontent = $this->generate_html();
		$position = $this->settings['position'];

		if ($position == 'before'){		
			return $newcontent . $content;
		} else if ($position == 'after') {
			return $content . $newcontent;
		}

		return $content;
	}
	
	function generate_html() {
		global $post;
		return '<div class="'.$this->content_class.'_'.$post->ID.' social_share_privacy clearfix"></div>' . "\n" ;
	}
	
	function jsconf(){
		$conf = $this->js_conf_default;
		foreach ($this->services as $service => $info){
			if ($this->settings[$service.'-status']){
				$conf['services'][$service]['status'] = 'on';
				$conf['services'][$service]['display_name'] = $info['name'];
				$conf['services'][$service]['dummy_img'] = $this->images.'dummy_'.$service.'.png';
				if ($info['username'])
					$conf['services'][$service][$info['username']] = $this->settings[$service.'-username'];
				if ($info['jsconf'])
					$conf['services'][$service] = array_merge($conf['services'][$service],$info['jsconf']);
			} else {
			//	$conf['services'][$service]['status'] = 'off';
			}
		}
//		$conf['uri'] = get_permalink();
		return $conf;
	}
}

class ShareButtonSettings {
	function ShareButtonSettings() {
		$this->__construct(func_get_args());
	}

	function __construct($services) {
		$this->services = $services;
		$this->settings_section = 'share-button-settings';
		$this->display_section = $this->settings_section . '-display';
		$this->services_section = $this->settings_section . '-services';
		$this->settings = get_option($this->settings_section);
		add_action('admin_init', array( &$this, 'admin_init'));
		add_action('admin_menu', array( &$this, 'admin_menu'));

	}

	function admin_menu() {
		add_options_page(
			__('Privacy Share Buttons','privacy-share-buttons'),
			__('Privacy Share Buttons','privacy-share-buttons'),
			'manage_options',
			$this->settings_section,
			array( &$this, 'submenu_page')
			);	     
	}

	function admin_init() {
		register_setting($this->settings_section, $this->settings_section, array(&$this, 'settings_validate'));

		add_settings_section($this->settings_section,
			__('Enable Social Share Buttons','privacy-share-buttons'), array(&$this, 'settings_section'), $this->settings_section);

		foreach ($this->services as $service => $info){
			$this->add_default_fields($service,$info);
		}

		add_settings_section($this->display_section,
			__('Display settings','privacy-share-buttons'),
			array(&$this, 'display_section'), 
			$this->settings_section);

		add_settings_field($this->display_section."[index]",
			__('Display on the Index page','privacy-share-buttons'),
			array(&$this, 'checkbox'),
			$this->settings_section, $this->display_section,
			array('id' => 'index'));


		add_settings_field($this->display_section."[pages]",
			__('Display on Pages','privacy-share-buttons'),
			array(&$this, 'checkbox'),
			$this->settings_section, $this->display_section,
			array('id' => 'pages'));

		add_settings_field($this->display_section."[position]",
			__('Position','privacy-share-buttons'),
			array(&$this, 'position_field'),
			$this->settings_section, $this->display_section);

		foreach ($this->services as $service => $info){
			if ($info['specific-settings']){
				add_settings_section($this->services_section,
					__('Specific Service settings','privacy-share-buttons'),
					array(&$this, 'services_section'), 
					$this->settings_section);
				break;
			}
		}
		
		foreach ($this->services as $service => $info){
			if ($info['username']){
				$this->username_settings($service,$info);			
			}
		}
	}

	function username_settings($service,$info) {
		$userfield = $this->service_section."[$service-username]";
		add_settings_field($userfield, sprintf(__("%s username",'privacy-share-buttons'),$info['name']), array(&$this, 'username_field'), $this->settings_section, $this->services_section,$service.'-username');
	}
	
	function username_field($id) {
		$field = $this->settings_section."[$id]";
		$value = $this->settings[$id];
		echo "@<input type='text' name='{$field}' value='{$value}' size='20' />";
	}

	function add_default_fields($serviceid, $info) {
		$display_id = "{$serviceid}-status";

		$info['id'] = $display_id;
		$info['label'] = __('Enable','privacy-share-buttons');
		add_settings_field(  $this->settings_section."[$display_id]" , $info['name'], array(&$this, 'checkbox'), $this->settings_section, $this->settings_section, $info);

	} 

	function services_section() {
		_e("Settings specific to some Social Share services.",'privacy-share-buttons');
	}

	function display_section() {
		_e("By default buttons will be showed on single Posts",'privacy-share-buttons');
	}

	function settings_section() {
		echo '';
	}

	function checkbox($info) {
		$id = $info['id'];
		$field =  $this->settings_section."[$id]";
		$value = $this->settings[$id];
		$checked = checked( '1', $value, false);

		echo "<input type='checkbox' name='{$field}' value='1' $checked />";
		echo "<label for='{$field}'>";
		echo __('Enable','privacy-share-buttons');
		echo "</label>";

	}

	function position_field() {
		$field = "{$this->settings_section}[position]";
		$value = $this->settings['position'];
		echo "<div><select name='{$field}'>";

		$options = array(
			'after' => __("After the post",'privacy-share-buttons'),
			'before' => __("Before the post",'privacy-share-buttons'),
			'manual' => __("Manual (shortcode)",'privacy-share-buttons')
			);
		foreach ($options as $key => $label) {
			$selected = selected($value, $key, false);
			echo "<option value='{$key}' $selected>$label</option>";
		}
		echo "</select></div>";
		echo "<div> <p>"._e('If you choose "Manual (Shortcode)", you can use the shortcode <strong>[share_buttons]</strong> inside your articles','privacy-share-buttons')."</p></div>";
	}

	function settings_validate($input) {
		return $input;
	}

	function submenu_page() {
		?>
			<div class="wrap">
			<div id="icon-themes" class="icon32"><br></div>
			<h2>Privacy Share Button Settings</h2>
			<?php
		if ( !empty( $_POST['action'] ) && 'update' == $_POST['action'] ) {
			update_option( $this->settings_section, $_POST[$this->settings_section] );
			$this->settings = get_option( $this->settings_section );
			echo '<div id="message" class="updated fade"><p><strong>'.__('Settings saved.','privacy-share-buttons').'</strong></p></div>';
		}
		?>

		<form method="post" action="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>">
		<?php settings_fields( $this->settings_section ); ?>
		<?php do_settings_sections( $this->settings_section ); ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="Save Changes" />
		</p>
		</form>
		<?php
	}
}

new ShareButton();
