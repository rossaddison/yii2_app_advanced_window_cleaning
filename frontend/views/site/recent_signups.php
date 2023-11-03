<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\AssignObserverRoleForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

$this->title = 'Assign Observer Role';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-observer">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please select the following user to assign the observer role to:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-observer']); ?>

                <?//= $form->field($model, 'user_id')->dropDownList(ArrayHelper::map($recent_signups, 'id','email'),['prompt'=>'Select...']); ?>
            
                <div class="form-group">
                    <?= Html::submitButton('', ['class' => 'btn btn-primary', 'name' => 'observer-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php echo VarDumper::dump($recent_signups);?>