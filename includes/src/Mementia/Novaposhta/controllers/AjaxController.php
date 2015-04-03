<?php
class Mementia_Novaposhta_AjaxController extends Mage_Core_Controller_Front_Action
{
	
	public function warehouseAction() {
		$result = array();
		$result['success'] = '';		
		$result['items'] = array();
		$result['count'] = 0;
		$id = (int) $this->getRequest()->getParam('id');
		if ($id) {
			$warehouses = Mage::getModel('novaposhta/warehouse')->getCollection()
			->addFieldToFilter('city_id',array('eq' => $id))
			->setOrder('number_in_city','ASC');
			foreach ($warehouses as $warehouse) {
				$result['items'][] = array('id' => $warehouse->getId(), 'label' => $warehouse->getAddressRu());
			}
			$result['count'] = $warehouses->count();
		}
		
		$result['success'] = "ok";
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	}	
	
}