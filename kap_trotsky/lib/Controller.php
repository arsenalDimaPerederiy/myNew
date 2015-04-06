<?php

include 'lib/BaseController.php';

class KapController extends BaseController
{
    protected $_allowedActions = array(
        'index',
        'upload',
        'view',
        'edit',
        'example',
        'settings',
        'logout',
        'delete',
        'edit_url'
    );

    protected $_mainMenu = array(
        'upload'    => 'Загрузить КАП',
        'view'      => 'Посмотреть КАП',
        'edit'      => 'Редактировать привязки',
        'settings'  => 'Настройки',
        'example'   => 'Пример'
    );

    private $_encodings = array('UTF-8', 'CP1251');
    private $_linkDelimeters = array(
        'ul-li' => 'Список ul li',
        'ol-li' => 'Список ol li',
        'br' => 'Перевод строки br',
        'coma' => 'Разделение запятой'
    );


    private $_settings = array(
        'encoding'  => 'UTF-8',
        'num_links' => 5,
        'delimeter' => 'ul li',
        'title' => '<strong>Links:</strong>',
        'get_params' =>  ''
    );

    public function actionIndex()
    {
        $logs = $this->_logger->readAll();

        $this->_view->render('index', array('logs' => $logs));
    }

    public function actionView()
    {
        $page = $this->getParam('page', 1);
        $page_size = $this->getCookie('ps', 500);
        $filter = $this->getParam('filter');

        $this->_view->render('view',array(
            'kap' => $this->_kap->getKap($page, $page_size, $filter),
            'page' => $page,
            'page_size' => $page_size,
            'filter' => $filter,
        ));
    }

    public function actionEdit()
    {
        $key = $this->getParam('key');
        $subkey = $this->getParam('subkey');
        $page = $this->getParam('page', 1);
        $page_size = $this->getCookie('ps', 500);
        $filter = $this->getParam('filter');

        if($key && $subkey)
        {
            $this->_logger->write($this->getLoggedInUser(), 'delete_link', $this->_kap->getLink($key));

            if($this->_kap->remove($key, $subkey))
                $this->_view->response('ok');
            else
                $this->_view->response('fail');
        }

        $this->_view->render('edit', array(
            'urls' => $this->_kap->getUrls($page, false, $page_size, $filter),
            'page' => $page,
            'page_size' => $page_size,
            'filter' => $filter
        ));
    }

    public function actionSettings()
    {
        if($this->isPost())
        {
            foreach($this->_settings as $name => $value)
                $this->_kap->setParam($name, $this->getParam($name, $value));
        }

        $this->_view->render('settings', array(
            'encodings' => $this->_encodings,
            'delimeters' => $this->_linkDelimeters,
            'prefered_encoding' => $this->_kap->getParam('encoding', 'UTF-8'),
            'prefered_delimeter' => $this->_kap->getParam('delimeter', 'ul li'),
            'title' => $this->_kap->getParam('title', '<strong>Links:</strong>'),
            'num' => $this->_kap->getParam('num_links', 3),
            'get_params' => $this->_kap->getParam('get_params', ''),
            'post' => $this->isPost(),
        ));
    }

    public function actionDelete()
    {
        $ids = $this->getParam('ids');
        $type = $this->getParam('type');

        if( ! in_array($type, array('kap', 'links')))
            $this->_view->response('fail');

        if($ids)
        {
            if($type == 'links')
            {
                $links = $this->_kap->getUrls(1, trim($ids, ","));
                $this->_logger->write($this->getLoggedInUser(), 'delete_link', $links);
            }

            if($this->_kap->removeAll($type, $ids))
                $this->_view->response('ok');
            else
                $this->_view->response('fail');


        }
    }

    public function actionUpload()
    {
        $reset = $this->getParam('reset');
        $kap = false;
        $excluded = false;

        if( ! empty($_POST['kap']))
            $kap = $_POST['kap'];

        if( !empty($_FILES['kap_file']) && file_exists($_FILES['kap_file']['tmp_name']))
            $kap = file_get_contents($_FILES['kap_file']['tmp_name']);

        if($kap)
        {
            $excluded = $this->_kap->updateKap($kap, $reset);
            $this->_logger->write($this->getLoggedInUser(), 'kap_upload');
        }

        $this->_view->render('upload', array(
            'excluded' => $excluded,
            'upload' => $kap
        ));
    }

    public function actionExample()
    {
        $this->_view->render('example', array('kap' => $this->_kap));
    }

    public function actionLogout()
    {
        $this->logout();
        $this->redirect($this->_view->buildUrl('index'));
    }

    public function actionEdit_url()
    {
        $url = $this->getParam('url');
        $old_url = $this->getParam('old_url');
        $change_links = $this->getParam('change_links');

        if($url && $old_url)
        {
            $this->_kap->updateKapUrl($url, $old_url);

            if($change_links)
                $this->_kap->updateLinksUrl($url, $old_url);

            $this->_view->response('ok');
        }
    }
}
