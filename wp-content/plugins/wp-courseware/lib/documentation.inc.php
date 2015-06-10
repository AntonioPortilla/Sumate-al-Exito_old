<?php

/**
 * Show extra support information.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo_Extra($page)
{
	$page->openPane('wpcw-docs-support-extra', __('Support Information', 'wp_courseware'));
	
	// Edit this
	printf('<p>Information on how users can get support with the plugin can go here...</p>');
	
	?>
	<p>Or you can do content this way too...</p>
	<?php 	
	
	$page->closePane();
}


/**
 * Show standard support information.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo($page)
{
	$page->openPane('wpcw-docs-support', __('Need Fly Plugin Support?', 'wp_courseware'));
	
	echo '<script type="text/javascript" src="//assets.zendesk.com/external/zenbox/v2.6/zenbox.js"></script>
<style type="text/css" media="screen, projection">
  @import url(//assets.zendesk.com/external/zenbox/v2.6/zenbox.css);
</style>
<script type="text/javascript">
  if (typeof(Zenbox) !== "undefined") {
    Zenbox.init({
      dropboxID:   "20211958",
      url:         "https://flyplugins.zendesk.com",
      tabTooltip:  "Support",
      tabImageURL: "https://assets.zendesk.com/external/zenbox/images/tab_support_right.png",
      tabColor:    "#ffa500",
      tabPosition: "Right",
      hide_tab:   "true"
    });
  }
</script>
	';
	
	echo '<p>'. sprintf(__('If you\'re in need of support with any Fly Plugin, please <a href="%s">click here</a>.', 'wp_courseware'), 
		'http://flyplugins.zendesk.com/account/dropboxes/20211958" onClick="script: Zenbox.show(); return false;').'</p>';
	
	$page->closePane();
}


/**
 * Show information on being an affiliate.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo_Affiliate($page)
{
	$page->openPane('wpcw-docs-affiliate', __('Want to become an affiliate?', 'wp_courseware'));
	
	echo '<p>'.__("If you're interested in making money by promoting WP Courseware, please visit our <a href='http://www.flyplugins.com/affiliate/'>affiliate page</a>.", 'wp_courseware').'</p>';	
	
	$page->closePane();
}

/**
 * Show the latest news.
 * 
 * @param Object $page A reference to the page object showing information.
 */
