<?php
/*
Plugin Name: We're Already Doing This!
Plugin URI: http://maxwoolf.co.uk
Description: Aimed at the commentariat theme, allow people and organisations to say "We're already doing this!".
Version: 0.4a
Author: Max Woolf
Author URI: http://maxwoolf.co.uk
License: All Rights Reserved
*/
global $wadt_db_version;
$wadt_db_version = "1.0";

function wadt_install() {
   global $wpdb;
   global $pnstats_db_version;

   $table_name = $wpdb->prefix . "wadt";
      
   $sql = "CREATE TABLE $table_name (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `postcode` varchar(15) DEFAULT NULL,
  `org_name` varchar(200) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `website` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);
   $wpdb->print_error();
 
   add_option("wadt_db_version", $wadt_db_version);
}

function build_menu()
{
	add_menu_page( "We're already doing this!", "WADT!", 'manage_options', 'wadt', 'build_menu_page');
}

function build_menu_page()
{
	global $wpdb;
	//Block someone without the right privileges
	if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    if(isset($_POST['delete']))
    {
	    if(!$wpdb->query("DELETE FROM ". $wpdb->prefix . "wadt WHERE id=". $_POST['delete']))
	    {
	    	echo "Database Error";
			echo "DELETE FROM ". $wpdb->prefix . "wadt WHERE id=". $_POST['delete'];
	    }
    }

		$actions = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix . "wadt");
	//Get any current stats in the database    
	echo "<div class='wrap'><div class='icon32' style='background-image:url(../wp-content/plugins/podnoshstats/pn-logo-wp-big.png)'></div><h2>We're already doing this!</h2>";
    echo "<table class='widefat'>
<thead>
    <tr>
    	<th>ID</th>
    	<th>Post</th>
        <th>Title</th>   
        <th>Description</th>
        <th>Postcode</th>
        <th>Org Name</th>
        <th>Telephone</th> 
        <th>Website</th>  
        <th>Delete</th> 
    </tr>
</thead>
<tfoot>
    <tr>
		<th>ID</th>
		<th>Post</th>
        <th>Title</th>   
        <th>Description</th>
        <th>Postcode</th>
        <th>Org Name</th>
        <th>Telephone</th> 
        <th>Website</th> 
        <th>Delete</th> 
    </tr>
</tfoot>
<tbody>";

foreach ($actions as $action) {
	echo "<tr>";
		echo "<td>" . $action->id . "</td>";
		echo "<td>" . $action->post_id . "</td>";
    	echo "<td>" . $action->title . "</td>";
    	echo "<td>" . $action->description . "</td>";
    	echo "<td>" . $action->postcode . "</td>";
    	echo "<td>" . $action->org_name . "</td>";
    	echo "<td>" . $action->telephone . "</td>";
    	echo "<td><a href=\"".$action->website."\">" . $action->website . "</a></td>";
    	echo "<td><form method='POST'><input type='hidden' value='".$action->id."' name='delete'><input class='button-primary' type='submit' value='Delete Help'></td></form>";
    echo "</tr>";
	}
}

function create_wadt_post_form()
{
	global $post;
	echo $content;
	echo "<br />";
	echo "<h2 id='wadt-form'>Do you or your organisation already do this?</h2><p>Let everyone know what is already happening in the city. (Any information you add will be shown on this page and on our <a href='http://fairbrum.podnosh.com/were-already-doing-it/'>map</a>.) All of these fields are <i>optional</i> except for the description.</p>";
	echo "<form method='POST'><input type='text' name='title' placeholder='Name'>";
	echo "<input type='text' name='org_name' placeholder='Your Organisation'><br />";
	echo "<input type='text' name='website' placeholder='Website'>";
	echo "<input type='text' name='postcode' placeholder='Postcode'><br />";
	echo "<input type='text' name='telephone' placeholder='Phone Number'><br />";
	echo "<textarea rows='5' cols='45' name='description' placeholder='Describe how you or your organisation are already doing this recommendation.'></textarea><br />";
	echo "<input type='hidden' name='post_id' value='".$post->ID."'>";
	echo "<input type='submit' value='Save'>";
	echo "</form>";
	echo "<br />";
}

