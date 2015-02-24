<?php
class Qaz_Qbanner_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	$data = array(
			'title'=> 'Nguyendt "',
			'name' => 'Name',
			'date'=>now()
		);
		$dataSerialize = serialize($data);
		print "<pre>";
		print_r(serialize($dataSerialize));
		
		print ("\n --------------");
		
		print_r(unserialize($dataSerialize));
    }
}