<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Model_oauth extends Mage_Core_Model_Abstract{

    public function _construct()
    {
        $this->_init('webinse_oauth/oauth');
    }

    public function setSocialNetworkNewRecord($data){
        try{
            $this->setData($data);
            $this->save();
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
    public function getRecordsByUserId($id){
        return $this->getCollection()->addFieldToFilter('user_soc_id',array('eq'=>$id));

    }
    public function GetUserBySocialIdSocId($id,$soc){
        return $this->getCollection()
                    ->addFieldToFilter('user_soc_id',array('eq'=>$id))
                    ->addFieldToFilter('social',array('eq'=>$soc));
    }
    public function setNewCustomerId($id,$customerId){
        try{
            $model=$this->load($id);
            $model->setCustomerId($customerId);
            if(!$this->save()){
                throw new Exception('new social record not create');
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
}