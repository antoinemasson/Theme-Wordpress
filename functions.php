<?php

//add_action('pre_get_posts','display_concerts');

function display_concerts($query){
	
	if ($query->is_front_page() && $query->is_main_query())
	{
		//10 dernières années
		$query->set('date_query', array('year' => getdate()['year']-10, 'compare' => '>='));

		//le lieu
		$query->set('meat_query', array(array('key'=>'wpcf-lieu', 'value' => false, 'type'=>BOOLEAN)));
		
		//qui possède une image à la une
		$query->set('meta_query', array(array('key'=>'_thumbnail_id', 'compare'=>'EXISTS')));
		
		return;
	}

}

function get_concert($query){
	
		$query->set('date_query', array('year' => 2006, 'compare' => '>=', 'year' => 2008, 'compare' => '<='));
	
		return;
}





function geolocalize($post_id){
	if(wp_is_post_revision($post_id))
		return ;
	$post=get_post($post_id);
	if(!in_array($post->post_type,array('concert')))
		return ;
	$lieu=get_post_meta($post_id,'wpcf-lieu',true);
	if(empty($lieu))
		return;
	$lat=get_post_meta($post_id,'lat',true);
	if(empty($latlon)){
		$address= $lieu.',France';
		$result=doGeolocation($address);
		if(false===$result)
			return ;
		try{
			
			$location=$result[0]['geometry']['location'];
			add_post_meta($post_id,'lat',$location["lat"]);
			add_post_meta($post_id,'lng',$location["lng"]);
			
		}catch(Exception $e){
			return ;
		}
	}
}
		
		
add_action('save_post','geolocalize');


function doGeolocation($address){
	
		$aContext = array(
			'http' => array(
				'proxy' => 'wwwcache.univ-orleans.fr:3128', // This needs to be the server and the port of the NTLM Authentication Proxy Server.
				'request_fulluri' => True,
				),
			);
		$cxContext = stream_context_create($aContext);
		// Now all file stream functions can use this context.
		//		$sFile = file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address), False, $cxContext);
		


		//$url="http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address);
	
	if ( $json=file_get_contents("http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address), False, $cxContext)){
		$data=json_decode($json,TRUE);
		if ( $data['status']=="OK" ){
			return $data['results'];
		}
	}

	return false;
}

function load_scripts(){
	
	if(! is_post_type_archive('concert') && ! is_post_type_archive('action'))
		return;
		
	wp_register_script(
		'leaflet-js',
		'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js')
	;
	wp_enqueue_script('leaflet-js');
	
	wp_register_style(
		'leaflet-css',
		'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css?ver=3.7.1')
	;
	wp_enqueue_style('leaflet-css');

}

function getMarkerList($post_type="concert")
{
	$results=getPostWithLatLon($post_type);
	//var_dump($results);
	$array=array(); 
	foreach($results as $result)
	{
		$array[]="var marker_".$result->ID."=L.marker([".$result->lat.",".$result->lng."]).addTo(map);";
		$array[]="var popup_".$result->ID."=L.popup().setContent('". addslashes($result->post_title) ."');";
		$array[]="popup_".$result->ID.".post_id=".$result->ID.";";
		$array[]="marker_".$result->ID.".bindPopup(popup_".$result->ID.");";
	}
		
	return implode(PHP_EOL,$array);
}

function getPostWithLatLon($post_type = "concert")
{
	global $wpdb;
	$query = "
		SELECT ID, post_title, p1.meta_value as lat, p2.meta_value as lng
		FROM wp_archetsposts, wp_archetspostmeta as p1, wp_archetspostmeta as p2
		WHERE wp_archetsposts.post_type = 'concert'
		AND p1.post_id = wp_archetsposts.ID
		AND p2.post_id = wp_archetsposts.ID
		AND p1.meta_key = 'lat'
		AND p2.meta_key = 'lng'";
		
	return $wpdb->get_results($query);
}	

add_action('wp_enqueue_scripts','load_scripts');

function get_content() {
	if( !wp_verify_nonce($_REQUEST['nonce'], 'popup_content')) {
		exit("d'où vient cette requête ?");
	}
	else {
		$post_id = $_REQUEST['post_id'];

		$post = get_post($post_id, ARRAY_A);
		
		$post_title = "<h4>".$post['post_title']."</h4>";

		if (!empty($post['post_content'])) {
			$post_content = $post['post_content'];
		}
		else {
			$post_content = "";
		}

		echo $post_title . substr($post_content, 0, -1);
	}
}


add_action('wp_ajax_popup_content', 'get_content');
add_action('wp_ajax_nopriv_popup_content', 'get_content');


/*


function get_concert_sans_lieux($query){
	
		$query->set('date_query', array('lieux' => "", 'compare' => '=='));
	
		return;
}

function get_action_sans_pays($query){
	
		$query->set('date_query', array('pays' => "", 'compare' => '=='));
	
		return;
}*/


?>
