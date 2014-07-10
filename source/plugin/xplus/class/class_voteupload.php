<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_voteupload.php 784 2010-10-19 08:39:21Z Niexinyuan $
 */

class vote_upload
{

	var $uid;
	var $aid;
	var $simple;
	var $statusid;
	var $attach;
	var $user;
	var $imageexts;
	var $attachextensions;
	var $maxattachsize;
	var $voteid;

	function vote_upload() {
		global $_G;

		$this->uid = intval($_G['gp_uid']);
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$this->uid);

		if(!$_FILES['Filedata']['error'] && $_G['gp_hash'] == $swfhash && $this->uid) {

			$this->aid = 0;
			$this->simple = 0;
            
			$_G['uid'] = $this->uid;

			$this->voteid = !empty($_G['gp_voteid']) ? intval($_G['gp_voteid']) :0;

			if($this->voteid <= 0 || !intval(DB::result_first("SELECT contenttype FROM ".DB::table('xplus_vote')." WHERE voteid='{$this->voteid}'"))) {				
                $this->uploadmsg(9);
			}
            
			$attach = upload_images($_FILES['Filedata'], 'common', true, 176, 176);
            
			$caption = dhtmlspecialchars(trim($attach['name']));
			$caption = substr($caption, 0, -(strlen(fileext($caption)) + 1));
			$data = array(
				'voteid' => $this->voteid,
				'caption' => $caption,
				'displayorder' => 0,
				'imageurl' => $attach['attachment'],
				'aid' => $attach['aid']
			);
			DB::insert('xplus_vote_choice', $data);

			$this->aid = $this->voteid;
			$this->uploadmsg(0);
        }
        
	}

	function uploadmsg($statusid) {
		global $_G;
		if($this->simple == 1) {
			echo 'DISCUZUPLOAD|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'];
		} elseif($this->simple == 2) {
			echo 'DISCUZUPLOAD|'.($_G['gp_type'] == 'image' ? '1' : '0').'|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'].'|'.$this->attach['attachment'].'|'.$this->attach['name'];
		} else {
			echo $this->aid;
		}
		exit;
	}
}

?>