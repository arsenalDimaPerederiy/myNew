
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
    <style>
      html {
        background: #393939;
      }
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
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
    </style>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="<?=$this->buildUrl('index')?>"><img src="<?=$this->getBasePath()?>img/logo.png" /></a>
          <div class="nav-collapse collapse">
          </div>
        </div>
      </div>
    </div>
    <div class="container">
        <div class="alert alert-danger">
            <?=$msg?>
        </div>
    </div>
    <div id="footer">
        <a href="http://netpeak.ua" class="developed" target="_blank"></a>
        <p style="color: #888">
        Данный скрипт - разработка R&D отдела Netpeak для увеличения количества прибыльных бизнесов среди наших клиентов. Что с ним делать и как - знают только наши сотрудники.
        </p>
    </div>
  </body>
</html>

