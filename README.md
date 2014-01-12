# Kirk Admin Links

Provides a floating widget on the front-end site with direct links to edit the current page, regions, and apps. The widget is only visible if the viewer is logged into Perch and has User Role privileges to edit something on the current page.

You may find it easier to locate the page, post, event, etc using your site's navigation, or maybe you're just browsing the site and notice a typo. If you're already logged in you can go directly to the region editing screen with a single click.

With this app installed it's possible to "round trip" between the public site and Admin panel in one browser tab using the URL link back to the page in the Perch sidebar (not the View Page link added in Perch 2.4, which opens a new tab). No more "two tab tango" or page reloading to check your edits.

The app currently does not show anything in the Perch admin panel, just the widget on the front end. All configuration is done through options in the function call.

As of version 0.7 the app uses PerchLang for translation and attempts to use Perch app lang files where possible.

## Requirements

- Perch 2.x with the standard Content app

## License & Disclaimers

- Use and abuse this at your own risk. 
- This app taps directly into Perch's Content app using non-API functions, so an upgrade of Perch core may break this app's functionality.
- The code. I know. If you can improve it please do and share it back.

## Installation

1. Add the kirk_admin_links folder to your /perch/addons/apps folder.
2. Include the runtime.php in your /perch/config/apps.php file: `include(PERCH_PATH.'/addons/apps/kirk_admin_links/runtime.php');`
3. Use the `kirk_admin_links()` PHP function call anywhere in the <body> of your site (preferably in an include or perch_layout so you can maintain it in one spot)
4. Optionally add options as detailed below (options should be optional, yes?)

## Options

It's possible to get direct links to items in "multiple item" regions as well as entries in the Blog, Gallery, and Events apps. You can add as many multiples as you want or need.

To get an item out of a multiple-item region we need to know:

1. the name of the region (the regionKey)
2. the query variable, e.g. "s" from ?s=slug_name
3. the field id of the slug to test against, e.g. "title_slug"

For apps we need to know:

1. the page the app detail appears on (e.g. a blog post)
2. the handle of the app
3. the query variable used to pass in the identifying slug, e.g. "s" from ?s=post_slug

So, like this:
```php
kirk_admin_links(array(
	'multiples' => array(
		array(
			'region-name'=>'The Name of the Region',
			'query-var'=>'s',
			'slug-id'=>'slug_field_id'
		),
		array(
			'region-name'=>'Another Region',
			'query-var'=>'s',
			'slug-id'=>'slug_field_id'
		)
	),
	'apps' => array(
		array(
			'page' => '/your_blog_post_page_path.php',
			'app' => 'perch_blog',
			'query-var' => 's'
		),
		array(
			'page' => '/your_gallery_album_page_path.php',
			'app' => 'perch_gallery',
			'query-var' => 's'
		)
	)
));
```
Two entries are shown for 'multiples' and 'apps' but you might use none or any number needed.

**Note that there are no defaults.** All attributes must be entered for it to work. These are just sample attributes.

## Change Log

### Version 0.8

- Added individual Event item support
- Added privilege/permissions checks for apps
- Removed loop breaks in case the same regionKey is used twice
- Fixed a condition where two dividers would show with nothing in-between

### Version 0.7

- Added Gallery support
- Added individual Gallery album support
- Added Lang folder and en-gb.txt file
- Added Lang support that draws from Perch apps where possible
- Made the widget fade out when not hovered over
- Minor fixes and style updates

### Version 0.6

- BREAKING CHANGE: Changed variable inputs to use hyphens rather than underscores (more like other Perch var inputs)... I need to stop changing these things
- Added multiple item region item title to edit link
- Minor style edits

### Version 0.5

- Added shared regions

### Version 0.4

- Added 'apps' to options
- Added individual blog post editing
- BREAKING CHANGE: Changed variable inputs to be more Perch-like (I think)
- Added page name to "edit" link

### Version 0.3

- Made link/hover color styles !important
- Made $opts optional in kirk_admin_links
- Added checks to remove PHP notices/warnings
- Added support for Events app
- Added support for Blog app

### Version 0.2.1

- Uses perch_content_custom to find a specific item in multiple item regions instead of looping through all items. Should be more efficient with large sets of data.

### Version 0.2

- Added support for editing items in multiple item regions

### Version 0.1

- Initial version
