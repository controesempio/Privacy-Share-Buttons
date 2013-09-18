<?php
/*
  Plugin Name: Privacy Share Buttons
  Plugin URI: https://github.com/controesempio/Privacy-Share-Buttons
  Description: Enables the well-known "Share this" buttons for different social networks, but with respect toward's your user privacy and data.
  Version: 0.3
  Author: lucha <lucha@paranoici.org>
  Author URI: https://github.com/controesempio/
  Tags: privacy, social, twitter, facebook, googleplus
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
	$this->url = plugins_url(basename(dirname(__FILE__)));
	$this->css = '/css/socialshareprivacy.css';
	$this->js = $this->url .'/js/jquery.socialshareprivacy.min.js';
	$this->jquery_cookie = $this->url .'/js/jquery.cookie.min.js';

	$this->global_options = array(
	    'path_prefix' => $this->url . '/',
	    'css_path' => $this->css,
	    );
	$this->_all_services = array('buffer',
	    'delicious','disqus','mail',
	    'facebook', 'fbshare', 'flattr',
	    'gplus', 'hackernews', 'pinterest',
	    'linkedin', 'reddit', 'stumbleupon',
	    'tumblr','twitter','xing');
	$this->_services_options = array(
	    'buffer' => array('username' => 'via'),
	    'delicious' => array('username' => 'shortname'),
	    'flattr' => array('username' => 'uid'),
	    'twitter' => array('username' => 'via'),
	    );
	$this->settings = new ShareButtonSettings($this->_all_services, $this->_services_options);
	$this->settings = $this->settings->settings;
	$this->content_class = 'privacy_share_buttons_post';
	add_action( 'init', array( &$this, 'init' ) );
	}

    function init() {
	$activate = false;
	foreach ($this->_all_services as $service)
	    if (array_key_exists($service.'-status',$this->settings) and $this->settings[$service.'-status']){
		$activate = true;
		break;
		}
	if (!$activate)
	    return;

	add_action('wp_enqueue_scripts',array(&$this, 'enqueue_scripts'));
		
	add_shortcode('share_buttons',array(&$this,'short_code'));
	add_filter('the_content', array(&$this, 'filter_content'), 8);		
	}
	
    function enqueue_scripts() {
	wp_register_script('jquery-cookie',$this->jquery_cookie,array('jquery'),false,true);
	wp_enqueue_script('social-share-privacy',$this->js,array('jquery','jquery-cookie'),false,true);
	wp_enqueue_script('ssp', $this->url . '/js/ssp-onload.js',null,false,true);
	}
	
    function short_code($atts) {
	if ($this->settings['position'] == 'manual')
	    return $this->generate_html();
	return '';
	}
	
    function filter_content($content){
	if (array_key_exists('index',$this->settings) and !$this->settings['index'] and is_home())
	    return $content;
	if (array_key_exists('pages',$this->settings) and !$this->settings['pages'] and is_page())
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
	return "<div class='share' data-options='" . $this->jsconf() . "'></div>";
	}
	
    function jsconf(){
	$conf = $this->global_options;
	foreach ($this->_all_services as $service)
	    if (array_key_exists($service.'-status',$this->settings) and ($this->settings[$service.'-status'] == 1)){
		$conf['services'][$service]['status'] = true;
		if (array_key_exists($service,$this->_services_options))
		    foreach ($this->_services_options[$service] as $option => $name)
			$conf['services'][$service][$name] = $this->settings[$service.'-'.$option];
		}
	    else
		$conf['services'][$service]['status'] = false;
	  
	return json_encode($conf);
	}
    }

class ShareButtonSettings {
    function ShareButtonSettings() {
	$this->__construct(func_get_args());
	}

    function __construct($services, $options) {
	$this->services = $services;
	$this->services_options = $options;
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

	foreach ($this->services as $service){
	    $this->add_default_fields($service);
	    
	    if (array_key_exists($service,$this->services_options))
		if (array_key_exists('username',$this->services_options[$service]))
		    $this->username_settings($service);
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
	}

    function add_default_fields($serviceid) {
	$display_id = "{$serviceid}-status";
	
	$info['id'] = $display_id;
	$info['label'] = __('Enable','privacy-share-buttons');
	$info['name'] = $serviceid;
	add_settings_field($this->settings_section."[$display_id]" , $info['name'], array(&$this, 'checkbox'), $this->settings_section, $this->settings_section, $info);

	} 
    
    function username_settings($service) {
	$userfield = $this->services_section."[$service-username]";
	add_settings_field($userfield, sprintf(__("%s username",'privacy-share-buttons'),$service),
	    array(&$this, 'username_field'), $this->settings_section, $this->settings_section,$service.'-username');
	}
	
    function username_field($id) {
	$field = $this->settings_section."[$id]";
	$value = '';
	if (array_key_exists($id,$this->settings))
	    $value = $this->settings[$id];
	echo "@<input type='text' name='{$field}' value='{$value}' size='20' />";
	}

    function display_section() {
	_e("By default buttons will be showed on single Posts",'privacy-share-buttons');
	}

    function settings_section() {
	_e("Do not enable too many of them. They will look ugly all together.");
	}

    function checkbox($info) {
	$id = $info['id'];
	$field =  $this->settings_section."[$id]";
	$value = '0';
	if (array_key_exists($id, $this->settings))
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
