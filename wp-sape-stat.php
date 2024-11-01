<?php
/*
Plugin Name: WP Sape Stat
Plugin URI: http://seogad.ru/
Version: 1.2
Author: <a href="http://seogad.ru/">Seogad</a>
Description: This plugin displays some useful statistics from your <a href="http://www.sape.ru/r.eMotfFWAlO.php">Sape.ru</a> account. All in one page with filters. Widget included.
*/


// define the plugin directory


if (!defined ('WP_PLUGIN_URL'))
	define ('WP_PLUGIN_URL', get_option ('siteurl'). '/wp-content/plugins');
if (!defined ('WP_PLUGIN_DIR'))
	define ('WP_PLUGIN_DIR', ABSPATH. 'wp-content/plugins' );
if (!defined('WP_CONTENT2_URL')) {
	define('WP_CONTENT2_URL', get_option('siteurl').'/wp-content');
}

if (!defined('WP_PLUGIN2_URL')) {
	define('WP_PLUGIN2_URL', str_replace("\\","/",WP_CONTENT2_URL.'/plugins/wp-sape-stat/'));
}

function load_locale() {
		// Here we manually fudge the plugin locale as WP doesnt allow many options
		$locale = get_locale();
		if( empty( $locale ) )
			$locale = 'ru_RU';

		$mofile = dirname( __FILE__ )."/wp-sape-stat-$locale.mo";
		load_textdomain( "WPSapeStat", $mofile );
	};



