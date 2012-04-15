Privacy Share Buttons
=====================

A jQuery plugins that enables a privacy-aware version of the weel know "share" buttons for different social networks.

Supported buttons: Facebook, Twitter, GooglePlus, and Identi.ca (sort of).

The plugins offers the possibility of showing in posts (and optionally pages) buttons for sharing content to the supported social networks. These buttons are dummy-button by default, and only after explicit action by the visitor they are activated and become the real buttons. This allows to give control to the single visitor if he wants to send sensible personal information to third-parties sites, and thus providing a nice compromise between privacy and social sharing.

The repository contains mainly a Wordpress plugins tought to help integration of the jQuery plugin: if you are only interested in the javascript part, have a look in the _js/_ directory and forget everything else. The javascript code is completely independet and can be used outside the Wordpress plugin.

History and Credits
-------------------

This is a fork/rewrite of a plugin [originally written by heisde.de](http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html). Most of the original code is gone, but I've kept referneces and credits in the relevant files. I've send my changes upstream but had no response, probably they are not interested in further developing the plugins or weren't interested in my changes. 

There are a number of Wordpress plugins in the wordpress.org repository that serve to integrate the original Heise.de jQuery code into Wordpress. I've tried 3 of them [1](http://wordpress.org/extend/plugins/2-click-socialmedia-buttons/) [2](http://wordpress.org/extend/plugins/wp-social-share-privacy-plugin/) [3](http://wordpress.org/extend/plugins/xsd-socialshareprivacy/), and took lot of inspiration from them, but I wasn't totally satisfied by any of them. And all the docs are written in German, wich I don't speak. And I wanted to patch the original jQuery anyway.

Known problems
--------------
1. Identi.ca buttons sucks, it's a dirty hack that needs a local .php file to be installed, and is too slow.
2. There are some problems with the rendering of the buttons, expecially on some Wordpress themes. I am no CSS wizard. I'm trying to get a better output but I will definetly appreciate some more expert advice.