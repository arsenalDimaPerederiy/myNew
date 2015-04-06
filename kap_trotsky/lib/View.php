<?php

class KapView
{
    private $viewDir = 'views/';
    private $_mainMenu;
    private $_user;

    public function __construct($mainMenu = array(), $user = false)
    {
        $this->_mainMenu = $mainMenu;
        $this->_user = $user;
    }

    public function render($template, $params = array(), $layout = true)
    {
        extract($params);
        ob_start();
        include $this->viewDir.$template.'.php';


        if($layout)
        {
            $content = ob_get_clean();

            ob_start();
            include $this->viewDir.'layout.php';
        }

        echo ob_get_clean();

        exit();
    }

    public function response($text)
    {
        echo json_encode($text);
        exit();
    }

    public function buildUrl($action)
    {

        $script_name = $_SERVER['SCRIPT_NAME'];

        return $script_name . '?action=' . $action;
    }

    public function getBasePath()
    {
        return str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    }
}