function submit_wadt()
{

	global $wpdb;
		//If someone has entered in some data, then let's put it in the database.
	define( 'DIEONDBERROR', true );
	$wpdb->show_errors();
	if(isset($_POST['title']))
	{
	global $post;
	$sql = "INSERT INTO ". $wpdb->prefix . "wadt (`id`, `post_id`, `title`, `description`, `postcode`, `org_name`, `telephone`, `website`) VALUES (NULL, '".$_POST['post_id']."', '". $_POST['title']."', '". $_POST['description']."', '". $_POST['postcode']."', '". $_POST['org_name']."', '". $_POST['telephone']."', '". $_POST['website']."')";
	$wpdb->query($sql);
	}
}

function add_gmaps_js()
{
	echo "<script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=AIzaSyAJU5MBDWPhAnjDe7sOMIhCYR4mH2fvzk8&sensor=false'></script>";
	echo "<script type='text/javascript'>

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-34413982-1']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

  </script>";
	//echo "<style type='text/css'>html { height: 100% }body { height: 100%; margin: 0; padding: 0 }#map_canvas { height: 100% }</style>";
	
}

function get_postcodes()
{
	global $wpdb;
	$sql = "SELECT * FROM ". $wpdb->prefix . "wadt";
	return $wpdb->get_results($sql);
}

function wadt_map() {
    echo "<div id='map_canvas' style='width:100%; height:800px; position:inherit;'></div> <br />";
    echo "<script type='text/javascript'>
      function initialize() {
      var geocoder;
      geocoder = new google.maps.Geocoder();
        var mapOptions = {
          center: new google.maps.LatLng(52.48624299999999, -1.890401),
          zoom: 10,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
    ";
    $datas = get_postcodes();
    foreach($datas as $data)
    {
		if (substr($data->website, 0, 7) != "http://") {
			$url = "http://" . $data->website;
		}else{
			$url = $data->website;
		}
		if (substr($data->website, 0, 7) != "http://") {
			$url = "http://" . $data->website;
		}else{
			$url = $data->website;
		}
		if($data->org_name != "")
		{
		  $name =  $data->org_name ." is already doing the recommendation ";
		}else if($data->title != "")
		{
		  $name =  $data->title ." is already doing the recommendation ";
		}else{
		  $name =  "Anonymous is already doing the recommendation ";
		}
		$current_post = get_post($data->post_id);
    	echo "
        geocoder.geocode( { 'address': '". $data->postcode ."'}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
                map: map,
                animation: google.maps.Animation.DROP,
                title: '". $data->postcode ."',
                position: results[0].geometry.location
            });
            var infowindow = new google.maps.InfoWindow({
            content: '".$name." ".$current_post->post_title.". <br /> ".addslashes($data->description)." <a href=\'".get_permalink($data->post_id)."\'>Read more...</a><br />Tel: ".$data->telephone ." - Website: <a href=\'".$url."\' target=\'_blank\'>".$url."</a>'
        });
        
        google.maps.event.addListener(marker, 'click', function() {
        	infowindow.close()
          infowindow.open(map,marker);
        });
          } else {
            alert('Geocode was not successful for the following reason: ' + status);
          }
        });";
    }
    
    echo "}</script>";
    echo "<script type='text/javascript'>google.maps.event.addDomListener(window, 'load', initialize);</script>";
}


function show_wadt()
{
	global $wpdb;
	global $post;
	$sql = "SELECT * FROM ". $wpdb->prefix ."wadt WHERE `post_id` = ".$post->ID."";
	$results = $wpdb->get_results($sql);
	echo "<h4 class='comment-heading post-title'>".sizeof($results)." already doing this</h4>";
    echo "<ol class='commentlist'>";
	foreach($results as $result) {
		echo "<li class='comment even thread-even depth-1' id='comment-1'>";
		echo "<div class='comment-body' id='div-comment-1'>";
		echo "<img alt='' src='http://0.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=50' class='avatar avatar-50 photo avatar-default' height='50' width='50' />";
		echo "<div class='comment-wrap'>";
		echo "<div class='comment-text'>";
		//Make sure the website is http.
		if (substr($result->website, 0, 7) != "http://") {
			$url = "http://" . $result->website;
		}else{
			$url = $result->website;
		}
		if($result->org_name != "")
		{
		  echo "<a href='".$url."'>".$result->org_name ."</a> is already doing this.<br />";
		}else if($result->title != "")
		{
		  echo "<a href='".$url."'>".$result->title ."</a> is already doing this.<br />";
		}else{
		  echo "Anonymous is already doing this.<br />";
		}
		
		
		echo $result->description;
		echo "<hr />";
		echo "<p class='comment-date'>Tel: " . $result->telephone. " -  Website: <a href='".$url."' target='_blank'>" . $url ."</a> - Postcode: <a href='http://fairbrum.podnosh.com/were-already-doing-it/'>".$result->postcode."</a></p>";
		echo "</div>";
	}
	echo "</li>";
	echo "</ol>";
}

