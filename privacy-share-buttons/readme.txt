=== Privacy Share Buttons ===
Contributors: lucha
Tags: privacy, social, twitter, facebook, identica, googleplus
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: 0.1

Enables the well-known "Share this" buttons for different social networks, but with respect toward's your user privacy and data.

== Description ==
A simple interface to a jQuery plug-in, originally written by heisde.de and thereafter re-written by the author.

The plugins offers the possibility of showing in posts (and optionally pages) buttons for sharing content to Facebook,
Google Plus, Twitter, and Identi.ca. These buttons are dummy-button by default, and only after explicit action by the visitor
they are activated and become the real buttons. This allows to give control to the single visitor if he wants to send 
sensible personal information to third-parties sites, and thus providing a nice compromise between privacy and social sharing.


*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.


== Installation ==

1. Upload `privacy-share-buttons` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the configuration page of the plugins to choose which buttons to show (and where)

The buttons rendering could work very badly depending on your theme's CSS. I'm not very good at this, so if you can come up with a nicer way to show the buttons, I'll be glad to include it. If the buttons looks like half hidden on your theme, try adding these lines to your CSS:

.post {overflow: visible;}
.entry-content {overflow: visible;}

== Changelog ==

= 0.1 =
* First version