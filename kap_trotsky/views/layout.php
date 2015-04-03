<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>"Троцкий" - управление перелинковкой</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="<?=$this->getBasePath()?>css/bootstrap.css" rel="stylesheet">
    <link href="<?=$this->getBasePath()?>css/bootstrap-responsive.css" rel="stylesheet">
    <script src="<?=$this->getBasePath()?>js/jquery.js"></script>
    <script src="<?=$this->getBasePath()?>js/jquery.cookie.js"></script>
    <script src="<?=$this->getBasePath()?>js/bootstrap-modal.js"></script>
    <script src="<?=$this->getBasePath()?>js/app.js"></script>
    <style>
      html {
        background: #393939;
      }
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
      .modal  { width: 575px; }
      .navbar-inner
      {
        padding: 0 10px 0 10px!important;
      }
      .navbar-inner .container
      {
        width: 100%;
        margin: 0;
      }
      .navbar .brand
      {
        padding: 0;
      }
      .navbar .brand img
      {
        margin: 3px 10px 0 30px;
      }
      #footer {
          background: #393939;
          border-top: 1px solid #8c7f82;
          margin-top: 30px;
          position: relative;
          overflow: hidden;
          padding: 10px 20px;
          text-align: center;
      }
      a.developed {
          display: inline-block;
          height: 90px;
          width: 150px;
          background: url(<?=$this->getBasePath()?>img/copyright.png);
          margin-left: auto;
          margin-right: auto;
      }
      .right { float: right; }
      .left { float: left; }
      .selected { color: red!important; }
    </style>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="<?=$this->buildUrl('index')?>"><img src="<?=$this->getBasePath()?>img/logo.png" /></a>
          <div class="nav-collapse collapse">
            <ul class="nav">
                <?php foreach($this->_mainMenu as $action => $name): ?>
                    <li <?php if($template == $action): ?>class="active"<?php endif; ?>><a href="<?=$this->buildUrl($action)?>"><?=$name?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php if($this->_user):?>
                <p class="navbar-text pull-right">
                    <b><?=$this->_user?></b>
                    <a href="<?=$this->buildUrl('logout')?>" class="navbar-link">Выйти</a>
                </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
        <?=$content?>
    </div>
    <div id="footer">
        <a href="http://netpeak.ua" class="developed" target="_blank"></a>
        <p style="color: #888">
        Данный скрипт - разработка R&D отдела Netpeak для увеличения количества прибыльных бизнесов среди наших клиентов. Что с ним делать и как - знают только наши сотрудники.
        </p>
    </div>
  </body>
</html>

