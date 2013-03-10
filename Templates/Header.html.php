<!DOCTYPE html>
<html>
    <head>
        <title><?=$this->title;?></title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <?=$this->RenderTemplate('Templates::CSS/css.html.php')?>

        <?=$this->RenderTemplate('Templates::JS/js.html.php')?>

    </head>

    <body>

        <?=$this->RenderTemplate('Templates::MainMenu.html.php')?>

        <div class="wrapper">

            <?=$this->FlashAll();?>

            <div id="JSEvent"></div>

        </div>