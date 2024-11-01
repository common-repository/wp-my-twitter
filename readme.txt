=== Plugin Name ===
Contributors: infinetsoftware
Tags: twitter, tweets
Requires at least: 2.0.2
Tested up to: 2.8
Stable tag: trunk

Display your Twitter Tweets on your blog.

== Description ==

WP My Twitter will display your recent Twitter Tweets. Works in your theme, with shortcode, or as a Widget.

= Related =
*	[WP My Twitter](http://www.infinetsoftware.com/blog/40-wp-my-twitter-plugin/)
*	[Sponsored Posts Plugin](http://www.wpsponsored.com/)

== Installation ==

1.	Download the zip file and extract the twitter.php file.
2.	Upload twitter.php to your Wordpress plugins folder.
3.	Login to the Wordpress admin area and activate the plugin.
4.	Browse to Wordpress Settings and then the WP My Twitter tab. Enter your Twitter username and password and click update

= Displaying your Tweets: = 

= As a Sidebar Widget (Requires Wordpress 2.3+ and a Widget-ready Theme) =
1.	Login to the Wordpress admin area and browse to the Appearance Tab
2.	Browse to the Widgets Tab and Enable WP My Twitter

= In Posts/Pages with Shortcode (Wordpress 2.5+): =
*	When writing or editing a page or post, insert the code `[WPMyTwitter]` to display your recent tweets.
*	Optionally specifiy the number of tweets you want to display. Defaults to 5.
	Ex: `[WPMyTwitter Count="10"]`

= In Your Theme: =
*	Insert the following code where you want your Twitter feed to appear in your blog template:
	`<?php WPTwitter_GetTweets(); ?>`
*	Optionally pass a Count to set the max number of tweets to display. Defaults to 5.
	Ex: `<?php WPTwitter_GetTweets(10); ?>`

== Frequently Asked Questions ==

= What are the requirements for WP My Twitter? =

1.	Wordpress 2.x+
2.	PHP 5+
3.	PHP CURL support (to connect to Twitter)
4.	A Twitter Account

== Changelog ==

= 1.1 =
*	Now uses WP Cron for loading and caching Tweets
*	Powered by link is now optional
*	Bugfix for limiting the number of tweets displayed