function wadt_link($content)
{
  define( 'WADT_PATH', plugin_dir_url(__FILE__) );
	if(get_post_type(get_the_ID())== "post"){
		$content = $content .= "<br /><a href='#respond'><img src='".WADT_PATH."commentbubble.png' style='float:left;'></a>";
		$content .= "<a href='#wadt-form'><img src='".WADT_PATH."organisationbubble.png' style='float:right;'></a>";
		return $content;
	}else{
		return $content;
	}
}

function wadt_full_table()
{
  global $wpdb;
	global $post;
	$sql = "SELECT * FROM ". $wpdb->prefix ."wadt";
	$results = $wpdb->get_results($sql);
	echo "<h4>We're already doing it</h4>";
    echo "<ol class='commentlist'>";
	foreach($results as $result) {
		echo "<li class='comment even thread-even depth-1' id='comment-1'>";
		echo "<div class='comment-body' id='div-comment-1'>";
		$hash = md5( strtolower( trim( $result->title ) ) );
		echo "<img alt='' src='http://0.gravatar.com/avatar/".$hash."?s=50' class='avatar avatar-50 photo avatar-default' height='50' width='50' />";
		echo "<div class='comment-wrap'>";
		echo "<div class='comment-text'>";
		//Make sure the website is http.
		if (substr($result->website, 0, 7) != "http://") {
			$url = "http://" . $result->website;
		}else{
			$url = $result->website;
		}
		if (substr($result->website, 0, 7) != "http://") {
			$url = "http://" . $result->website;
		}else{
			$url = $result->website;
		}
		if($result->org_name != "")
		{
		  $name =  $result->org_name ." is already doing the recommendation ";
		}else if($result->title != "")
		{
		  $name =  $result->title ." is already doing the recommendation ";
		}else{
		  $name =  "Anonymous is already doing the recommendation ";
		}
		$current_post = get_post($result->post_id);
		echo "<a href='".$url."'>".$result->org_name ."</a> is already doing the recommendation: <a href=\"".get_permalink($current_post->ID)."\" target=\"_blank\">".$current_post->post_title."</a><br />";
		echo $result->description;
		echo "<br />Tel: " . $result->telephone. " -  Website: <a href='".$url."' target='_blank'>" . $url ."</a> - Postcode: <a href='http://fairbrum.podnosh.com/were-already-doing-it/'>".$result->postcode."</a>";
		echo "</div>";
	}
	echo "</li>";
	echo "</ol>";
}

function wadt_popular_posts_list()
{
  global $wpdb;
      $posts = $wpdb->get_results("SELECT comment_count, ID, post_title FROM $wpdb->posts ORDER BY comment_count DESC LIMIT 0 , 5");
      //$popular.='<ul>';
      foreach ($posts as $post) {
          setup_postdata($post);
          $id = $post->ID;
          $title = $post->post_title;
          $count = $post->comment_count;
          
          if ($count != 0) {
              //$popular .= '<li>';
              $popular .= '<a href="' . get_permalink($id) . '" title="' . $title . '">' . $title . '</a> <small>'.$count.' comments</small><br />';
              //$popular .= "<div class='sep'></div>";
          }
      }
      //$popular .= '</ul>';
      echo "<h4>Recommendations you're talking about</h4> ";
      echo $popular;
}


