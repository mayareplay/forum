<?
include('../../common.inc.php');

$meta['keywords'] = $meta['description'] = '�ҵ�����,����,����Ӫ,������,̫����,�޸�ׯ,�����ܱ�,����,������';

foreach($yezhu as $k=>$val){ $pageTitle.=' | '.$val['name']; }

$pageTitle = '�ҵ����� '.$pageTitle;


include(template_name('header'));
?>
<style>
.shequlist{margin-left:30px; margin-top:20px; margin-bottom:20px; font-size:20px}
.shequlist h1{font-size:20px}
</style>
<div class="shequlist">
  <h1>��ѡ��Ҫǰ��������:</h1>
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

