# Kirk Admin Links

## What It Does

Provides a floating palette on the front-end site with direct links to edit the current page, regions, and apps. By default direct links are shown to edit the current page, each of the current page's regions, all shared regions, and entry lists and "add new item" for many of Perch's first-party apps. With a tiny bit of configuration (see Options) direct links can be made to edit the currently viewed multiple item region detail (think list/detail approaches) or individual items in many of Perch's first-party apps (e.g. a Blog post or Gallery album).

The palette is only visible if the viewer is logged into Perch and has User Role privileges to edit something on the current page. The app currently does not show anything in the Perch admin panel, just the palette on the front end. All configuration is done through options in the function call.

Perch 2.4 added the control-E shortcut to edit the current page. This is essentially that made visible and with a lot of extra details.

As of version 0.7 the app uses PerchLang for translation and attempts to use Perch app lang files where possible.

## Why It Helps

You may find it easier to locate the page, post, event, etc using your site's navigation, or maybe you're just browsing the site and notice a typo. If you're already logged in you can go directly to the region editing screen with a single click. This significantly bridges the gap between the public-facing site and the editing experience.

With this app installed it's possible to "round trip" between the public site and Admin panel in one browser tab using the URL link back to the page in the Perch sidebar (not the View Page link added in Perch 2.4, which opens a new tab). No more "two tab tango" or page reloading to check your edits.

## Requirements

- Perch 2.x with the standard Content app
- For best results it's recommended to have the latest version of Perch and all first-party apps (e.g. Blog)

## License & Disclaimers

- Use and abuse this at your own risk. 
- This app taps directly into Perch's Content app using non-API functions, so an upgrade of Perch core may break this app's functionality. This may cause a logged-in user to get error messages or warnings on the public site. If this happens, just comment out the kirk_admin_links call and let me know what's happening.
- The code. I know. If you can improve it please do and share it back.

## Installation

1. Add the kirk_admin_links folder to your /perch/addons/apps folder.
2. Include the runtime.php in your /perch/config/apps.php file: `include(PERCH_PATH.'/addons/apps/kirk_admin_links/runtime.php');`
3. Use the `kirk_admin_links()` PHP function call anywhere in the <body> of your site (preferably in an include or perch_layout so you can maintain it in one spot)
4. Optionally add options as detailed below (options should be optional, yes?)

## Options

It's possible to get direct links to items in "multiple item" regions as well as entries in the Blog, Gallery, Events, and Shop apps. You can add as many items to the 'pages' array as you want or need. Attributes are explained below.

Just specify the URL of the page that contains the editable item

```php
kirk_admin_links(array(
	'pages' => array(
		array(
			'page'=>'/index.php', // required (key)
			'query-var'=> 's', // optional, defaults to 's'
			'app'=>'perch_blog', // app handle, required for apps (except Content app)
			// below only needed for multiple item regions
			'region-name'=> 'Region Name', // required for multiple item regions
			'region-page'=> '/index.php', // optional, use for multiple item regions only if the region's page is different than the page we're on
			'slug-id'=>'field_id', // optional, for multiple item regions only, the id of the slug field to test against, defaults to 'slug'
		)
	)	
));
```

If you're using the Blog app and a query variable of 's' (the default) then it might look like this:

```php
kirk_admin_links(array(
	'pages' => array(
		array(
			'page'=>'/blog/post.php',
			'app'=>'perch_blog'
		)
	)	
));
```

Now you'd have a direct link from any Blog post to its corresponding edit screen in Perch.

Or maybe you're also using Gallery and a multiple item region for a list/detail presentation, then it might be something like this:

```php
kirk_admin_links(array(
	'pages' => array(
		array(
			'page'=>'/blog/post.php',
			'app'=>'perch_blog'
		),
		array(
			'page'=>'/gallery/album.php',
			'app'=>'perch_gallery'
		),
		array(
			'page'=>'/news/detail.php',
			'region-name'=>'News',
			'slug-id'=>'title_slug'
		)
	)	
));
```

## Change Log

### Version 0.9

- BREAKING CHANGE: combined "multiples" and "apps" option arrays into "pages" array; multiple item regions now require the 'region-name' attribute
- Added 'region-page' attribute for regions that originate on a different page than they are displayed on
- Added defaults for 'query-var' and 'slug-id'
- Default text in link palette if nothing is editable
- Perch Shop app integration

### Version 0.8.3

- Not sure what happened in this one...

### Version 0.8.2

- Updated palette styles

### Version 0.8.2

- Updated palette styles

### Version 0.8.1

- Changed Gallery check to use perch_gallery_album_details to accomodate older versions

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
