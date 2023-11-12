<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'House 2 House';
?>
<div class="site-index">
</div>
<br>
<?php echo Html::tag('H1',"Common/config/aliases"); ?>
<br>
<?php echo "@webroot -------> ". Url::to('@webroot'); ?>
<br>
<?php echo "@app --------> ". Url::to('@app'); ?>
<br>
<?php echo "@frontend ----------> ". Url::to('@frontend'); ?>
<br>
<?php echo "@backend ---------> ". Url::to('@backend'); ?>
<br>
<?php echo "@migrations ---------> ". Url::to('@migrations'); ?>
<br>
<?php echo "@console ---------> ". Url::to('@console'); ?>
<br>
<?php echo "@base_root ---------> ". Url::to('@base_root'); ?>
<br>
<?php echo "@common ---------> ". Url::to('@common'); ?>
<br>
<?= Html::tag('br'); ?>
<?php echo "@web ---- .htacces file location ie. frontend/web ----------> ". Html::tag('a',Url::to('@web'),['class' => 'btn btn-success']); ?>