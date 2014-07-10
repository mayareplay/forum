<?php


$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
$_config['db']['1']['dbhost'] = 'localhost';
$_config['db']['1']['dbuser'] = 'wangjing_cn';
$_config['db']['1']['dbpw'] = '123';
$_config['db']['1']['dbcharset'] = 'gbk';
$_config['db']['1']['pconnect'] = '0';
$_config['db']['1']['dbname'] = 'wangjing_cn';
$_config['db']['1']['tablepre'] = 'pre_';
$_config['db']['2']['dbhost'] = 'localhost';
$_config['db']['2']['dbuser'] = 'testforum';
$_config['db']['2']['dbpw'] = '123';
$_config['db']['2']['dbcharset'] = 'gbk';
$_config['db']['2']['pconnect'] = '0';
$_config['db']['2']['dbname'] = 'testforum';
$_config['db']['2']['tablepre'] = 'pre_';
$_config['db']['100']['dbhost'] = 'localhost';
$_config['db']['100']['dbuser'] = 'wangjing';
$_config['db']['100']['dbpw'] = '123';
$_config['db']['100']['dbcharset'] = 'gbk';
$_config['db']['100']['pconnect'] = '0';
$_config['db']['100']['dbname'] = 'wangjing';
$_config['db']['100']['tablepre'] = '';
$_config['db']['common']['slave_except_table'] = '';
$_config['db']['slave'] = '';

// --------------------------  CONFIG MEMORY  --------------------------- //
$_config['memory']['prefix'] = 'NjzWsf_';
$_config['memory']['eaccelerator'] = 1;
$_config['memory']['apc'] = 1;
$_config['memory']['xcache'] = 1;
$_config['memory']['memcache']['server'] = '';
$_config['memory']['memcache']['port'] = 11211;
$_config['memory']['memcache']['pconnect'] = 1;
$_config['memory']['memcache']['timeout'] = 1;
$_config['memory']['redis']['server'] = '';
$_config['memory']['redis']['port'] = 6379;
$_config['memory']['redis']['pconnect'] = 1;
$_config['memory']['redis']['timeout'] = '0';
$_config['memory']['redis']['requirepass'] = '';
$_config['memory']['redis']['serializer'] = 1;
$_config['memory']['wincache'] = 1;

// --------------------------  CONFIG SERVER  --------------------------- //
$_config['server']['id'] = 1;

// -------------------------  CONFIG DOWNLOAD  -------------------------- //
$_config['download']['readmod'] = 2;
$_config['download']['xsendfile']['type'] = '0';
$_config['download']['xsendfile']['dir'] = '/down/';

// ---------------------------  CONFIG CACHE  --------------------------- //
$_config['cache']['type'] = 'file';

// --------------------------  CONFIG OUTPUT  --------------------------- //
$_config['output']['charset'] = 'gbk';
$_config['output']['forceheader'] = 1;
$_config['output']['gzip'] = '0';
$_config['output']['tplrefresh'] = 2;
$_config['output']['language'] = 'zh_cn';
$_config['output']['staticurl'] = 'static/';
$_config['output']['ajaxvalidate'] = '0';
$_config['output']['iecompatible'] = '0';

// --------------------------  CONFIG COOKIE  --------------------------- //
$_config['cookie']['cookiepre'] = 'osTO_';
$_config['cookie']['cookiedomain'] = '';
$_config['cookie']['cookiepath'] = '/';

// -------------------------  CONFIG SECURITY  -------------------------- //
$_config['security']['authkey'] = '01359f4xdwvAevlW1';
$_config['security']['urlxssdefend'] = 1;
$_config['security']['attackevasive'] = '3';
$_config['security']['querysafe']['status'] = 1;
$_config['security']['querysafe']['dfunction']['0'] = 'load_file';
$_config['security']['querysafe']['dfunction']['1'] = 'hex';
$_config['security']['querysafe']['dfunction']['2'] = 'substring';
$_config['security']['querysafe']['dfunction']['3'] = 'if';
$_config['security']['querysafe']['dfunction']['4'] = 'ord';
$_config['security']['querysafe']['dfunction']['5'] = 'char';
$_config['security']['querysafe']['daction']['0'] = 'intooutfile';
$_config['security']['querysafe']['daction']['1'] = 'intodumpfile';
$_config['security']['querysafe']['daction']['2'] = 'unionselect';
$_config['security']['querysafe']['daction']['3'] = '(select';
$_config['security']['querysafe']['daction']['4'] = 'unionall';
$_config['security']['querysafe']['daction']['5'] = 'unionall';
$_config['security']['querysafe']['daction']['6'] = 'uniondistinct';
$_config['security']['querysafe']['dnote']['0'] = '/*';
$_config['security']['querysafe']['dnote']['1'] = '*/';
$_config['security']['querysafe']['dnote']['2'] = '#';
$_config['security']['querysafe']['dnote']['3'] = '--';
$_config['security']['querysafe']['dnote']['4'] = '"';
$_config['security']['querysafe']['dlikehex'] = 1;
$_config['security']['querysafe']['afullnote'] = 1;

// --------------------------  CONFIG ADMINCP  -------------------------- //
// -------- Founders: $_config['admincp']['founder'] = '1,2,3'; --------- //
$_config['admincp']['founder'] = '18499,78';
$_config['admincp']['forcesecques'] = '0';
$_config['admincp']['checkip'] = 1;
$_config['admincp']['runquery'] = 1;
$_config['admincp']['dbimport'] = 1;

// --------------------------  CONFIG REMOTE  --------------------------- //
$_config['remote']['on'] = '0';
$_config['remote']['dir'] = 'remote';
$_config['remote']['appkey'] = '62cf0b3c3e6a4c9468e7216839721d8e';
$_config['remote']['cron'] = 1;

// ---------------------------  CONFIG DEBUG  --------------------------- //
$_config['debug'] = 1;

// ------------------------  CONFIG EXT_SYSTEM  ------------------------- //
$_config['ext_system']['quoteurl'] = 'http://localhost/wangjing_cn/www/handle';
$_config['ext_system']['site_cfg']['site_id'] = 1;
$_config['ext_system']['site_cfg']['1']['site_name'] = '望京网';
$_config['ext_system']['site_cfg']['1']['db_link_id'] = 1;
$_config['ext_system']['site_cfg']['2']['site_name'] = 'CBD网';
$_config['ext_system']['site_cfg']['2']['db_link_id'] = 2;
$_config['ext_system']['site_name'] = '望京网';


$_config['ext_system']['manage_group'] = 29;
$_config['ext_system']['iframeckre'] = 'http://www.wangjing.cn/';
$_config['ext_system']['privateforum'] = array('517');
$_config['ext_system']['noadsuid'] = array('493648');


// ----------------------  CONFIG PLUGINDEVELOPER  ---------------------- //
$_config['plugindeveloper'] = 1;

// ---------------------------  CONFIG INPUT  --------------------------- //
$_config['input']['compatible'] = 1;


// -------------------  THE END  -------------------- //

?>