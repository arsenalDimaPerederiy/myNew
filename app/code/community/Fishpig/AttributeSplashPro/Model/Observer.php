<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Model_Observer extends Varien_Object
{
	public function fseoLayeredNavigationMatchEntityObserver(Varien_Event_Observer $observer)
	{
		if (!Mage::helper('fseo/layer')->isEntityTypeEnabled('splash_page')) {
			return $this;
		}

		$urlKey = $observer->getEvent()->getRequestUri();	
		$urlSuffix = Fishpig_AttributeSplashPro_Model_Page::getUrlSuffix();

    	if ($urlSuffix && $urlSuffix !== '/') {
			if (substr($urlKey, -strlen($urlSuffix)) !== $urlSuffix) {
				return false;
			}
			
			$urlKey = substr($urlKey, 0, -strlen($urlSuffix));
    	}

    	if (substr_count($urlKey, '/') < 1) {
	    	return false;
    	}

    	$firstToken = substr($urlKey, 0, strpos($urlKey, '/'));

    	$resource = Mage::getSingleton('core/resource');
    	$db = $resource->getConnection('core_read');
    	
    	$select = $db->select()
    		->from($resource->getTableName('splash/page'), array('object_id' => 'page_id', 'request_path' => 'url_key'))
#    		->where('? LIKE ' . (new Zend_Db_Expr("CONCAT(SUBSTR(request_path, 0, -" . strlen($urlSuffix) . "), '%')")), $urlKey)
    		->where('url_key LIKE ?', $firstToken . '%')
//    		->where('store_id=?', Mage::app()->getStore()->getId())
//    		->where('is_system=?', 1)
//    		->where('category_id IS NOT NULL')
//    		->where('product_id IS NULL');
;

   		if ($results = $db->fetchAll($select)) {
   			$winner = array('length' => false, 'result' => false);

			foreach($results as $result) {
				$bUrlKey = rtrim($urlSuffix ? substr($result['request_path'], 0, -strlen($urlSuffix)) . '/' : $result['request_path'], '/');

				if (strpos($urlKey, $bUrlKey) === 0 || strpos($bUrlKey, $urlKey) === 0) {
					list($objectId, $requestPath) = array_values($result);
					
					if ($winner['length'] === false || strlen($bUrlKey) > $winner['length']) {
						$winner['length'] = strlen($bUrlKey);
						$winner['result'] = $result;
					}
				}
			}

			if ($winner['length'] !== false) {
				$entityUri = trim($urlSuffix, '/') ? substr($requestPath, 0, -strlen(trim($urlSuffix, '/'))) : $requestPath;
				$tokens = explode('/', trim(substr($urlKey, strlen($entityUri)), '/'));

				$observer->getEvent()->getTransport()
					->setEntityData(
						new Varien_Object(array(
							'entity_id' => $objectId,
							'entity_type' => 'splash_page',
							'entity_url_key' => $entityUri,
							'url_suffix' => $urlSuffix,
							'tokens' => $tokens,
							'module_name' => 'splashpro',
							'controller_name' => 'page',
							'action_name' => 'view',
							'params' => array(
								'id' => $objectId,
							)
						)));
			}
   		}
   		
   		return false;
	}
}
