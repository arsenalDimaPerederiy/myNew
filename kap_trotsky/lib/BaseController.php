<?php
session_start();

include 'lib/Model.php';
include 'lib/View.php';
include 'lib/Logging.php';

class BaseController
{
    protected $_view;
    protected $_kap;
    protected $_allowedActions = array();
    protected $_mainMenu = array();
    protected $_requestParams = array();
    protected $_openIDhost = 'http://auth.aks.od.ua/';
    protected $_logger;
    protected $_dir;

    public function __construct($config)
    {
        $this->_logger = new KapLogging($config['data_dir'], $config['crypt_key']);
        $this->_kap = new KapModel($config);

        $this->_view = new KapView($this->_mainMenu, $this->getLoggedInUser());
        $this->_requestParams = array_merge($_GET, $_POST);

    }

    public function run()
    {
        if(! $this->getLoggedInUser())
            return $this->openIDLogin();
        else
            return call_user_func_array(array($this, $this->_getAction()), array());
    }

    protected function _getAction()
    {
        //$path_info = @$_SERVER['REQUEST_URI'];
        //$action = array_shift(explode('?', $path_info));
        //$action = array_pop(explode('/', trim($action, '/')));
        $action = $this->getParam('action', 'index');

        if( ! in_array($action, $this->_allowedActions))
            $action = 'index';


        $function_name = 'action' . ucfirst($action);

        return $function_name;

    }

    protected function isGet()
    {
        return ! empty($_GET);
    }

    protected function isPost()
    {
        return ! empty($_POST);
    }

    protected function getParam($name, $defaultValue = false)
    {
        return isset($this->_requestParams[$name]) ? $this->_requestParams[$name] : $defaultValue;
    }

    protected function getCookie($name, $defaultValue = false)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $defaultValue;
    }

    protected function getLoggedInUser()
    {
        return isset($_SESSION['email']) ? $_SESSION['email'] : false;
    }
    protected function setLoggedInUser($email)
    {
        return $_SESSION['email'] = $email;
    }

    protected function logout()
    {
        session_destroy();
    }

    protected function redirect($url)
    {
        header('Location: ' . $url);
    }

    protected function openIDLogin()
    {
        require 'openid.php';
        try {
            # Change 'localhost' to your domain name.
            $openid = new LightOpenID($_SERVER['HTTP_HOST']);
            if(!$openid->mode) {
                if($this->getParam('auth'))
                {
                    $openid->identity = $this->_openIDhost;
                    $openid->required = array('contact/email');
                    header('Location: ' . $openid->authUrl());
                }
                else
                {
                    $this->_view->render('login', array(), false);
                }
            } elseif($openid->mode == 'cancel') {
                $this->_view->render('error', array('msg' => 'Аутентификация была прервана'), false);
            } else {
                if($openid->validate())
                {
                    $arrs = $openid->getAttributes();

                    if(isset($arrs['contact/email']))
                    {
                        $this->setLoggedInUser($arrs['contact/email']);
                        $this->_logger->write($arrs['contact/email'], 'login');
                        $this->redirect($this->_view->buildUrl('index'));
                    }
                    else
                    {
                        $this->_view->render('error', array('msg' => 'Не переданы необходимы данные для авторизации'), false);
                    }
                }
                else
                {
                    $this->_view->render('error', array('msg' => 'Валидацию выполнить не удалось'), false);
                }
            }
        } catch(ErrorException $e) {
            $this->_view->render('error', array('msg' => $e->getMessage()), false);
        }
    }
}
