# Kirk Admin Links

Provides a floating widget on the front-end site with direct links to edit the current page, regions, and apps. The widget is only visible if the viewer is logged into Perch.

## Installation

1. Add kirk_admin_links folder to your /perch/addons/apps folder.
2. Include the runtime.php in your /perch/config/apps.php file.
3. Use the kirk_admin_links() function call anywhere in the <body> of your site (preferably in an include or perch_layout so you can maintain it in one spot)
4. Optionally add options as detailed below (options should be optional, yes?)

## Options

It's possible to get direct links to items in "multiple item" regions as well as posts in the Blog app (I may eventually add more). You can add as many multiples as you want or need.

To get an item out of a multiple-item region we need to know:

1. the name of the region (the regionKey)
2. the query variable, e.g. "s" from ?s=slug_name
3. the field id of the slug to test against, e.g. "title_slug"

For apps we need to know:

1. the page the app detail appears on (e.g. a blog post)
2. the handle of the app
3. the query variable used to pass in the identifying slug (e.g. the postSlug value)

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
		)
	)
));
```

## Change Log

### Version 0.6

BREAKING CHANGE: Changed variable inputs to use hyphens rather than underscores (more like other Perch var inputs)... I need to stop changing these things
Added multiple item region item title to edit link
Minor style edits

### Version 0.5

Added shared regions

### Version 0.4

Added 'apps' to options
Added individual blog post editing
BREAKING CHANGE: Changed variable inputs to be more Perch-like (I think)
Added page name to "edit" link

### Version 0.3

Made link/hover color styles !important
Made $opts optional in kirk_admin_links
Added checks to remove PHP notices/warnings
Added support for Events app
Added support for Blog app

### Version 0.2.1

Uses perch_content_custom to find a specific item in multiple item regions instead of looping through all items. Should be more efficient with large sets of data.

### Version 0.2

Added support for editing items in multiple item regions

### Version 0.1

Initial version
