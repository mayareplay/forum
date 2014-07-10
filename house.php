<?
include('../../common.inc.php');

$meta['keywords'] = $meta['description'] = '我的社区,望京,来广营,酒仙桥,太阳宫,崔各庄,望京周边,东湖,望京网';

foreach($yezhu as $k=>$val){ $pageTitle.=' | '.$val['name']; }

$pageTitle = '我的社区 '.$pageTitle;


include(template_name('header'));
?>
<style>
.shequlist{margin-left:30px; margin-top:20px; margin-bottom:20px; font-size:20px}
.shequlist h1{font-size:20px}
</style>
<div class="shequlist">
  <h1>请选择要前往的社区:</h1>
  <?
  $dot = '';
  foreach($yezhu as $k=>$arr){
	  $url = $arr['url'];
	   $name = $arr['name'];	
	  echo $dot."<a href=\"$url\" target=\"_blank\">$name</a>";
	$dot=' | ';
  }
?>
</div>

<?
include(template_name('footer'));  
  ?>	  