if (!class_exists("WPSapeStat")) {

	class WPSapeStat {

		var $adminOptionsName = "WPSapeStatAdminOptions";

		function WPSapeStat() { //constructor

		} //End function WPSapeStat


		function get_sape_stat() {
	global $wpdb;
	$table_name_today = $wpdb->prefix."sapestat_today";
	$ssOptions = $this->getAdminOptions();
	if (!$ssOptions['login'] || !$ssOptions['password']) return false;

	$dnows = date('d.m.Y H:i',mktime());
	
	$upd = false;
	if (!(get_option('WPSapeStat_datetimes'))) {
		update_option('WPSapeStat_datetimes', $dnows);
	} else {
		if (!(get_option('WPSapeStat_tupdate'))) update_option('WPSapeStat_tupdate', 180);
		$before = strtotime(get_option('WPSapeStat_datetimes'));
		$now = strtotime($dnows);
		$razn = ($now-$before)/60;
		
		/* Если времени с последнего обновления статистики меньше чем указано в опциях, выводим старую стату */
		if ($razn<get_option('WPSapeStat_tupdate')) return true;
	}	

	require_once(dirname(__FILE__).'/php/xml.php');

	$sapelogin = $ssOptions['login'];
	$sapepassword = md5("".$ssOptions['password']."");

	$ljClient = new IXR_Client('www.sape.ru', '/api_xmlrpc.php');
	$ljClient->query('sape.login', $sapelogin, $sapepassword, true);

	/*Делаем выборку по всем сайтам аккаунта*/
	$ljClient->query('sape.get_sites');
	$ljResponse = $ljClient->getResponse();
	



	//echo '<pre>';print_r($ljResponse);echo '</pre>';die;

	foreach($ljResponse as $k) {

	global $wpdb;
	$wpdb->sapestat_today = $wpdb->prefix.'sapestat_today';

		$today_sape_id=$k['id'];
		$today_url=$k['url'];
		$today_cy=$k['cy'];
		$today_pr=$k['pr'];
		$today_category_id=$k['category_id'];
		//$today_date_created=$k['date_created'];
		//$today_date_last_mpp_changed=$k['date_last_mpp_changed'];
		$today_status=$k['status'];
		$today_comment_admin=$k['comment_admin'];
		$today_domain_level=$k['domain_level'];
		$today_flag_auto=$k['flag_auto'];
		$today_mpp_1=$k['mpp_1'];
		$today_mpp_2=$k['mpp_2'];
		$today_mpp_3=$k['mpp_3'];
		$today_flag_blocked_in_yandex=$k['flag_blocked_in_yandex'];
		$today_flag_hide_url=$k['flag_hide_url'];
		$today_flag_not_for_sale=$k['flag_not_for_sale'];
		$today_amount_today=round($k['amount_today'],2);
		$today_amount_yesterday=round($k['amount_yesterday'],2);
		$today_amount_total=round($k['amount_total'],2);
		$today_in_yaca=$k['in_yaca'];
		$today_in_dmoz=$k['in_dmoz'];
		$today_nof_yandex=$k['nof_yandex'];
		$today_nof_google=$k['nof_google'];

	$data_array = array(
	'sape_id' => ''.$today_sape_id.'',
	'url' => ''.$today_url.'',
	'cy' => ''.$today_cy.'',
	'pr' => ''.$today_pr.'',
	'category_id' => ''.$today_category_id.'',
	'last_update_time' => ''.time().'',
	//'date_created' => ''.$today_date_created.'',
	//'date_last_mpp_changed' => ''.$today_date_last_mpp_changed.'',
	'status' => ''.$today_status.'',
	'comment_admin' => ''.$today_comment_admin.'',
	'domain_level' => ''.$today_domain_level.'',
	'flag_auto' => ''.$today_flag_auto.'',
	'mpp_1' => ''.$today_mpp_1.'',
	'mpp_2' => ''.$today_mpp_2.'',
	'mpp_3' => ''.$today_mpp_3.'',
	'flag_blocked_in_yandex' => ''.$today_flag_blocked_in_yandex.'',
	'flag_hide_url' => ''.$today_flag_hide_url.'',
	'flag_not_for_sale' => ''.$today_flag_not_for_sale.'',
	'amount_today' => ''.$today_amount_today.'',
	'amount_yesterday' => ''.$today_amount_yesterday.'',
	'amount_total' => ''.$today_amount_total.'',
	'in_yaca' => ''.$today_in_yaca.'',
	'in_dmoz' => ''.$today_in_dmoz.'',
	'nof_yandex' => ''.$today_nof_yandex.'',
	'nof_google' => ''.$today_nof_google.'');
	
	$check_sape_id = $wpdb->get_row("SELECT * FROM $wpdb->sapestat_today WHERE sape_id = $today_sape_id ", ARRAY_A);

	
	if ($check_sape_id['sape_id'] == $today_sape_id) { 

	$where_array = array('url' => ''.$today_url.'');
	$wpdb->update( $table_name_today , $data_array, $where_array );

	} else {

	$wpdb->insert( $table_name_today , $data_array );
	}
	
	
	unset($sites);
	$sites[0]=0;
	$ljClient->query('sape.get_site_money_stats',$today_sape_id,0,0,0);
	$ljResponse = $ljClient->getResponse();

	foreach($ljResponse as $r) {

	global $wpdb;
	$wpdb->sapestat = $wpdb->prefix.'sapestat';
	$table_name_stat = $wpdb->prefix."sapestat";

	unset($news); 
	foreach($r['date_logged'] as $v) $news[]=$v;
	$year=$news[0];
	$month=$news[1];
	$day=$news[2];
	$day+=0;
	$sites[$day]=round($r['sum'],2);
	$url = $today_url;
	$stat_sape_id = $today_sape_id;

	$data_array2 = array(
	'sape_id' => ''.$stat_sape_id.'',
	'url' => ''.$url.'',
	'year' => ''.$year.'',
	'month' => ''.$month.'',
	'day' => ''.$day.'',
	'sum' => ''.$sites[$day].'');


	$check_sites = $wpdb->get_row("SELECT * FROM $wpdb->sapestat WHERE sape_id = $stat_sape_id 
	AND year = $year AND month = $month AND day = $day ", ARRAY_A);

	if ($check_sites['sape_id'] == $stat_sape_id && $check_sites['year'] == $year && $check_sites['month'] == $month && $check_sites['day'] == $day )
	
	{} else {

	$wpdb->insert( $table_name_stat , $data_array2 );
	}



	}
	}

	update_option('WPSapeStat_datetimes', $dnows);	
	return true;
}




		function init() {
			$this->getAdminOptions();
		}//End init function


		//Returns an array of admin options
		function getAdminOptions() {
			$sapestatAdminOptions = array(
			'sapestat_db_version' => '1.0',
			'login' => '',
			'password' => '',
			'show_yaca' => 'true',
			'show_pr' => 'true', 
			'show_yapages' => 'true',
			'show_gpages' => 'true');
			$ssOptions = get_option($this->adminOptionsName);
			if (!empty($ssOptions)) {
				foreach ($ssOptions as $key => $option)
				$sapestatAdminOptions[$key] = $option;
			}
			update_option($this->adminOptionsName, $sapestatAdminOptions);
			return $sapestatAdminOptions;


		}//End function getAdminOptions
		

		function WPSapeStatPage() {
			$ssOptions = $this->getAdminOptions();
			$d = $this->get_sape_stat();
			global $wpdb;
			
			
			$wpdb->sapestat_today = $wpdb->prefix.'sapestat_today';
			$wpdb->sapestat = $wpdb->prefix.'sapestat';

			if ($ssOptions['password'] != "" && $ssOptions['login'] != "") { 


			if (isset($_GET['act'])) {
					$act = $_GET['act'];
				};
			if(strlen($act)<1) $act="today";

			function dat($data) {
			$q[]="".__('January','WPSapeStat')."";
			$q[]="".__('February','WPSapeStat')."";
			$q[]="".__('March','WPSapeStat')."";
			$q[]="".__('April','WPSapeStat')."";
			$q[]="".__('May','WPSapeStat')."";
			$q[]="".__('Juin','WPSapeStat')."";
			$q[]="".__('July','WPSapeStat')."";
			$q[]="".__('August','WPSapeStat')."";
			$q[]="".__('September','WPSapeStat')."";
			$q[]="".__('October','WPSapeStat')."";
			$q[]="".__('November','WPSapeStat')."";
			$q[]="".__('December','WPSapeStat').""; 
			// ---- считываем месяц
			$m=date('m',$data);
			if ($m=="01") $m=1;
			if ($m=="02") $m=2;
			if ($m=="03") $m=3;
			if ($m=="04") $m=4;
			if ($m=="05") $m=5;
			if ($m=="06") $m=6;
			if ($m=="07") $m=7;
			if ($m=="08") $m=8;
			if ($m=="09") $m=9;


			// ---- считываем число
			$c=date('j',$data);


			$year=date('Y',$data);
			// - извлекаем значениечение месяца
			$mesyac = $q[$m];
			$datas="$mesyac $year";
			return $datas;
			}
			
			?>	<style type="text/css">
				.wpsapestat_table td {
				font-size:0.8em !important;
				padding:2px;
				}
				.wpsapestat_table td sup {
				font-size:1em !important;
				}
				</style>
				
				<div class=wrap>
				<h2><?php _e('Sape.ru Statistics','WPSapeStat'); ?></h2>
					<?php if ($act=="today") { 
					$today_stats = $wpdb->get_results("SELECT * FROM $wpdb->sapestat_today ", ARRAY_A);
					?>
					<h3><?php _e('Today & Yesterday','WPSapeStat'); ?>
					<a href="tools.php?page=wpsapestat&act=month"><?php _e('Month','WPSapeStat'); ?></a>
					<a href="tools.php?page=wpsapestat&act=year"><?php _e('Year','WPSapeStat'); ?></a> <a href="tools.php?page=wpsapestat&act=all"><?php _e('Total','WPSapeStat'); ?></a></h3>
					<table cellspacing="2" class="wpsapestat_table">
					<tr class="wpsapestat_table_header">
					<td><?php _e('Status','WPSapeStat'); ?></td>
					<td><?php _e('URL','WPSapeStat'); ?></td>
					<td><?php _e('Yesterday','WPSapeStat'); ?></td>
					<td><?php _e('Today','WPSapeStat'); ?></td>
					<?php if ($ssOptions['show_yaca'] == "true") { ?><td><?php _e('CY','WPSapeStat'); ?></td><?php }?>
					<?php if ($ssOptions['show_pr'] == "true") { ?><td><?php _e('PR','WPSapeStat'); ?></td><?php }?>
					<?php if ($ssOptions['show_yapages'] == "true") { ?><td><?php _e('Yandex pages','WPSapeStat'); ?></td><?php } ?>
					<?php if ($ssOptions['show_gpages'] == "true") { ?><td><?php _e('Google pages','WPSapeStat'); ?></td><?php } ?>
					</tr>
						<?php $today=0;
						$yest=0;

						/*Делаем выборку по всем сайтам аккаунта*/
						$tstats = $today_stats;

						foreach($tstats as $key=>$k) {
						
						$tod=round($k['amount_today'],2);
						$yes=round($k['amount_yesterday'],2);
						$status = $k['status'];
						$yapages = $k['nof_yandex'];
						$gpages = $k['nof_google'];
						$yaca = $k['cy'];
						$pr = $k['pr'];
						$url=str_replace("http://","",$k['url']); 
						/*Если итоговая сумма больше нуля, то отображаем..*/
						if($tod>0 || $yes>0) {
						if($tod>=$yes) $back="92FF84"; else $back="FF7474"; ?>
						<tr>
						<td><?php echo $status; ?></td>
						<td><a href="http://<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></td>
						<td style="background: #92FF84"><?php echo $yes; ?></td>
						<td style="background: #<?php echo $back; ?>"><?php echo $tod ?></td>
						<?php if ($ssOptions['show_yaca'] == "true") { ?><td><?php echo $yaca; ?></td><?php } ?>
						<?php if ($ssOptions['show_pr'] == "true") { ?><td><?php echo $pr; ?></td><?php } ?>
						<?php if ($ssOptions['show_yapages'] == "true") { ?><td><?php echo $yapages; ?></td><?php } ?>
						<?php if ($ssOptions['show_gpages'] == "true") { ?><td><?php echo $gpages; ?></td><?php } ?>
						</tr>  <?php ; 
						} $today+=$tod; $yest+=$yes;
						$yaca_total+=$yaca; $pr_total+=$pr; $yapages_total+=$yapages; $gpages_total+=$gpages;
						} 

						/*Итоговая статистика*/
						if($today>=$yest) $back="92FF84"; else $back="FF7474";
						$mid=$today-$yest; 
						if($mid>0) $ss="<sup style='font-size: 8pt; color: black;'>+$mid</sup>"; 
						else $ss="<sup style='font-size: 8pt; color: black;'>-$mid</sup>"; ?>
						<tr>
						<td></td>
						<td><?php _e('All sites','WPSapeStat'); ?></td>
						<td style="background: #92FF84"><?php echo $yest; ?></td>
						<td style="background: #<?php echo $back; ?>" ><?php echo $today; echo $ss; ?></td>
						<?php if ($ssOptions['show_yaca'] == "true") { ?><td><?php echo $yaca_total; ?></td><?php } ?>
						<?php if ($ssOptions['show_pr'] == "true") { ?><td><?php echo $pr_total; ?></td><?php }?>
						<?php if ($ssOptions['show_yapages'] == "true") { ?><td><?php echo $yapages_total; ?></td><?php } ?>
						<?php if ($ssOptions['show_gpages'] == "true") { ?><td><?php echo $gpages_total; ?></td><?php } ?>
						</tr>
						</table>
						
					<?php } elseif ($act=="month") { 
					$mounth=$_GET['m'];
					$tomounth=date("n");
					if(strlen($mounth)<1) $mounth=$tomounth;

					$year=$_GET['y'];
					$toyear=date("Y");
					if(strlen($year)<1) $year=$toyear;

					/*Подсчитываем число дней в месяце*/
					$cday=date('t',mktime(0, 0, 0, $mounth, date("d"), $year));
					$today=date("j");
					if($year==$toyear && $mounth==$tomounth && $today<$cday) $cday=$today-1;

					if($mounth>1 && $mounth<12) {$m1=$mounth-1; $y1=$year; $m2=$mounth+1; $y2=$year;}
					if($mounth==1){$m1=12; $y1=$year-1; $m2=$mounth+1; $y2=$year;}
					if($mounth==12) {$m1=$mounth-1; $y1=$year; $m2=1; $y2=$year+1;}

					$dd=dat(mktime(0, 0, 0, $mounth, 0, $year));
					$month_sites = $wpdb->get_results("SELECT DISTINCT sape_id, url FROM $wpdb->sapestat 
					WHERE year = $year AND month = $mounth ", ARRAY_A); 
					


					?>

					<h3><a href="tools.php?page=wpsapestat&act=today"><?php _e('Today & Yesterday','WPSapeStat'); ?></a>
					<?php _e('Month','WPSapeStat'); ?>
					<a href="tools.php?page=wpsapestat&act=year"><?php _e('Year','WPSapeStat'); ?></a> <a href="tools.php?page=wpsapestat&act=all"><?php _e('Total','WPSapeStat'); ?></a></h3>
					<p><?php echo $dd; ?></p>
					<p><a href="tools.php?page=wpsapestat&act=month&m=<?php echo $m1; ?>&y=<?php echo $y1; ?>">
					<?php _e('&laquo; Previous month','WPSapeStat'); ?></a>
					<a href="tools.php?page=wpsapestat&act=month&m=<?php echo $m2; ?>&y=<?php echo $y2; ?>">
					<?php  _e('Next month &raquo;','WPSapeStat'); ?></a></p>
					<?php if (count($month_sites) != 0 ) { ?>

					<table cellspacing="2" class="wpsapestat_table">
					<tr class="wpsapestat_table_header"><td><?php _e('URL/Date','WPSapeStat'); ?></td>
					<?php for($i=1; $i<=$cday; $i++){ ?>
					<td><?php echo $i; ?></td><?php ;} ?>
					<td><?php _e('Total','WPSapeStat'); ?></td></tr>


<?php 

foreach($month_sites as $k){
$kid=$k['sape_id'];

unset($sites);
$sites[0]=0;
$month_stats = $wpdb->get_results("SELECT * FROM $wpdb->sapestat 
WHERE sape_id = $kid AND year = $year AND month = $mounth ", ARRAY_A);
foreach($month_stats as $r) {
unset($news);
foreach($r as $v) $news[]=$v;
$inc=$news[5];
$inc+=0;
$sites[$inc]=round($r['sum'],2); 
}

/*Если сумма за месяц больше нуля...*/
if(array_sum($sites)>0){
$url=str_replace("http://","",$k['url']);  ?>
<tr><td><a href="http://<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></td>
<?php
$sum=0;
for($i=1; $i<=$cday; $i++){

$is=$sites[$i]; 
if(!isset($is)) $is=0;
if($is>=$is2) $back="92FF84"; else $back="FF7474";
$is2=$is; ?>

<td style="background: #<?php echo $back; ?>"><?php echo $is; ?></td>
<?php $sum+=$is;
$days[$i]+=$is;
} ?>

<td style="background: #92FF84"><?php echo $sum; ?></td></tr>
<?php flush();
}

}

/*Общая статистика*/
?>
<tr><td><?php _e('All sites','WPSapeStat'); ?></td>
<?php $allsum=0;
for($i=1; $i<=$cday; $i++){ $v=$days[$i];
if($v>=$v2) $back="92FF84"; else $back="FF7474";
$v2=$v; ?>
<td style="background: #<?php echo $back; ?>"><?php echo $v;?></td>

<?php $allsum+=$v;
}
?>
<td style="background: #92FF84"><?php echo $allsum; ?></td>
</tr></table>


<?php /*Дополнительная полезная информация :)*/
$mid=round($allsum/$cday,2);

$arr=rsort($days);
$max=round($days[0],2);

$arr2=sort($days);
$min=round($days[0],2); ?>
					<p><strong><?php _e('Day income','WPSapeStat'); ?></strong><br />
					<?php _e('Middle:','WPSapeStat'); ?> <?php echo $mid; ?><br />
					<?php _e('Maximal:','WPSapeStat'); ?> <?php echo $max; ?><br />
					<?php _e('Minimal:','WPSapeStat'); ?> <?php echo $min; ?></p>




					<?php } elseif (count($month_sites) == 0 ) { ?>
					<p><?php _e("You haven't earned anything with sape.ru this month!","WPSapeStat"); ?></p>
					<?php } } elseif ($act=="year") {
					$year=$_GET['y'];
					$toyear=date("Y");
					if(strlen($year)<1) $year=$toyear;

					/*Подсчитываем число месяцев в году*/
					$cday=12;
					$mounth=date("n");
					if($year==$toyear && $mounth<$cday) $cday=$mounth;
					$y1=$year-1;
					$y2=$year+1;
					$y3=$toyear+1;

					$year_sites = $wpdb->get_results("SELECT DISTINCT sape_id, url FROM $wpdb->sapestat 
					WHERE year = $year ", ARRAY_A);
					 ?>

					<h3><a href="tools.php?page=wpsapestat&act=today"><?php _e('Today & Yesterday','WPSapeStat'); ?></a>
					<a href="tools.php?page=wpsapestat&act=month"><?php _e('Month','WPSapeStat'); ?></a>
					<?php _e('Year','WPSapeStat'); ?> <a href="tools.php?page=wpsapestat&act=all"><?php _e('Total','WPSapeStat'); ?></a></h3>
					<p><?php echo $year; ?></p>
					<p><a href="tools.php?page=wpsapestat&act=year&y=<?php echo $y1; ?>">&laquo; <?php echo $y1; ?></a>
					<?php if ($y3 != $y2) { ?>
					<a href="tools.php?page=wpsapestat&act=year&y=<?php echo $y2; ?>"><?php echo $y2; ?> &raquo;</a>
					<?php }; ?></p>
					<?php if (count($year_sites) != 0 ) { 
					if($year==$toyear && $mounth<$cday) $cday=$mounth; $y1=$year-1; ?>
					
					<table cellspacing="2" class="wpsapestat_table">
					<tr class="wpsapestat_table_header"><td><?php _e('URL/Month','WPSapeStat'); ?></td>
					<?php 
					$q[]="";
					$q[]="".__('January','WPSapeStat')."";
					$q[]="".__('February','WPSapeStat')."";
					$q[]="".__('March','WPSapeStat')."";
					$q[]="".__('April','WPSapeStat')."";
					$q[]="".__('May','WPSapeStat')."";
					$q[]="".__('Juin','WPSapeStat')."";
					$q[]="".__('July','WPSapeStat')."";
					$q[]="".__('August','WPSapeStat')."";
					$q[]="".__('September','WPSapeStat')."";
					$q[]="".__('October','WPSapeStat')."";
					$q[]="".__('November','WPSapeStat')."";
					$q[]="".__('December','WPSapeStat').""; 
					?>
					<?php for($i=1; $i<=$cday; $i++){ ?>
					<td><?php echo $q[$i]; ?></td><?php ;} ?>
					<td><?php _e('Total','WPSapeStat'); ?></td></tr>

<?php

/*И статистику за год для каждого из них*/
foreach($year_sites as $k){
$kid=$k['sape_id'];

unset($sites);
$sites[0]=0;
$year_stats = $wpdb->get_results("SELECT * FROM $wpdb->sapestat 
WHERE sape_id = $kid AND year = $year ", ARRAY_A);
foreach($year_stats as $r) {
unset($news);
foreach($r as $v) $news[]=$v;
$inc=$news[4];
$inc+=0;
$sites[$inc]+=round($r['sum'],2); 
}

/*Если сумма за год больше нуля...*/
if(array_sum($sites)>0){
$url=str_replace("http://","",$k['url']); ?>
<tr><td><a href="http://<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></td>
<?php $sum=0;
for($i=1; $i<=$cday; $i++){

$is=$sites[$i]; 
if(!isset($is)) $is=0;
if($is>=$is2) $back="92FF84"; else $back="FF7474";
$is2=$is; ?>
<td style="background: #<?php echo $back; ?>"><?php echo $is; ?></td>
<?php $sum+=$is;
$days[$i]+=$is;
} ?>
<td style="background: #92FF84"><?php echo $sum; ?></td></tr>
<?php flush();
}

}

/*Общая статистика*/
?>
<tr><td><?php _e('All sites','WPSapeStat'); ?></td>

<?php $allsum=0;
for($i=1; $i<=$cday; $i++){ $v=$days[$i];
if($v>=$v2) $back="92FF84"; else $back="FF7474";
$v2=$v; ?>
<td style="background: #<?php echo $back; ?>"><?php echo $v;?></td>
<?php $allsum+=$v; } ?>
<td style="background: #92FF84"><?php echo $allsum; ?></td>
</tr></table>

<?php /*Дополнительная полезная информация :)*/
$mid=round($allsum/$cday,2);

$arr=rsort($days);
$max=round($days[0],2);

$arr2=sort($days);
$min=round($days[0],2); ?>

					<p><strong><?php _e('Month income','WPSapeStat'); ?></strong><br />
					<?php _e('Middle:','WPSapeStat'); ?> <?php echo $mid; ?><br />
					<?php _e('Maximal:','WPSapeStat'); ?> <?php echo $max; ?><br />
					<?php _e('Minimal:','WPSapeStat'); ?> <?php echo $min; ?></p>



					<table cellspacing="5" class="wpsapestat_table">
					<tr class="wpsapestat_table_header">

					</tr>
					</table>
					<?php } elseif (count($year_sites) == 0 ) { ?>
					<p><?php _e("You haven't earned anything with sape.ru this year!","WPSapeStat"); ?></p>
					<?php } } elseif ($act=="all") { ?>
					<h3><a href="tools.php?page=wpsapestat&act=today"><?php _e('Today & Yesterday','WPSapeStat'); ?></a>
					<a href="tools.php?page=wpsapestat&act=month"><?php _e('Month','WPSapeStat'); ?></a>
					<a href="tools.php?page=wpsapestat&act=year"><?php _e('Year','WPSapeStat'); ?></a>
					<?php _e('Total','WPSapeStat'); ?></h3>
					<table cellspacing="2" class="wpsapestat_table">
					<tr class="wpsapestat_table_header"><td><?php _e('URL','WPSapeStat'); ?></td>
					<td><?php _e('Total','WPSapeStat'); ?></td></tr>

<?php

$allsum=0;
/*Получаем инфу о сайтах*/
$month_sites = $wpdb->get_results("SELECT DISTINCT sape_id, url FROM $wpdb->sapestat", ARRAY_A); 

/*И статистику за все время для каждого из них*/
foreach($month_sites as $k){
$kid=$k['sape_id'];

unset($sites);
$site=0;
$all_stats = $wpdb->get_results("SELECT * FROM $wpdb->sapestat 
WHERE sape_id = $kid ", ARRAY_A);
foreach($all_stats as $r) $sites+=round($r['sum'],2); 

/*Если сумма больше нуля...*/
if($sites>0){
$url=str_replace("http://","",$k['url']); ?>
<tr><td><a href="http://<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></td>
<td style="background: #92FF84"><?php echo $sites; ?></td></tr>
<?php $allsum+=$sites;
}

flush();
} ?>
<tr><td><?php _e('All sites','WPSapeStat'); ?></td><td style="background: #92FF84"><?php echo $allsum; ?></td></tr></table>




					<?php } ?>
				
				</div>
	    		<?php

			} else { ?>

			<div class=wrap>
			<h2><?php _e('Sape.ru Statistics','WPSapeStat'); ?></h2>
			<div class="error"><p><strong><?php _e("You must specify your login and password.","WPSapeStat");?></strong> <a href="options-general.php?page=wp-sape-stat.php"><?php _e("Configure WP Sape Stat","WPSapeStat");?></a></p></div>
			<p><?php _e("Create an account in","WPSapeStat"); ?> <a href="http://www.sape.ru/r.eMotfFWAlO.php" target="_blank"><?php _e("Sape.ru","WPSapeStat"); ?></a> <?php _e("if you haven't done it yet.","WPSapeStat"); ?></p>
			</div>
			<?php 
			}
		}

		
		//Prints out the admin page
		function printAdminPage() {
			$ssOptions = $this->getAdminOptions();

			if (isset($_POST['update_sapestatSettings'])) {

				if (isset($_POST['sapestatYaca'])) {
					$ssOptions['show_yaca'] = $_POST['sapestatYaca'];
				}

				if (isset($_POST['sapestatPR'])) {
					$ssOptions['show_pr'] = $_POST['sapestatPR'];
				}


				if (isset($_POST['sapestatYaPages'])) {
					$ssOptions['show_yapages'] = $_POST['sapestatYaPages'];
				}

				if (isset($_POST['sapestatGPages'])) {
					$ssOptions['show_gpages'] = $_POST['sapestatGPages'];
				}

				if (isset($_POST['sapestatLogin'])) {
					$ssOptions['login'] = $_POST['sapestatLogin'];
				}


				if (isset($_POST['sapestatPassword'])) {
					$ssOptions['password'] = $_POST['sapestatPassword'];
				}

				update_option($this->adminOptionsName, $ssOptions);


				if (isset($_POST['sapestatUpdatetime'])) {
					update_option('WPSapeStat_tupdate', $_POST['sapestatUpdatetime']);
				}
				

				?>

				<div class="updated"><p><strong><?php _e("Settings Updated.","WPSapeStat");?></strong></p></div>
			<?php
			} ?>

			<div class=wrap>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

				<h2><?php _e('WP Sape Stat Configuration','WPSapeStat'); ?></h2>

				<p><?php _e("If you still don't have your account in","WPSapeStat"); ?> <a href="http://www.sape.ru/r.eMotfFWAlO.php"><?php _e("Sape.ru","WPSapeStat"); ?></a> <?php _e("just create it and start earning money now!","WPSapeStat"); ?></p>
				
				<h3><?php _e('Your Sape.ru login','WPSapeStat'); ?></h3>
				<input type="text" name="sapestatLogin" value="<?php echo $ssOptions['login']; ?>">

				<h3><?php _e('Your Sape.ru Password','WPSapeStat'); ?></h3>
				<input type="password" name="sapestatPassword" value="<?php echo $ssOptions['password']; ?>">

				<h3><?php _e('Update Statistics','WPSapeStat'); ?></h3>
				<p><?php _e('How often should the plugin update your sape.ru statistics? In minutes','WPSapeStat'); ?></p>
				<input type="text" name="sapestatUpdatetime" value="<?php echo get_option('WPSapeStat_tupdate'); ?>">

				<h3><?php _e('Show Yandex Thematic Index?','WPSapeStat'); ?></h3>

				<p>
					<label for="sapestatYaca_yes"><input type="radio" id="sapestatYaca_yes" name="sapestatYaca" value="true" 
					<?php if ($ssOptions['show_yaca'] == "true") { _e('checked="checked"',"WPSapeStat"); }?> />
					<?php _e('Yes','WPSapeStat'); ?></label>

					<label for="sapestatYaca_no"><input type="radio" id="sapestatYaca_no" name="sapestatYaca" value="false" 
					<?php if ($ssOptions['show_yaca'] == "false") { _e('checked="checked"', "WPSapeStat"); }?>/>
					<?php _e('No','WPSapeStat'); ?></label>
				</p>

				<h3><?php _e('Show Google Page Rank?','WPSapeStat'); ?></h3>

				<p>
					<label for="sapestatPR_yes"><input type="radio" id="sapestatPR_yes" name="sapestatPR" value="true" 
					<?php if ($ssOptions['show_pr'] == "true") { _e('checked="checked"',"WPSapeStat"); }?> />
					<?php _e('Yes','WPSapeStat'); ?></label>

					<label for="sapestatPR_no"><input type="radio" id="sapestatPR_no" name="sapestatPR" value="false" 
					<?php if ($ssOptions['show_pr'] == "false") { _e('checked="checked"', "WPSapeStat"); }?>/>
					<?php _e('No','WPSapeStat'); ?></label>
				</p>

				<h3><?php _e('Show number of indexed pages in Yandex?','WPSapeStat'); ?></h3>

				<p>
					<label for="sapestatYaPages_yes"><input type="radio" id="sapestatYaPages_yes" name="sapestatYaPages" value="true" 
					<?php if ($ssOptions['show_yapages'] == "true") { _e('checked="checked"',"WPSapeStat"); }?> />
					<?php _e('Yes','WPSapeStat'); ?></label>

					<label for="sapestatYaPages_no"><input type="radio" id="sapestatYaPages_no" name="sapestatYaPages" value="false" 
					<?php if ($ssOptions['show_yapages'] == "false") { _e('checked="checked"', "WPSapeStat"); }?>/>
					<?php _e('No','WPSapeStat'); ?></label>
				</p>

				<h3><?php _e('Show number of indexed pages in Google?','WPSapeStat'); ?></h3>

				<p>
					<label for="sapestatGPages_yes"><input type="radio" id="sapestatGPages_yes" name="sapestatGPages" value="true" 
					<?php if ($ssOptions['show_gpages'] == "true") { _e('checked="checked"',"WPSapeStat"); }?> />
					<?php _e('Yes','WPSapeStat'); ?></label>

					<label for="sapestatGPages_no"><input type="radio" id="sapestatGPages_no" name="sapestatGPages" value="false" 
					<?php if ($ssOptions['show_gpages'] == "false") { _e('checked="checked"', "WPSapeStat"); }?>/>
					<?php _e('No','WPSapeStat'); ?></label>
				</p>

				<div class="submit">
					<input type="submit" name="update_sapestatSettings" value="<?php _e('Save Settings', 'WPSapeStat') ?>" />
				</div>
			

			</form>
				<h2><?php _e('Copyright Information','WPSapeStat'); ?></h2>
				<p><?php _e('This plugin is free and uses','WPSapeStat'); ?> <a href="http://www.gnu.org/copyleft/gpl.html" target="_blank"><?php _e('GNU/GPL license','WPSapeStat'); ?></a>. <?php _e('The author of this plugin is one of the russians moneymakers, known as ','WPSapeStat'); ?><a href="http://seogad.ru/" target="_blank"><?php _e('Seogad','WPSapeStat'); ?></a>. <?php _e('It is also to note that the plugin is based on','WPSapeStat'); ?> <a href="http://spryt.ru/sape-stat/" target="_blank"><?php _e('this great code','WPSapeStat'); ?></a> <?php _e('developed by','WPSapeStat'); ?> <a href="http://spryt.ru/" target="_blank"><?php _e('Spryt','WPSapeStat'); ?></a>.</p>
				<p><?php _e('If you like this plugin and want it to be developed in the future, please donate or give me some useful advise by sending e-mail or leaving a comment on my','WPSapeStat'); ?> <a href="http://seogad.ru/" target="_blank"><?php _e('web-site','WPSapeStat'); ?></a>.</p>
				<h2><?php _e('How to donate?','WPSapeStat'); ?></h2>
				<p><?php _e('It is simple. Just send me some money (earned using sape.ru of course :) to webmoney or Yandex.money. You can also send me some TNX-points (xaps) or you can ask me about any other way of help.','WPSapeStat'); ?></p>
				<p><ul>
					<li>WMR: <strong>R750814341044</strong> (<?php _e('Ruble','WPSapeStat'); ?>)</li>
					<li>WMZ: <strong>Z387096379128</strong> (<?php _e('Dollar','WPSapeStat'); ?>)</li>
					<li>WME: E309343620119 (<?php _e('Euro','WPSapeStat'); ?>)</li>
					<li>WMU: U402178621200 (<?php _e('Ukraine Hryvna','WPSapeStat'); ?>)</li>
					<li>WMG: G283792694178 (<?php _e('Gold!','WPSapeStat'); ?>)</li>
					<li><?php _e('Yandex.Money','WPSapeStat'); ?>: <strong>41001129583833</strong></li>
					<li><?php _e('TNX ID','WPSapeStat'); ?>: 119647606</li>
				</ul></p>
				<p><?php _e('Thank you for using this plugin and earning money with','WPSapeStat'); ?> <a href="http://www.sape.ru/r.eMotfFWAlO.php"><?php _e('Sape.ru','WPSapeStat'); ?></a> :)</p>
			</div>
		<?php
		}//End function printAdminPage()

	}

} //End Class WPSapeStat


if (class_exists("WPSapeStat")) {

$seogad_wpSapeStats = new WPSapeStat();

      };

//Initialize the admin panel
		if (!function_exists("WPSapeStat_ap")) {
			function WPSapeStat_ap() {
				global $seogad_wpSapeStats;

				if (!isset($seogad_wpSapeStats)) {
					return;
				}

				if (function_exists('add_options_page')) {
					add_options_page('WP Sape Stat', 'WP Sape Stat', 9,
					basename(__FILE__), array(&$seogad_wpSapeStats, 'printAdminPage'));
				}
			}
		};


//Initialize the main plugin page
		if (!function_exists("WPSapeStat_mainpage")) {
			function WPSapeStat_mainpage() {
				global $seogad_wpSapeStats;

				if (!isset($seogad_wpSapeStats)) {
					return;
				}

				if (function_exists('add_options_page')) {
					add_management_page('WP Sape Stat', 'WP Sape Stat', 9,
					wpsapestat, array(&$seogad_wpSapeStats, 'WPSapeStatPage'));
				}
			}
		};

//database
	function WPSapeStat_db_install() {
		
		
		global $wpdb;
   		global $WPSapeStat_db_version;

		$table_name = $wpdb->prefix . "sapestat";
		$table_name_today = $wpdb->prefix . "sapestat_today";
		
		if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
			$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			sape_id bigint(20) NOT NULL,
			url VARCHAR(255) NOT NULL,
			year VARCHAR(255) NOT NULL,
			month VARCHAR(255) NOT NULL,
			day VARCHAR(255) NOT NULL,
			sum VARCHAR(255) DEFAULT '0' NOT NULL,
			UNIQUE KEY id (id)
			);";

			$wpdb->query($sql); 
			
			}

		if($wpdb->get_var("show tables like '$table_name_today'") != $table_name_today) {
			$sql2 = "CREATE TABLE " . $table_name_today . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			sape_id bigint(20) NOT NULL,
			url VARCHAR(255) NOT NULL,
			cy bigint(20) NOT NULL,
			pr bigint(20) NOT NULL,
			category_id bigint(20) NOT NULL,
			last_update_time VARCHAR(255) NOT NULL,
			date_created VARCHAR(255) NOT NULL,
			date_last_mpp_changed VARCHAR(255) NOT NULL,
			status VARCHAR(255) NOT NULL,
			comment_admin text NOT NULL,
			domain_level bigint(20) NOT NULL,
			flag_auto bigint(20) NOT NULL,
			mpp_1 bigint(20) NOT NULL,
			mpp_2 bigint(20) NOT NULL,
			mpp_3 bigint(20) NOT NULL,
			flag_blocked_in_yandex bigint(20) NOT NULL,
			flag_hide_url bigint(20) NOT NULL,
			flag_not_for_sale bigint(20) NOT NULL,
			amount_today VARCHAR(255) NOT NULL,
			amount_yesterday VARCHAR(255) NOT NULL,
			amount_total VARCHAR(255) NOT NULL,
			in_yaca VARCHAR(30) NOT NULL,
			in_dmoz VARCHAR(30) NOT NULL,
			nof_yandex bigint(20) NOT NULL,
			nof_google bigint(20) NOT NULL,
			UNIQUE KEY id (id)
			);";
			$wpdb->query($sql2);

		}
	};


function WPSapeStatWidget($args) {
	extract($args);
	global $seogad_wpSapeStats;
	$ssOptions = $seogad_wpSapeStats->getAdminOptions();
	$d = $seogad_wpSapeStats->get_sape_stat();
	global $wpdb;
	$wpdb->sapestat_today = $wpdb->prefix.'sapestat_today';
	$today_stats = $wpdb->get_results("SELECT * FROM $wpdb->sapestat_today 
	WHERE sape_id > 0 ", ARRAY_A);
						$today=0;
						$yest=0;

						/*Делаем выборку по всем сайтам аккаунта*/
						$tstats = $today_stats;

						foreach($tstats as $key=>$k) {
						$tod=round($k['amount_today'],2);
						$yes=round($k['amount_yesterday'],2);
						$yapages = $k['nof_yandex'];
						$gpages = $k['nof_google'];
						$yaca = $k['cy'];
						$pr = $k['pr']; 
					$sites_count = count($today_stats);
					$today+=$tod; 
					$yest+=$yes;
					$yaca_total+=$yaca; 
					$pr_total+=$pr; 
					$yapages_total+=$yapages; 
					$gpages_total+=$gpages; }
	echo '<link rel="STYLESHEET" type="text/css" href="'.WP_PLUGIN2_URL.'css/wp-sape-stat.css">';
	echo $before_widget;
	echo $before_title;
	if (get_option('WPSapeStat_title')) {echo get_option('WPSapeStat_title');} else {echo ''.__('Sape.ru Statistics','WPSapeStat').'';}
	echo $after_title;
	
	
	if (!$d) { ?><?php _e('Widget is not configured!','WPSapeStat'); ?>
	<?php } else { if ($ssOptions['password'] != "" && $ssOptions['login'] != "") { ?>
	<ul class="wp_sape_stat_widget"><li class="today"><?php _e('Today:','WPSapeStat'); ?> <span class="today_value"><?php echo $today; ?> <?php _e('RUR','WPSapeStat'); ?></span></li>
	<li class="yesterday"><?php _e('Yesterday:','WPSapeStat'); ?> <span class="yesterday_value"><?php echo $yest; ?> <?php _e('RUR','WPSapeStat'); ?></span></li>
	<?php if (get_option('WPSapeStat_sites') == 'checked') { ?><li class="number"><?php _e('Number of sites:','WPSapeStat'); ?> <span class="number_value"><?php echo $sites_count; ?></span></li><?php } ?>
	<?php if (get_option('WPSapeStat_cy') == 'checked') { ?><li class="cy"><?php _e('Total CY:','WPSapeStat'); ?> <span class="cy_value"><?php echo $yaca_total; ?></span></li><?php } ?>
	<?php if (get_option('WPSapeStat_pr') == 'checked') { ?><li class="pr"><?php _e('Total PR:','WPSapeStat'); ?> <span class="pr_value"><?php echo $pr_total; ?></span></li><?php } ?>
	<?php if (get_option('WPSapeStat_ypages') == 'checked') { ?><li class="ypages"><?php _e('Yandexed pages:','WPSapeStat'); ?> <span class="ypages_value"><?php echo $yapages_total; ?></span></li><?php } ?>
	<?php if (get_option('WPSapeStat_gpages') == 'checked') { ?><li class="gpages"><?php _e('Googled pages:','WPSapeStat'); ?> <span class="gpages_value"><?php echo $gpages_total; ?></span></li><?php } ?>
	</ul>
	<?php if (get_option('WPSapeStat_link') == 'checked') { ?><noindex><div class="wp_sape_stat_author_link"><?php _e('Powered by','WPSapeStat'); ?> 
	<a href="http://seogad.ru/" target="_blank">WP Sape Stat</a></div></noindex><?php } ?>
		<?php }
	}	
	echo $after_widget;
}

function register_my_widget() {
		global $seogad_wpSapeStats;
		$ssOptions = $seogad_wpSapeStats->getAdminOptions();

		if ($ssOptions['password'] != "" && $ssOptions['login'] != "") {


		register_sidebar_widget('WP Sape Stat', 'WPSapeStatWidget');
		register_widget_control('WP Sape Stat', 'WPSapeStatWidget_control' ); }
		}

add_action('init', 'register_my_widget');
add_action('init', 'load_locale' );

function WPSapeStatWidget_control() {
	if (!empty($_REQUEST['WPSapeStat_title'])) {
		update_option('WPSapeStat_title', $_REQUEST['WPSapeStat_title']);
		update_option('WPSapeStat_sites', $_REQUEST['WPSapeStat_sites']);
		update_option('WPSapeStat_cy', $_REQUEST['WPSapeStat_cy']);
		update_option('WPSapeStat_pr', $_REQUEST['WPSapeStat_pr']);
		update_option('WPSapeStat_ypages', $_REQUEST['WPSapeStat_ypages']);
		update_option('WPSapeStat_gpages', $_REQUEST['WPSapeStat_gpages']);
		update_option('WPSapeStat_link', $_REQUEST['WPSapeStat_link']);
	}
?>
<p><?php _e('Title','WPSapeStat'); ?>*:<br /><input type="text" name="WPSapeStat_title" value="<? if (get_option('WPSapeStat_title')) echo get_option('WPSapeStat_title')?>"/></p>
<p><?php _e('Show:','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_sites" value="checked" <? if (get_option('WPSapeStat_sites')=='checked') echo get_option('WPSapeStat_sites')?>> 
<?php _e('Number of sites','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_cy" value="checked" <? if (get_option('WPSapeStat_cy')=='checked') echo get_option('WPSapeStat_cy')?>> 
<?php _e('Yandex CY','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_pr" value="checked" <? if (get_option('WPSapeStat_pr')=='checked') echo get_option('WPSapeStat_pr')?>> 
<?php _e('Google PR','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_ypages" value="checked" <? if (get_option('WPSapeStat_ypages')=='checked') echo get_option('WPSapeStat_ypages')?>> 
<?php _e('Yandexed pages','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_gpages" value="checked" <? if (get_option('WPSapeStat_gpages')=='checked') echo get_option('WPSapeStat_gpages')?>> 
<?php _e('Googled pages','WPSapeStat'); ?><br />
<input type="checkbox" name="WPSapeStat_link" value="checked" <? if (get_option('WPSapeStat_link')=='checked') echo get_option('WPSapeStat_link')?>> 
<?php _e('Author link','WPSapeStat'); ?>
</p>
<?
}

//Actions and Filters	
if (isset($seogad_wpSapeStats)) {
	//Actions

	add_action('activate_wp-sape-stat/wp-sape-stat.php',
	array(&$seogad_wpSapeStats, 'init'));
	
	add_action('admin_menu', 'WPSapeStat_ap');
	add_action('admin_menu', 'WPSapeStat_mainpage');
	add_action('activate_wp-sape-stat/wp-sape-stat.php', 'WPSapeStat_db_install');


	
	//Filters
};

?>
