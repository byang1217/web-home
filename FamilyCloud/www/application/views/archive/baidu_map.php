<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<style type="text/css">
		body, html{width: 100%;height: 100%;overflow: hidden;margin:0;}
		#panorama {height: 50%;overflow: hidden;}
		#normal_map {height:50%;overflow: hidden;}
	</style>
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=FC69imYsWGM1yUMHdHQWF7Ia"></script>
	<title><?php echo $page_title;?></title>
</head>
<body>
	<div id="normal_map"></div>
	<div id="panorama"></div>
<script type="text/javascript">
	var x = <?php echo $gps_x;?>;
	var y = <?php echo $gps_y;?>;
	var ggPoint = new BMap.Point(x,y);
	
	translateCallback = function (data){
		if(data.status === 0) {
			//全景图展示
			var panorama = new BMap.Panorama('panorama');
			panorama.setPosition(data.points[0]); //根据经纬度坐标展示全景图
			panorama.setPov({heading: -40, pitch: 6});

			panorama.addEventListener('position_changed', function(e){ //全景图位置改变后，普通地图中心点也随之改变
				var pos = panorama.getPosition();
				map.setCenter(new BMap.Point(pos.lng, pos.lat));
				marker.setPosition(pos);
			});

			//普通地图展示
			var mapOption = {
					mapType: BMAP_NORMAL_MAP,
					maxZoom: 18,
					drawMargin:0,
					enableFulltimeSpotClick: true,
					enableHighResolution:true
				}
			var map = new BMap.Map("normal_map", mapOption);
			map.centerAndZoom(data.points[0], 15);
			var myIcon = new BMap.Icon("http://yangjingxuan.tk/application/views/img/map_marker_76_152.png", new BMap.Size(76,152));
			var marker=new BMap.Marker(data.points[0], {icon:myIcon});
			marker.enableDragging();
			map.addOverlay(marker);  

			marker.addEventListener('dragend',function(e){
				panorama.setPosition(e.point); //拖动marker后，全景图位置也随着改变
				panorama.setPov({heading: -40, pitch: 6});}
			);
		}
	}

	setTimeout(function(){
		var convertor = new BMap.Convertor();
		var pointArr = [];
		pointArr.push(ggPoint);
		convertor.translate(pointArr, 1, 5, translateCallback)
	}, 1000);




</script>

</body>
</html>
