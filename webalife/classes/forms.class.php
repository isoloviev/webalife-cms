<?php
/*
Powered by DevLab CMS (c) 2006
Created: Ivan S. Soloviev, sites@devlab.spb.ru
Description: Main constructor of forms
*/

include_once(CMS_CLASSES."oohforms.inc.php");

class GenForms extends site
{
	function GenForms()
	{
		global $f;
		$f = new form();
	}
	function GetElements($form)
	{
		global $mysql;
		$data = $mysql->sql("SELECT e.* FROM ".PREFIX."forms_elements e, ".PREFIX."forms f WHERE f.id = e.form_id AND f.name_id = '".$form."' and e.active = 1 ORDER BY e.sort_id ASC", 2);
		return $data;
	}
	function GetOptions($form, $group, $mode = '')
	{
		global $mysql;
		$data = $mysql->sql("SELECT text FROM ".PREFIX."forms_groups WHERE element_id = ".$group." ORDER BY id", 2);
		if($mode == '')	$ar[] = array("label"=>"��������...", "value"=>"");
		foreach($data as $r) $ar[] = $r['TEXT'];
		return $ar;
	}
	function add($params)
	{
		global $f;
		$f->add_element($params);
	}
	function display($par1,$par2,$par3,$par4,$par5)
	{
		global $f;
		$f->start($par1,$par2,$par3,$par4,$par5);
	}
	function get_start($par1,$par2,$par3,$par4,$par5)
	{
		global $f;
		return $f->get_start($par1,$par2,$par3,$par4,$par5);
	}
	function get_finish($par1,$par2)
	{
		global $f;
		return $f->get_finish($par1,$par2);
	}
	function show($name, $value = '') 
	{
		global $f;
		$f->show_element($name, $value);
	}
	function get($name, $value = '') 
	{
		global $f;
		return $f->get_element($name, $value);
	}
	function finish()
	{
		global $f;
		$f->finish();
	}
}