# Extending the WP REST API
Sample code for extending the [WordPress REST API](https://wordpress.org/plugins/rest-api/).  [Slides are also available](https://docs.google.com/presentation/d/1o4gJnEcq1vbDUsjZu_zRfh8D7crxzU45gTCquZOFODw/pub?start=false&loop=false&delayms=3000)


## Examples in this Code
* Add revision counts to posts: ```register_api_field()```
* Add featured image URL and dimensions to posts: ```rest_prepare_post``` filter and ```add_link()```
* Custom routes: ```register_rest_route```
* Custom authentication: ```determine_current_user``` filter
* Forcing API Link in headers to SSL: ```rest_url``` filter
* Disallowing non-SSL requests: ```rest_pre_dispatch``` filter
* Non-JSON responses: ```rest_pre_serve_request``` filter
* Change base API URL prefix: ```‘rest_url_prefix’``` filter
* Hide media endpoing from non-authenticated users: ```global $wp_post_types; $wp_post_types['attachment']->show_in_rest = is_user_logged_in();```
* Disabled media endpoint: ```rest_dispatch_request``` filter

