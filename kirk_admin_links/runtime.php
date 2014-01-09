<?php  

/*

Version: 0.7
Author: Kirk Roberts

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

		$links = '';
		$appLinks = '';

		$API  = new PerchAPI(1.0, 'kirk_admin_links');
		$Lang = $API->get('Lang');

		$Pages = new PerchContent_Pages;
		$pagePath = $_SERVER['PHP_SELF'];
		$page = $Pages->find_by_path($pagePath);

		// check for apps first
		if (!empty($opts['apps']) && is_array($opts['apps'])) {
			foreach ($opts['apps'] as $key) {
        	
        if ($key['page'] == $pagePath) {

        	// found keys to do a match
      		$queryVar = $key['query-var'];
      		$query = perch_get($queryVar);

      		switch ($key['app']) {

      			// blog
      			case 'perch_blog':
      				$id = perch_blog_post_field($query, 'postID', true);
      				$title = perch_blog_post_field($query, 'postTitle', true);

      				if ($id) {

      					$BlogAPI  = new PerchAPI(1.0, 'perch_blog');
								$BlogLang = $BlogAPI->get('Lang');

	        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/?id=' . $id . '">' . $BlogLang->get('Edit Post') . ': &ldquo;' . $title . '&rdquo;</a></div>';
      				}
      				break;

      			// gallery
      			case 'perch_gallery':

      				$id = perch_gallery_album_field($query, 'albumID', true);
      				$title = perch_gallery_album_field($query, 'albumTitle', true);

      				if ($id) {

      					$GalleryAPI  = new PerchAPI(1.0, 'perch_gallery');
								$GalleryLang = $GalleryAPI->get('Lang');

	        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/edit/?id=' . $id . '">' . $GalleryLang->get('Edit Album') . ': &ldquo;' . $title . '&rdquo;</a></div>';
      				}
      				break;

      		}
      		break; // break out of loop
        }
      }
		}

		$Regions = new PerchContent_Regions;
		$Items = new PerchContent_Items;

		if ($page) {

			$pageID = $page->pageID();

			if ($pageID) {

				$pageRegions = $Regions->get_for_page($pageID, false);
				
				if (!empty($links)) $links .= '<hr>';

				// edit page / regions (content app)
				$title = $page->pageTitle();
				$links .= '<div><a class="block" href="'.PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/page/?id=' . $pageID . '">' . $Lang->get('Edit the &ldquo;%s&rdquo; page', $title) . '</a></div>';
				
				if (!empty($links)) $links .= '<hr>';

				$count = 0;
				if (PerchUtil::count($pageRegions)) {

					foreach($pageRegions as $Region) {
						if ($Region->role_may_edit($CurrentUser)) {

							$regionURL = PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/edit/?id=' . PerchUtil::html($Region->id());
							$regionKey = PerchUtil::html($Region->regionKey());

							// add region to list
			        $links .= '<div><a href="' . $regionURL . '" class="block">' . $regionKey . '</a>';
			        
			        // is it a multiple item region?
			        if ($Region->regionMultiple()) {
			        	
			        	// let's see if we have keys to find the specific entry
			        	if (!empty($opts['multiples']) && is_array($opts['multiples'])) {

					        foreach ($opts['multiples'] as $key) {

					        	if ($key['region-name'] == $regionKey) {
						        	
						        	// found keys to do a match
					        		$queryVar = $key['query-var'];
					        		$query = perch_get($queryVar);

					        		// if no query with that handle, break out of loop
					        		if (empty($query)) break;

					        		$slugID = $key['slug-id'];

					        		// use perch_content_custom() to find the item
					        		$item = perch_content_custom($regionKey, array(
					        			'skip-template'=>true,
					        			// 'raw' => false,
					        			'page'	=> $pagePath,
					        			'filter' => $slugID,
					        			'match' => 'eq',
					        			'value' => $query
					        		), true);

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
					        			$links .= '<br> &gt; <a href="' . $itemURL . '"> ' . $Lang->get('Edit') . ' ' . $title . '</a>';
					        		}

						        	break; // exit key search

						        }
					        } // end foreach: keys
				        } // end if: keys exist
			        } // end if: multiple
			        
			        // close region entry
			        $links .= '</div>';

			      } // end if: can edit
					} // end foreach: regions
				} // end if: $pageRegions count

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

			if (empty($BlogLang)) {
				$BlogAPI  = new PerchAPI(1.0, 'perch_blog');
				$BlogLang = $BlogAPI->get('Lang');
			}

			$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/">' . $BlogLang->get('Blog') . '</a> &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/">' . $BlogLang->get('Add Post') . '</a></div>';
		}

		// Gallery check
		if (class_exists('PerchGallery_Albums')) {

			if (empty($GalleryLang)) {
				$GalleryAPI  = new PerchAPI(1.0, 'perch_gallery');
				$GalleryLang = $GalleryAPI->get('Lang');
			}

			$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/">' . $GalleryLang->get('Gallery') . '</a> &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_gallery/edit/">' . $GalleryLang->get('New Album') . '</a></div>';
		}

		// Events check
		if (class_exists('PerchEvents_Events')) {

			if (empty($EventsLang)) {
				$EventsAPI  = new PerchAPI(1.0, 'perch_events');
				$EventsLang = $EventsAPI->get('Lang');
			}

			$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/">' . $EventsLang->get('Events') . '</a> &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/edit/">' . $EventsLang->get('New Event') . '</a></div>';
		}

		if ($appLinks) {
			$links .= '<hr>' . $appLinks;
		}

		// if links, show them
		if ($links) {
			
			echo '<div id="kirk-admin-links">' . $links . '</div>';

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
			width: 200px;
			padding: 16px 18px 18px;
			position: fixed;
			right: 20px;
			z-index: 9999;
			opacity: 0.3;
		}
		#kirk-admin-links:hover {
			opacity: 1;
		}
		#kirk-admin-links > div {
			margin: 0 0 4px;
		}
		#kirk-admin-links a {
			color: #ddd !important;
			text-decoration: none;
		}
		#kirk-admin-links .block {
			display: block;
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