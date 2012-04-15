/*
 *	jQuery Privacy Share Buttons plugin
 *
 *	ideas, original code and images taken from: 
 *  http://www.heise.de/extras/socialshareprivacy/
 * 	Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * 						   Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *  
 *  Copyright (c) 2012 lucha <lucha@paranoici.org>
 *  
 *  released under the terms of either the MIT License or the GNU General Public License (GPL) Version 2
 */
;(function($) {

var SocialButton = function(elements, options){
	
	this.elements = elements;
	this.options = $.extend(true, {}, this.defaults, options);
	
	if (!this.is_on())
		return;
		
	this.append_css();
	this.attach();
	
}; SocialButton.prototype = {
	// defalt values for options
	defaults : {
		'info_link' 		: 'http://cavallette.noblogs.org/?p=7641',
		'txt_help'  		: 'When you activate these buttons by clicking on them, some of your personal data will be transferred to third parties and can be stored by them. For more information click on the <em> i </em>',
		'settings_perma'	: 'Permanently enable data transfer for:',
		'css_path'			: '',
		'uri'				: '',
		'cookie_options'    : {
			'path'			: '/',
			'expires'		: 365
		},
		'services'			: {
			'facebook' : {
				'display_name'      : 'Facebook',
				'status'			: 'off',
				'perma'     		: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send your recommendation to Facebook. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_off'			: 'not connected with Facebook',
				'txt_on'			: 'connected with Facebook',
				'dummy_img'			: '',
				'action'			: 'recommend',
				'iframe_src'			: function (options){ 
					// return '<iframe src="//www.facebook.com/plugins/like.php?href&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:auto; width:450px; height:21px;" allowTransparency="true"></iframe>';
					return '<div id="fb-root"></div>'
					+'<script>(function(d, s, id) {'
					+' var js, fjs = d.getElementsByTagName(s)[0];'
					+' if (d.getElementById(id)) return;'
					+' js = d.createElement(s); js.id = id;'
					+' js.src = "//connect.facebook.net/it_IT/all.js#xfbml=1";'
					+'  fjs.parentNode.insertBefore(js, fjs);'
					+'}(document, "script", "facebook-jssdk"));</script>'
					+'<div class="fb-like" data-send="false" data-layout="button_count" data-width="400" data-show-faces="false" data-action="recommend"></div>';
					// return '<iframe src="//www.facebook.com/plugins/like.php?href='
					// 		+ options.uri 
					// 		+ '&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false'
					// 		+ '&amp;action=' + options.services.facebook.action 
					// 		+ '&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" '
					// 		+ 'style="border:none; overflow:hidden; width:130px; height:25px;" allowTransparency="true"></iframe>';
					}
			}, 
			'twitter' : {
				'display_name'      : 'Twitter',
				'status'			: 'off',
				'perma'      		: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send your recommendation to Twitter. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_off'			: 'not connected with Twitter',
				'txt_on'			: 'connected with Twitter',
				'dummy_img'			: '',
				'reply_to'			: '',
				'text'				:  encodeURIComponent(document.title),
				'iframe_src'		: function(options){
					var reply_to = (options.services.twitter.reply_to != '') ? '&amp;via=' + options.services.twitter.reply_to : '';
					
					return '<iframe allowtransparency="true" frameborder="0" scrolling="no" '
					     + 'src="http://platform.twitter.com/widgets/tweet_button.html?'
						 + 'url=' + options.uri + '&amp;counturl=' + options.uri 
						 + '&amp;text=' + options.services.twitter.text
						 + reply_to
						 + '&amp;count=horizontal'
						 + '&amp;lang=' + options.services.twitter.language 
						 + '" style="width:100px; height:25px;"></iframe>';
					}
			},
			'identica' : {
				'display_name'		: 'Identi.ca',
				'status'			: 'off',
				'perma'				: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send your recommendation to Identi.ca. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_off'			: 'not connected with Identi.ca',
				'txt_on'			: 'connected with Identi.ca',
				'dummy_img'			: '',
				'text'				: encodeURIComponent(document.title),
				'identica_lib' 		: '',
				'iframe_src'		: function(options){
					return '<iframe scrolling="no" frameborder="0" src="'
					 + options.services.identica.identica_lib 
					 + '?noscript&style2'
					 + '&amp;title=' + options.services.identica.text
					 + '" allowtransparency="true" style="width:130px; height:25px; position: absolute;"></iframe>';
					}
			},
			'gplus' : {
				'display_name'      : 'Google+',
				'status'			: 'off',
				'perma'      		: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send your recommendation to Google+. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_off'			: 'not connected with Google+',
				'txt_on'			: 'connected with Google+',
				'dummy_img'			: '',
				'language'			: 'en',
				'iframe_src'		: function(options){
					var gplusdiv = $('<div class="g-plusone" data-size="medium"></div>');
					var gplusjs = '<script type="text/javascript">window.___gcfg = {lang: "' + options.services.gplus.language
								  + '"}; (function() { var po = document.createElement("script"); po.type = "text/javascript";'
								  + 'po.async = true; po.src = "https://apis.google.com/js/plusone.js";'
								  + ' var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s); })(); </script>';
					return gplusdiv.append(gplusjs);
				}
			}
/*			'flattr' : {
				'display_name'		: 'Flattr',
				'status'			: 'off',
				'perma'				: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send yout recommendation to Flattr. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_flattr_off'	: 'not connected with Flattr',
				'txt_flattr_on'		: 'connected with Flattr',
				'dummy_img'			: '',
				'iframe_src'			: function(){return '';}
			},
			'xing' : {
				'display_name'		: 'Xing',
				'status'			: 'off',
				'perma'				: 'on',
				'txt_info'			: '2 click for more privacy: only if you click here, the button will activate and you will be able to send your recommendation to Xing. When enabled, data will be transferred to third parties - see <em> i </em>.',
				'txt_gplus_off'		: 'not connected with Xing',
				'txt_plus_on'		: 'connected with Xing',
				'dummy_img'			: '',
				'xing_lib'			: '',
				'iframe_src'			: function(){return '';}
			}
*/
		}
	},
	
	// let's check if at least one service is active
	is_on : function(){
		var is_on = false;
		for (var name in this.options.services){
			var serv = this.options.services[name];
			if (serv.status == 'on'){
				is_on = true;
				break;
			}
		}
		return is_on;
	},
	
	// let's check if we have to show the settings area
	perma_is_on : function(){
		var perma_is_on = false;
		for (var name in this.options.services){
			var serv = this.options.services[name];
			if (serv.status == 'on' && serv.perma == 'on'){
				perma_is_on = true;
				break;
			}
		}
		// IE7 has problems with cookies and JSON, so we don't show them the settings area
		return perma_is_on && (!$.browser.msie || ($.browser.msie && ($.browser.version > 7.0)));
	},
	
	// adds CSS to head if we have to do so
	append_css : function(){
		// insert stylesheet into document and prepend target element
		if (this.options.css_path.length > 0) {
			// IE fix (needed for IE < 9 - but this is done for all IE versions)
			if (document.createStyleSheet) {
				document.createStyleSheet(options.css_path);
			} else {
				$('head').append('<link rel="stylesheet" type="text/css" href="' + options.css_path + '" />');
			}
		}
	},
	
	dummy_image : function(service){
		return $('<img/>', {
			src : service.dummy_img,
			alt : service.display_name + ' Dummy Image',
			"class" : 'dummy_img'
			});
	},
	
	switch_button : function(service, element){
		var c_switch = $('.switch',$(element));
		var dummy_div = $('div.dummy_btn',$(element));
		
		if (c_switch.hasClass('off')){
			$(element).addClass('info_off');
			c_switch.addClass('on').removeClass('off').html(service.txt_on);
			dummy_div.html(service.iframe_src(this.options));
		} else {
			$(element).removeClass('info_off');
			c_switch.addClass('off').removeClass('on').html(service.txt_off);
			dummy_div.html(this.dummy_image(service));
		}
	},
	
	attach : function(){
		var self = this;
		this.elements.each( function(){
			// contex will hold all the button, whether active or not, and the info and setting area
			var context = $('<ul class="social_share_privacy_area"></ul>').appendTo(this);
			
			// let's add the single buttons
			for (var name in self.options.services){
				var serv = self.options.services[name];
				if (serv.status != 'on')
					continue;

				var iframe = serv.iframe_src(self.options);

				var container = $('<li class="help_info '+name +'"><span class="info">' + serv.txt_info + '</span></li>').appendTo(context);

				$('<span class="switch off">' + serv.txt_off + '</span>').appendTo(container);
				var dummy_div = $('<div class="dummy_btn"></div>').appendTo(container);
				dummy_div.append(self.dummy_image(serv));

				container.click( {serv:serv, element:container}, function(event){
					self.switch_button(event.data.serv,event.data.element);
				});					
			}
				
			// now it's time for the info area
			var container = $('<li class="settings_info">'
			+'<div class="settings_info_menu off perma_option_off">'
			+ '<a href="'+ self.options.info_link +'">'
			+ '<span class="help_info icon">'
			+ '<span class="info">' + self.options.txt_help + '</span>'
			+ '</span></a>'
			+ '</div></li>'			
			).appendTo(context);
			
			// show the overlays of the buttons and info area
			$('.help_info').each(function(){
				$(this).mouseenter(function() {
					if(!$(this).hasClass('info_off'))
					 	$('.info',this).show();
					});
				$(this).mouseleave(function() { $('.info',this).hide();});
			});

			// and finally it's time for the settings area (i.e. permanent activation)
			if (self.perma_is_on()){
				var info_menu = container.find('.settings_info_menu').removeClass('perma_option_off');
				$('<span class="settings">Settings</span><form><fieldset><legend>' + self.options.settings_perma + '</legend></fieldset></form>').appendTo(info_menu);
				
				for (var name in self.options.services){
					var serv = self.options.services[name];
					// first let's check if we have the perma option activated
					if (!(serv.status == 'on' && serv.perma=='on'))
						continue;
					
					// let's get the cookie and check if we have to activate the button	
					var checked = ($.cookie('privacyShareButtons_'+name) == 'perma_on') ? 'checked="checked"' : '';
					info_menu.find('form fieldset').append(
						'<input type="checkbox" name="perma_status_'+name+'" id="perma_status_' + name + '" '
						+ checked +' />'
						+ '<label for="perma_status_'+name+'">'
						+ serv.display_name + '</label>');
						
					// if it's need, let's click the button so it gets activated
					if (checked != '')
						$('li.'+name+' span.switch',context).click();
				}
				
	            info_menu.find('span.settings').css('cursor', 'pointer');
				// show the overlay of the setting area
				info_menu.find('span.settings').mouseenter(function(){ 
					info_menu.removeClass('off').addClass('on');
				});
				container.mouseleave(function(){ 
					info_menu.removeClass('on').addClass('off');
				});
	            
				// let's handle changes in the settings
				$(info_menu.find('fieldset input')).click( function (event) {
	                    var click = event.target.id;
	                    var service = click.substr(click.lastIndexOf('_') + 1, click.length);
	                    var cookie_name = 'privacyShareButtons_' + service;

	                    if ($('#' + event.target.id + ':checked').length) {
							$.cookie(cookie_name,'perma_on',self.options.cookie_options);
	                        $('form fieldset label[for=' + click + ']', context).addClass('checked');
	                    } else {
	                        $.cookie(cookie_name,null,self.options.cookie_options);
							$('form fieldset label[for=' + click + ']', context).removeClass('checked');
	                    }
	                });
			}
		});
	}
	
}

$.fn.socialShareButtons = function(options){
	new SocialButton(this, options);
	return this;
}
$(document).ready($(".social_share_privacy").socialShareButtons(socialshareprivacy_settings));
})(jQuery);
