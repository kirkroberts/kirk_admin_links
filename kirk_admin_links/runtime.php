<?php  

/*

Version: 0.9
Author: Kirk Roberts

USAGE

kirk_admin_links(array(
	'pages' => array(
		array(
			'page'=>'/index.php', // required (key)
			'query-var'=> 's', // optional, defaults to 's'
			'app'=>, // required for apps, e.g. 'perch_shop'
			// below only needed for multiple item regions
			'region-name'=> 'Region Name', // required for multiple item regions
			'region-page'=> '/index.php', // optional, use for multiple item regions only if the region's page is different than the page we're on
			'slug-id'=>'field_id', // optional for multiple item regions, unnecessary for apps, defaults to 'slug'
		)
	)	
));

*/

// need to do this immediately to avoid a PHP session warning later
// this feels wrong, but it works for now
$Users = new PerchUsers;
$kirkAdminLinksCurrentUser = $Users->get_current_user();


function kirk_admin_links($opts = array()) {
	
	global $kirkAdminLinksCurrentUser;

	$CurrentUser = $kirkAdminLinksCurrentUser;

	// is the user logged in?
	if (is_object($CurrentUser) && $CurrentUser->logged_in()) {

		$API  = new PerchAPI(1.0, 'kirk_admin_links');
		$Lang = $API->get('Lang');

		// we're relying on these classes from the Content app
		$Regions = new PerchContent_Regions;
		$Items = new PerchContent_Items;
		$Pages = new PerchContent_Pages;

		$currentPagePath = $_SERVER['PHP_SELF'];
		$page = $Pages->find_by_path($currentPagePath);

		$links = '';
		$appLinks = '';

		// check for pages first
		if (!empty($opts['pages']) && is_array($opts['pages'])) {

			$tests = $opts['pages'];

			foreach ($tests as $test) {

				if ($test['page'] == $currentPagePath) {

					$queryVar = !empty($test['query-var']) ? $test['query-var'] : 's';

					$slug = perch_get($queryVar);

					if (!empty($slug)) {

						// check apps
						if (!empty($test['app'])) {
							switch ($test['app']) {

		      			// blog
		      			case 'perch_blog':

		      				if ($CurrentUser->has_priv('perch_blog')) {
			      				$id = '';
			      				$id = perch_blog_post_field($slug, 'postID', true);
			      				$title = perch_blog_post_field($slug, 'postTitle', true);

			      				if (!empty($id)) {

			      					$BlogAPI  = new PerchAPI(1.0, 'perch_blog');
											$BlogLang = $BlogAPI->get('Lang');

				        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/?id=' . $id . '">' . $BlogLang->get('Edit Post') . ': &ldquo;' . $title . '&rdquo;</a></div>';
			      				}
			      			}
		      				break;

		      			// gallery
		      			case 'perch_gallery':

		      				if ($CurrentUser->has_priv('perch_gallery')) {
			      				$id = '';
			      				$album = perch_gallery_album_details($slug, array(
			      					'skip-template' => true
			      					));
			      				if ($album) {
			      					$id = $album['albumID'];
			      					$title = $album['albumTitle'];
			      				}
			      				if (!empty($id)) {

			      					$GalleryAPI  = new PerchAPI(1.0, 'perch_gallery');
											$GalleryLang = $GalleryAPI->get('Lang');

				        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/edit/?id=' . $id . '">' . $GalleryLang->get('Edit Album') . ': &ldquo;' . $title . '&rdquo;</a></div>';
			      				}
			      			}
		      				break;

		      			case 'perch_events':

		      				if ($CurrentUser->has_priv('perch_events')) {
			      				$id = '';
			      				$event = perch_events_custom(array(
			      					'filter'=>'eventSlug',
									    'match'=>'eq',
									    'value'=>$slug,
									    'skip-template'=>true
									    ));
			      				$id = $event[0]['eventID'];
			      				$title = $event[0]['eventTitle'];

			      				if (!empty($id)) {

			      					$EventsAPI  = new PerchAPI(1.0, 'perch_events');
											$EventsLang = $EventsAPI->get('Lang');

				        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/edit/?id=' . $id . '">' . $EventsLang->get('Edit Event') . ': &ldquo;' . $title . '&rdquo;</a></div>';
			      				}
			      			}
		      				break;

		      			case 'perch_shop':

		      				if ($CurrentUser->has_priv('perch_shop')) {
			      				$id = '';
			      				$product = perch_shop_custom(array(
			      					'filter'=>'productSlug',
									    'match'=>'eq',
									    'value'=>$slug,
									    'skip-template'=>true
									    ));
			      				$id = $product[0]['productID'];
			      				$title = $product[0]['productTitle'];

			      				if (!empty($id)) {

			      					$ShopAPI  = new PerchAPI(1.0, 'perch_shop');
											$ShopLang = $ShopAPI->get('Lang');

				        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_shop/edit/?id=' . $id . '">' . $ShopLang->get('Edit Product') . ': &ldquo;' . $title . '&rdquo;</a></div>';
			      				}
			      			}
		      				break;

		      		}
						}

						// check Content
						if (!empty($test['region-name'])) {

							$regionKey = $test['region-name'];

							$regionPagePath = !empty($test['region-page']) ? $test['region-page'] : $currentPagePath;

							$slugID = !empty($test['slug-id']) ? $test['slug-id'] : 'slug';

	        		// use perch_content_custom() to find the item
	        		$item = perch_content_custom($regionKey, array(
	        			'skip-template'=>true,
	        			// 'raw' => false,
	        			'page'	=> $regionPagePath,
	        			'filter' => $slugID,
	        			'match' => 'eq',
	        			'value' => $slug
	        		), true);

	        		if (!empty($item)) {

	        			$regionPage = $Pages->find_by_path($regionPagePath);
	        			$regionPageID = $regionPage->pageID();
								$Region = $Regions->find_for_page_by_key($regionPageID, $regionKey);
								$regionURL = PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/edit/?id=' . PerchUtil::html($Region->id());

		        		// get the id, make the link
		        		$itemID = $item[0]['_id'];
		        		$title = $item[0]['_title'];
		        		if (empty($title)) {
		        			$title = $Lang->get('this item');
		        		} else {
		        			$title = '&ldquo;' . $title . '&rdquo;';
		        		}
		        		if ($itemID) {
		        			$itemURL = $regionURL . '&itm=' . $itemID;
		        			$links .= '<div><a class="block" href="' . $itemURL . '"> ' . $Lang->get('Edit') . ' ' . $title . '</a></div>';
		        		}
	        		}

						} // end check Content

					}
				}
			}
		}

		// page regions

		if ($page) {

			$pageID = $page->pageID();

			if ($pageID) {

				$pageRegions = $Regions->get_for_page($pageID, false);
				
				if (!empty($links)) $links .= '<hr>';

				// edit page / regions (content app)
				$title = $page->pageTitle();
				$links .= '<div><a class="block" href="'.PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/page/?id=' . $pageID . '">' . $Lang->get('Edit the &ldquo;%s&rdquo; page', $title) . '</a></div>';

				$count = 0;
				$pageRegionLinks = '';
				if (PerchUtil::count($pageRegions)) {

					foreach($pageRegions as $Region) {
						if ($Region->role_may_edit($CurrentUser)) {

							$regionURL = PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/edit/?id=' . PerchUtil::html($Region->id());
							$regionKey = PerchUtil::html($Region->regionKey());

							// add region to list
			        $pageRegionLinks .= '<div><a href="' . $regionURL . '" class="block">' . $regionKey . '</a>';
			        
			        // close region entry
			        $pageRegionLinks .= '</div>';

			      } // end if: can edit
					} // end foreach: regions
				} // end if: $pageRegions count

				if (!empty($pageRegionLinks)) {
					if (!empty($links)) $links .= '<hr>';
					$links .= $pageRegionLinks;
				}

			} // end if: $pageID
		} // end if: $page


		// shared regions
		
		$sharedRegions = $Regions->get_shared();

		if (PerchUtil::count($sharedRegions)) {

			$sharedRegionLinks = '';
			foreach($sharedRegions as $Region) {
				if ($Region->role_may_edit($CurrentUser)) {

					$regionURL = PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/edit/?id=' . PerchUtil::html($Region->id());
					$regionKey = PerchUtil::html($Region->regionKey());

					// add region to list
	        $sharedRegionLinks .= '<div><a href="' . $regionURL . '" class="block">' . $regionKey . '</a></div>';
	      }
	    }
	    if (!empty($sharedRegionLinks)) {

	    	if (!empty($links)) $links .= '<hr>';
	    	$links .= $sharedRegionLinks;

	    }
	  }


		// Blog check
		if (class_exists('PerchBlog_Posts')) {

			if ($CurrentUser->has_priv('perch_blog')) {
				if (empty($BlogLang)) {
					$BlogAPI  = new PerchAPI(1.0, 'perch_blog');
					$BlogLang = $BlogAPI->get('Lang');
				}

				$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/">' . $BlogLang->get('Blog') . '</a>';
				if ($CurrentUser->has_priv('perch_blog.post.create')) {
					$appLinks .= ' &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/">' . $BlogLang->get('Add Post') . '</a>';
				}
				$appLinks .= '</div>';
			}

		}

		// Gallery check
		if (class_exists('PerchGallery_Albums')) {

			if ($CurrentUser->has_priv('perch_gallery')) {
				if (empty($GalleryLang)) {
					$GalleryAPI  = new PerchAPI(1.0, 'perch_gallery');
					$GalleryLang = $GalleryAPI->get('Lang');
				}

				$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/">' . $GalleryLang->get('Gallery') . '</a>';

				if ($CurrentUser->has_priv('perch_gallery.album.create')) {
					$appLinks .= ' &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/edit/">' . $GalleryLang->get('New Album') . '</a>';
				}
				$appLinks .= '</div>';
			}

		}

		// Events check
		if (class_exists('PerchEvents_Events')) {

			if ($CurrentUser->has_priv('perch_events')) {
				if (empty($EventsLang)) {
					$EventsAPI  = new PerchAPI(1.0, 'perch_events');
					$EventsLang = $EventsAPI->get('Lang');
				}

				$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/">' . $EventsLang->get('Events') . '</a>';

				$appLinks .= ' &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/edit/">' . $EventsLang->get('New Event') . '</a>';

				$appLinks .= '</div>';
			}
		}

		// Shop check
		if (class_exists('PerchShop_Products')) {

			if ($CurrentUser->has_priv('perch_shop')) {
				if (empty($ShopLang)) {
					$ShopAPI  = new PerchAPI(1.0, 'perch_shop');
					$ShopLang = $ShopAPI->get('Lang');
				}

				$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_shop/">' . $ShopLang->get('Shop') . '</a>';

				$appLinks .= ' &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_shop/edit/">' . $ShopLang->get('Add Product') . '</a>';

				$appLinks .= '</div>';
			}
		}

		if ($appLinks) {
			if (!empty($links)) $links .= '<hr>';
			$links .= $appLinks;
		}

		// if links, show them
		if ($links) {
			
			echo '<div id="kirk-admin-links">' . $links . '</div>';

		} else {

			echo '<div id="kirk-admin-links">Logged into Perch: no links</div>';

		}

		// output these directly to the page (just so they're easier to edit here)
?>

	<style>
		#kirk-admin-links {
			background: #444;
			background: rgba(51,51,51,0.9);
			border-radius: 10px;
			bottom: 20px;
			box-shadow: 0 5px 15px #999;
			color: #999;
			cursor: default;
			font: 14px/18px arial,sans-serif;
			opacity: 0.3;
			padding: 16px 18px 18px;
			position: fixed;
			right: 20px;
			text-align: left;
			text-transform: none;
			width: 200px;
			z-index: 9999;
		}
		#kirk-admin-links:hover {
			opacity: 1;
		}
		#kirk-admin-links > div {
			margin: 0 0 4px;
		}
		#kirk-admin-links a {
			color: #ddd !important;
			display: inline;
			font: 14px/18px arial,sans-serif;
			padding: 0;
			text-decoration: none;
			text-transform: none;
		}
		#kirk-admin-links .block {
			display: block;
		}
		#kirk-admin-links .inset {
			display: block;
			position: relative;
			padding-left: 1em;
		}
		#kirk-admin-links .inset:before {
			content: '> ';
			position: absolute;
			top: 0;
			left: 0;
		}
		#kirk-admin-links a:hover {
			color: #fff !important;
		}
		#kirk-admin-links hr {
			border: 0;
			border-bottom: 1px solid #777;
			height: 0;
			padding: 0;
			margin: 8px 0 8px;
		}
		#kirk-admin-links,
		#kirk-admin-links a {
			-webkit-transition: all .3s;
			-moz-transition: all .3s;
			-ms-transition: all .3s;
			-o-transition: all .3s;
			transition: all .3s;
		}
	</style>

<?php

	} // end if: user logged in

} // end function


?>