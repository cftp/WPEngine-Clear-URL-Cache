=== WPEngine Clear URL Cache ===
Contributors: simonwheatley, cftp
Tags: twitter, tweet
Requires at least: 3.4.2
Tested up to: 3.4.2
Stable tag: 0.1
 
This WordPress plugin only works on WPEngine, and allows you to target 
a particular URL with an HTTP cache PURGE request.

== Description ==

This WordPress plugin only works on WPEngine, and allows you to target 
a particular URL with an HTTP cache PURGE request. The plugin was created
in response to the non-200 response caching times for WP Engine being 
(at the time of writing) 24 hours. Mostly these caches are cleared 
automatically and as they should be, but occasionally there's a situation
where this does not happen.

WARNING: This plugin really will not work on another service, it entirely
depends on functionality uniquely provided by WPEngine within their service.

If you install the plugin on a host which is not WPEngine, it will post a 
whinging notice in all admin screens requesting you to turn it off.

== Changelog ==

= 0.1 =

* Initial release!