function WPCW_docs_showSupportInfo_News($page)
{
	$page->openPane('wpcw-docs-support-news', __('Latest news from FlyPlugins.com', 'wp_courseware'));
	
	$rss = fetch_feed('http://feeds.feedburner.com/FlyPlugins');

	// Got items, so show the news
	if (!is_wp_error($rss)) 
	{
		$rss_items = $rss->get_items(0, $rss->get_item_quantity(3));	
		
		$content = '<ul>';
		if ( !$rss_items ) {
		    $content .= '<li class="fly">'.__( 'No news items, feed might be broken...', 'wp_courseware' ).'</li>';
		} else {
		    foreach ( $rss_items as $item ) {
		    	$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls=null, 'display' ) );
				$content .= '<li class="fly">';
				$content .= '<a class="rsswidget" href="'.$url.'">'. esc_html( $item->get_title() ) .'</a> ';
				$content .= '</li>';
		    }
		}		
		$content .= '</ul>';
	}

	$content .= '<ul class="wpcw_connect">';
	$content .= '<li class="facebook"><a href="http://flyplugins.com/facebook">'.		__( 'Like Fly Plugins on Facebook', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="twitter"><a href="http://flyplugins.com/twitter">'.			__( 'Follow Fly Plugins on Twitter', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="youtube"><a href="http://flyplugins.com/youtube">'.			__( 'Watch Fly Plugins on YouTube', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="googleplus"><a href="http://flyplugins.com/Google-Plus">'.	__( 'Circle Fly Plugins on Google+', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="rss"><a href="http://feeds.feedburner.com/FlyPlugins">'.	__( 'Subscribe with RSS', 'wp_courseware' ).'</a></li>';
	$content .= '<li class="email"><a href="http://feedburner.google.com/fb/a/mailverify?uri=FlyPlugins&amp;loc=en_US">'.__( 'Subscribe by email', 'wp_courseware' ).'</a></li>';
	$content .= '</ul>';

	echo '<div class="wpcw_fly_support_news">'. $content .'</div>';	
	
	$page->closePane();
}


/**
 * Shows the documentation page for the plugin. TO TWEAK
 */
function WPCW_showPage_Documentation()
{
	// Wrapper added to format lists and other HTML correctly.
	printf('<div class="wpcw_docs_wrapper">');
	
	$page = new PageBuilder(true);
	$page->showPageHeader(__('WP Courseware - Documentation', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	?>	

	
	<?php $page->openPane('wpcw-docs-shortcodes-progress', __('Course Progress Shortcodes', 'wp_courseware')); ?>
	<p><?php _e('To show the current user progress, you can use the <code>[wpcourse]</code> shortcode. Here\'s a summary of the shortcode parameters for <code>[wpcourse]</code>:','wp_courseware') ?></p>
	<dl>
		<dt><?php _e('course','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Required)</em> The ID of the course to show.','wp_courseware') ?></dd>
		
		<dt><?php _e('show_title','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the course title. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>
		
		<dt><?php _e('show_desc','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the course description. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>		
		
		<dt><?php _e('module','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> The number of the module to show from the specified course.','wp_courseware') ?></dd>
		
		<dt><?php _e('module_desc','wp_courseware') ?></dt>
		<dd><?php _e('<em>(Optional)</em> If true, show the module descriptions. (It can be <b>true</b> or <b>false</b>. By default, it\'s <b>false</b>).','wp_courseware') ?></dd>
	</dl>
	
	<br/>
	<p><?php _e('Here are some examples of how <code>[wpcourse]</code> shortcode works:','wp_courseware') ?></p>
	<dl>
		<dt><?php _e('Example 1: <code>[wpcourse course="2" module_desc="false" show_title="false" show_desc="false" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows course 2, just with module and unit titles. Do not show course title, course description or module descriptions.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 2: <code>[wpcourse course="2" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Exactly the same output as example 1.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 3: <code>[wpcourse course="1" module="4" module_desc="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows module 4 from course 1, with module titles and descriptions, and unit titles.','wp_courseware') ?></dd>
		
		<dt><?php _e('Example 4: <code>[wpcourse course="1" module_desc="true" show_title="true" show_desc="true" /]</code>','wp_courseware') ?></dt>
		<dd><?php _e('Shows course 1, with course title, course description, module title, module description and unit titles.','wp_courseware') ?></dd>
	</dl>
	
	<?php $page->closePane();  ?>
	
	
	
	<?php $page->openPane('wpcw-docs-shortcodes-example', __('WP Courseware Video Tutorials', 'wp_courseware')); ?>
	
	<div class="wpcw_vids"><h2><?php _e('How to create a new course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/x7q6T0R7vLg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to create a new module','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/v2h2y3iIOio?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<div class="wpcw_vids"><h2><?php _e('How to create a new unit and assign it to a module','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/3nrLv0wxK3w?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to edit and convert a post into a unit','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/zpnQSqKTePM?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<div class="wpcw_vids"><h2><?php _e('How to create a quiz','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/DK2bhF9goAw?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to add a course outline page','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/JR4k5SRlSD8?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to add a course menu widget to the sidebar','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/mwsE7l9sfmg?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to enroll students and track their progress','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/-1Gfh-3_Mxw?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<div class="wpcw_vids"><h2><?php _e('How to use the grade book','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/dsQrDqew8yk?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to import and export a course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/8m4o5HHq-rw?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('Bulk User Import and Course Enrollment via CSV','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/SPl2N9075LQ?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('How to Generate a PDF Certificate of Completion for Your Course','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/5bPUkGlNefI?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>
	
	<div class="wpcw_vids"><h2><?php _e('WP Courseware Automated Course Enrollment with S2 Member','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/MHwAhWe7Xg0?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('WP Courseware Automated Course Enrollment with Magic Members','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/JTlgEQ7KqY8?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="wpcw_vids"><h2><?php _e('WP Courseware Automated Course Enrollment with WishList Member','wp_courseware') ?></h2>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/EkgpNvMfReY?rel=0" frameborder="0" allowfullscreen></iframe>
	</div>


	<?php $page->closePane();  ?>
	
	
	<?php 
	// Needed to show RHS section for panels
	$page->showPageMiddle('23%');

	// RHS Support Information
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
	
	printf('</div>');
}

?>