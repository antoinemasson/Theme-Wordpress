<h5>carte</h5>
<div id="map" style="height:380px;width:500px"></div>
<script type="text/javascript">
	map = L.map('map', {
		center: [46, 0.8],
		zoom: 5
	});
	L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
		maxZoom: 14,
		attribution: ''
	}).addTo(map);

	<?php echo getMarkerList(); ?>

	map.on('popupopen', function(e){
		var post_id = e.popup.post_id;
		var nonce = '<?php echo wp_create_nonce("popup_content"); ?>';
		jQuery.post("<?php echo admin_url('admin-ajax.php') ?>",{
			action : 'popup_content', post_id: post_id, nonce: nonce
		}, function(response){
			console.log("resp", response);
			e.popup.setContent(response);
		});
	});
</script>
