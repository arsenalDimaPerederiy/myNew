<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

class Webinse_OAuth_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

} 