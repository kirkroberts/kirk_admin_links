<?php  

/*

Version: 0.6
Author: Kirk Roberts

*/

// admin links
// need to do this immediately to avoid a PHP session warning later
$Users = new PerchUsers;
$kirkAdminLinksCurrentUser = $Users->get_current_user();


function kirk_admin_links($opts = array()) {
	
	global $kirkAdminLinksCurrentUser;

	$CurrentUser = $kirkAdminLinksCurrentUser;
	$kirkAdminLinks = '';
	$links = '';
	$appLinks = '';

	// is the user logged in?
	if (is_object($CurrentUser) && $CurrentUser->logged_in()) {

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
	        			$links .= '<div><a class="block" href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/?id=' . $id . '">Edit Post: &ldquo;' . $title . '&rdquo;</a></div>';
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

				$pageRegions = $Regions->get_for_page($pageID, false); // include_shared
				
				// edit page / regions (content app)
				$links .= '<div><a class="block" href="'.PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/page/?id=' . $pageID . '">Edit the &ldquo;' . $page->pageTitle() . '&rdquo; page</a></div>';
				$links .= '<hr>';
				$count = 0;
				if (PerchUtil::count($pageRegions)) {

					foreach($pageRegions as $Region) {
						if ($Region->role_may_edit($CurrentUser)) {

							$regionURL = PerchUtil::html(PERCH_LOGINPATH).'/core/apps/content/edit/?id=' . PerchUtil::html($Region->id());
							$regionKey = PerchUtil::html($Region->regionKey());

							// add region to list
			        $links .= '<div><a href="' . $regionURL . '" class="edit">' . $regionKey . '</a>';
			        
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
					        			$title = 'this item';
					        		} else {
					        			$title = '&ldquo;' . $title . '&rdquo;';
					        		}
					        		if ($itemID) {
					        			$itemURL = $regionURL . '&itm=' . $itemID;
					        			$links .= '<br> &gt; <a href="' . $itemURL . '"> Edit ' . $title . '</a>';
					        		}

						        	break; // exit key search

						        }
					        } // end foreach: keys
				        } // end if: keys exist
			        } // end if: multiple
			        
			        // close region entry
			        $links .= '</div>';

			        // keep track of how many regions we're actually outputting
			        // ++$count;

			      } // end if: can edit
					} // end foreach: regions
				} // end if: $pageRegions count

				// no regions
				// if ($count == 0) $links .= '<div>( there are no editable regions )</div>';

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
	        $sharedRegionLinks .= '<div><a href="' . $regionURL . '" class="edit">' . $regionKey . '</a>';
	      }
	    }
	    if (!empty($sharedRegionLinks)) $links .= '<hr>' . $sharedRegionLinks;
	  }


		// Events check
		if (class_exists('PerchEvents_Events')) {
			$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/">Events List</a> &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_events/edit/">Add Event</a></div>';
		}

		// Blog check
		if (class_exists('PerchBlog_Posts')) {
			$appLinks .= '<div><a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/">Blog Posts</a> &nbsp&gt;&nbsp; <a href="' . PerchUtil::html(PERCH_LOGINPATH).'/addons/apps/perch_blog/edit/">Add Post</a></div>';
		}
		if ($appLinks) {
			$links .= '<hr>' . $appLinks;
		}

		// if links, show them
		if ($links) {
			
			$kirkAdminLinks = '<div id="kirk-admin-links">' . $links . '</div>';
			echo $kirkAdminLinks;

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
			font: 14px/18px arial,sans-serif;
			width: 200px;
			padding: 16px 18px 18px;
			position: fixed;
			right: 20px;
			z-index: 9999;
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
	</style>

<?php

	} // end if: user logged in

} // end function


?>