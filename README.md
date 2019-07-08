# Micropub Like Extension

A FreshRSS extension which (attempts to) post a "Like" to a Micropub endpoint when a user "favourites" a post. 

**NOTE: THIS PLUGIN NEEDS YOU TO CREATE A HOOK THAT DOES NOT EXIST.** An example of this hook and its implementation is [here](https://github.com/rosiel/FreshRSS/commit/3a94ce44bbf050f6fb8ef6c379ba11666d1b0d2d).

To use this plugin with an appropriately tweaked FreshRSS (see above), upload this directory in your `./extensions` directory and enable it on the extension panel in FreshRSS. You can configure the endpoint and an authentication token by clicking on the "Manage" button.

## Example configuration with Wordpress

This extension has (only) been tested with Wordpress. If you have a Wordpress.org site, install and activate the following plugins which are bundled with [IndieWeb](https://wordpress.org/plugins/indieweb/):

* [Micropub](https://wordpress.org/plugins/micropub/)
* [Post Kinds](https://wordpress.org/plugins/indieweb-post-kinds/)

You can find your micropub endpoint in the `rel="micropub"` header when doing a `GET` request on your Wordpress site's home page. With "pretty permalinks" configured (not `?p=XXX`), your Micropub endpoint may be at `https://your-domain.org/wp-json/micropub/1.0/endpoint`. 

I have no idea how to properly get an authentication token, but here's what worked for me. Taking advantage of [Quill](https://quill.p3k.io/), go there and enter your wordpress URL to sign in. You will need:

* at least one `rel="me"` link on your homepage that supports IndieAuth. This could be a social account, or your wordpress site itself. One way of doing this is by placing the `rel-me` widget provided by the IndieWeb plugin to an area on your site, configuring the options under IndieWeb > Options, and adding your socials to their respective fields, or your Wordpress homepage to the "Other Sites" box, in your User Profile (oddly, the "Website" field doesn't seem to create a rel-me link). 
* If using your Wordpress site as an auth endpoint, install and activate [IndieAuth](https://wordpress.org/plugins/indieauth/) and if required, fix your .htaccess file. It sometimes takes me two attempts to log in with Quill after doing this.

When logged in initially with Quill, your user information, including the access token, is displayed on the screen. After login, it is also available under your user settings in Quill. Put the access token in the configuration form for this plugin.

Once this plugin has been configured with an endpoint and an access token, clicking the "favourite" (star) in FreshRSS will create a post in your wordpress site of type "Like", that points to the article that you liked.

These likes will show up as very minimalistic posts in your feed and on your frontpage, but can be filtered out with the following in your theme's functions.php file:
```
function exclude_likes_faves ($query) {
        if ( $query->is_home OR $query->is_feed ) {
                $query->set( 'exclude_kind', 'like,favorite');
        }
        return $query;
}

add_action('pre_get_posts', 'exclude_likes_faves' );

```

If you want the authors of the posts to be informed of your actions, install and configure:

* [Webmention](https://wordpress.org/plugins/webmention/)


## Changelog
- 0.1 initial version