function wadt_newest_comments()
{
  global $wpdb;
  $comments = get_comments( array(
      'number'  => 5,
      'status'  => 'approve'
  ) );
  echo "<div style='margin:1em'>";
  echo "<h4>Most recent comments</h4>";
  //var_dump($comments);
  foreach ($comments as $comment)
  {
    $hash = md5( strtolower( trim( $comment->comment_author_email ) ) );
    echo "<img alt='' src='http://0.gravatar.com/avatar/".$hash."?s=50' class='avatar avatar-50 photo avatar-default' height='50' width='50' style='float:left;'/>";
    if($comment->comment_author_url != "" && isset($comment->comment_author_url))
    {
      echo "<a href='".$comment->comment_author_url."' target='_blank'>".$comment->comment_author."</a> said ".$comment->comment_content." about <a href='".get_permalink($comment->comment_post_ID)."'>".get_the_title($comment->comment_post_ID)."</a>";
    }else{
      echo $comment->comment_author." said ".$comment->comment_content." about <a href='".get_permalink($comment->comment_post_ID)."'>".get_the_title($comment->comment_post_ID)."</a>";
    }
    echo "<br /><br />";
  }
  echo "</div>";
}

function wadt_recent_tweets()
{
   $result = json_decode(file_get_contents('http://search.twitter.com/search.json?q=fairbrum&page=1&rpp=10'));
   echo "<h4>Tweets about fairbrum</h4>";
   //var_dump($result->results);
   foreach ($result->results as $tweet)
   {
     echo "<li><a href=\"http://www.twitter.com/".$tweet->from_user."\" target=\"_blank\">@".$tweet->from_user."</a> tweeted ".$tweet->text. "</li>";
   }
}

function recommendations_page()
{
	global $wpdb;
	$args = array('post_type' => 'post');
	$posts = get_posts($args);
	foreach ($posts as $post)
	{
		//echo var_dump($post) . "<br />";
		echo "<h2><a href=\"".get_permalink($post->ID)."\">  ". $post->post_title . "</a></h2>";
		echo "<p><a href=\"".get_permalink($post->ID)."\">Read the recommendation &raquo;</a></p>";
		echo "<div class='post-meta'>";
		$categories = get_the_category($post->ID);
		echo "<p>Posted in ";
		foreach($categories as $category)
		{
			echo "<a href=\"".get_category_link($category->cat_ID)."\">".$category->cat_name."</a> ";
		}
		$tags = get_the_tags($post->ID);
		if($tags)
		{
			echo "<br />Tagged ";
			//var_dump($tags);
			foreach ($tags as $tag)
			{
				echo "<a href=\"".get_tag_link($tag->term_id)."\">".$tag->name."</a>";
				echo " ";
			}
		}		
		$number_already_doing_this = "SELECT COUNT(*) FROM ".$wpdb->prefix. "wadt WHERE `post_id` = ".$post->ID;
    $adt = $wpdb->get_results($number_already_doing_this, ARRAY_N);
		echo "<a href='".get_comments_link($post->ID)."'><p style='float:right;'>".$adt[0][0]." already doing this</p></a>";
		echo "<a href='".get_comments_link($post->ID)."'><p style='float:left;'>" .$post->comment_count . " comments</p></a>";
    echo "</div>";
		echo "<div class='sep'></div>";
	}
}

function how_many()
{
global $wpdb;
global $post;
$number_already_doing_this = "SELECT COUNT(*) FROM ".$wpdb->prefix. "wadt WHERE `post_id` = ".$post->ID;
$adt = $wpdb->get_results($number_already_doing_this, ARRAY_N);
echo $adt[0][0] . " ";
}

add_shortcode('wadt_recent_tweets', 'wadt_recent_tweets');
add_shortcode('wadt_newest_comments', 'wadt_newest_comments');
add_shortcode('wadt_popular_posts_list', 'wadt_popular_posts_list');
add_shortcode( 'wadt_map', 'wadt_map' );
add_shortcode('wadt_full_table', 'wadt_full_table');
add_shortcode('recommendations_page', 'recommendations_page');

//Installation hooks
register_activation_hook(__FILE__,'wadt_install');
register_activation_hook(__FILE__, 'build_menu');

//Menu Hooks
add_action( 'admin_menu', 'build_menu' );

//Public facing hooks
add_action('comment_form_after', 'create_wadt_post_form');
add_action('wp_loaded', 'submit_wadt');
add_action('wp_head', 'add_gmaps_js');
add_action('comment_form_after', 'show_wadt');
add_filter('the_content', 'wadt_link');
?>