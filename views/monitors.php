<?php
require_once("../../../includes/config.php");
require_once("../includes/config.php");
require_once("../../../includes/database.php");
require_once("../../../includes/functions.php");

# seyi_code start
ini_set( "session.name", "ZMSESSID" );
session_start();
# seyi_code end


$mid = $_REQUEST['mid'];
$groupName = $_REQUEST['groupName'];
$bandwidth = $_COOKIE['zmBandwidth'];
if ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ){
 $protocol = 'https';
} else {
 $protocol = 'http';
}
define( "ZM_BASE_URL", $protocol.'://'.$_SERVER['HTTP_HOST'] );
?>
 <ul id="monitors" class="clearfix">
<?php
if ($mid) {
 $monitors = dbFetchAll( "select Id, Name, Width, Height from Monitors where Id = " . $mid . " order by Sequence asc" );
 foreach( $monitors as $monitor ){
  displayMonitor($monitor, $bandwidth);
 }
} elseif ($groupName){ # If a list of monitors
 $query = "select MonitorIds from Groups where Name = '".$groupName."'"; // Get all of the mids in a group
 $result = mysql_query($query); // Get all of the mids in a group
 $row = mysql_result($result, 0);
  $mids = explode(",", $row); # Put them into an array
 foreach ($mids as $mid){ # Foreach item in the array
  $query = "select Id, Name, Width, Height from Monitors where Id = ".$mid;
  foreach(dbFetchAll($query) as $monitor){ # Query the database
   displayMonitor($monitor, $bandwidth); # And call displayMonitor with the result
 }
}
} else {
 $monitors = dbFetchAll( "select Id, Name, Width, Height from Monitors order by Sequence asc" );
 foreach( $monitors as $monitor ){
  displayMonitor($monitor, $bandwidth);
 }
}
?>
 </ul>
 
<?php
function displayMonitor($monitor, $bandwidth){
	if (!defined(ZM_WEB_DEFAULT_SCALE)) {
		$scale = 40;
	} else {
		$scale = ZM_WEB_DEFAULT_SCALE;
	}
	
	if ( ZM_WEB_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
		$streamMode = "mpeg";
		$streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT, "buffer=".$monitor['StreamReplayBuffer'] ) );
	}
	elseif ( canStream() ) {
		$streamMode = "jpeg";
		$streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor['StreamReplayBuffer'] ) );
	}
	else {
		$streamMode = "single";
		$streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale ) );
	}

	$width = ($monitor['Width'] * ('.' . $scale) + 20);
	$display_name = strlen($monitor['Name'])>10 ? substr($monitor['Name'],0,10).'...' : $monitor['Name'];
?>
	<li id="monitor_<?php echo $monitor['Id'] ?>" style="width:<?php echo $width ?>px;">
		<div class="mon_header">
			<div style="float:left;overflow:hidden;width:100px;"><h3 style="display:inline;"><?php echo $display_name ?></h3></div>
			<div class="right">
				<div class="spinner"></div>
				<div class="minimize"><img src="skins/modern/graphics/minimize.png" style="width:15px;" alt="minimize" /></div>
				<div class="maximize" url="?view=watch&amp;mid=<?= $monitor['Id']; ?>"><img src="skins/modern/graphics/maximize.png" style="width:15px;" alt="maximize" /></div>


			</div>
			<br style="clear:both;" />
		</div>
		<div class="mon">
			<a rel="monitor" href="?view=watch&amp;mid=<?= $monitor['Id']; ?>" title="<?= $monitor['Name']; ?>">
			<?php
				//$name = $monitor['Name'] . "_live";
				//outputImageStill( "$name", $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] ); 
				// output image

				if ( $streamMode === "mpeg" ) outputVideoStream( 'liveStream'.$monitor['Id'], $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), ZM_MPEG_LIVE_FORMAT, $monitor['Name'] );
				elseif ( $streamMode == "jpeg" ) {
					if ( canStreamNative() ) outputImageStream( 'liveStream'.$monitor['Id'], $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );
					elseif ( canStreamApplet() ) outputHelperStream( 'liveStream'.$monitor['Id'], $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );
				}
				else outputImageStill( 'liveStream'.$monitor['Id'], $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );
			?>
			</a>
		</div>
		<div class="monfooter"></div>
</li>
<?php } ?>




<?php
function __displayMonitor($monitor, $bandwidth){
 if (!defined(ZM_WEB_DEFAULT_SCALE)) {
  $scale = 40;
 } else {
  $scale = ZM_WEB_DEFAULT_SCALE;
 } if ($bandwidth == 'high') {
   if ( ZM_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
    $streamMode = "mpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT, "buffer=".$monitor['StreamReplayBuffer'] ) );
} if ( canStream() ) {
    $streamMode = "jpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor['StreamReplayBuffer'] ) );
  }
 }
 if (($bandwidth == 'low' || $bandwidth == "medium" || $bandwidth == "" || !($bandwidth))) {
  $streamSrc = getStreamSrc( array( "mode=single", "monitor=".$monitor['Id'], "scale=".$scale ) );
 }
 $width = ($monitor['Width'] * ('.' . $scale) + 20);
?>
<li id="monitor_<?php echo $monitor['Id'] ?>" style="width:<?php echo $width ?>px;">
 <div class="mon_header">
  <h3 style="display:inline;"><?php echo $monitor['Name'] ?></h3>
  <div class="right">
   <div class="spinner"></div>
   <div class="minimize"><img src="skins/modern/graphics/minimize.png" style="width:15px;" alt="minimize" /></div>
   <div class="maximize" url="?view=watch&amp;mid=<?= $monitor['Id']; ?>" ><img src="skins/modern/graphics/maximize.png" style="width:15px;" alt="maximize" /></div>

  </div>
 </div>
 <div class="mon">
  <a rel="monitor" href="?view=watch&amp;mid=<?= $monitor['Id']; ?>" title="<?= $monitor['Name']; ?>">
   <?php
    $name = $monitor['Name'] . "_live";
    outputImageStill( "$name", $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );   ?>
  </a>
 </div>
 <div class="monfooter">
 </div>
</li>
<?php } ?>
