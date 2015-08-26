<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\time\TimePicker;
use app\components\AppConstant;
use app\components\AppUtility;

$this->title = 'New Course';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['/admin/admin/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="site-login">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'form-horizontal'],
                'action' => '',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-sm-10\">{input}</div>\n<div class=\"col-sm-10 clear-both col-sm-offset-2\">{error}</div>",
                    'labelOptions' => ['class' => 'col-sm-2 text-align-left'],
                ],
            ]); ?>
    <div class="item-detail-header">
        <?php echo $this->render("../../itemHeader/_indexWithLeftContent", ['link_title' => [AppUtility::t('Home', false)], 'link_url' => [AppUtility::getHomeURL() . 'site/index']]); ?>
    </div>
    <div class = "title-container">
        <div class="row">
            <div class="pull-left page-heading">
                <div class="vertical-align title-page"><?php echo $this->title ?></div>
            </div>
            <div class="pull-left header-btn">
                <?php echo Html::submitButton('<i class="fa fa-share header-right-btn"></i>Submit', ['class' => 'btn btn-primary pull-right page-settings', 'name' => 'Submit']) ?>
            </div>
        </div>
    </div>
<div class="tab-content shadowBox" style="margin-top:30px">
            <div class="form-label-alignment" style="margin-left: 20px; margin-right: 20px; margin-top: 20px">
                <br/>
                <?php echo $form->field($model, 'courseName')->textInput(); ?>
                <?php echo $form->field($model, 'enrollmentKey')->textInput() ?>

                        <div class="datetime form-group">
                    <?php
                    echo '<label class="start col-lg-2 pull-left "> Start Time </label>';
                    echo '<div class = "pull-left col-lg-4 time-input">';
                    echo TimePicker::widget([
                        'name' => 'start_time',
                        'value' => '10:00 AM',
                        'pluginOptions' => [
                            'showSeconds' => false
                        ]
                    ]);
                    echo '</div>';?>
                    <?php
                    echo '<label class="end pull-left col-lg-1"> End Time </label>';
                    echo '<div class="pull-left col-lg-4">';
                    echo TimePicker::widget([
                        'name' => 'end_time',
                        'value' => '10:00 PM',
                        'pluginOptions' => [
                            'showSeconds' => false,
                            'class' => 'time'
                        ]
                    ]);
                    echo '</div>';?>
                </div>

                <div style="clear: both"></div>
                <?php echo $form->field($model, 'available')->checkboxList([AppConstant::NUMERIC_TWO => 'Available to students', AppConstant::NUMERIC_ONE => 'Show on instructors home page'], ['checked' => AppConstant::NUMERIC_ONE]) ?>
                <?php echo $form->field($model, 'theme')->dropDownList(['angelishmore.css' => 'Angelishmore', 'angelish.css' => 'Angelish', 'facebookish.css' => 'Facebookish', 'modern.css' => 'Mordern', 'default.css' => 'Default'], ['prompt' => 'Select Theme']) ?>
                <?php echo $form->field($model, 'selfEnroll')->radioList([AppConstant::NUMERIC_ONE => 'No', AppConstant::NUMERIC_TWO => 'Yes']) ?>
                <?php echo $form->field($model, 'copycourse')->radioList([AppConstant::NUMERIC_ONE => 'Require enrollment key from everyone', AppConstant::NUMERIC_TWO => 'No key required for group members, require key from others ', AppConstant::NUMERIC_THREE => 'No key required from anyone']) ?>
                <?php echo $form->field($model, 'messageSystem')->radioList([AppConstant::NUMERIC_ONE => 'On for send and receive', AppConstant::NUMERIC_TWO => 'On for receive, students can only send to instructor', AppConstant::NUMERIC_THREE => 'On for receive, students can only send to students', AppConstant::NUMERIC_FOUR => 'On for receive, students cannot send', AppConstant::NUMERIC_FIVE => 'Off ']) ?>
                <?php echo $form->field($model, 'navigationLink')->checkboxList([AppConstant::NUMERIC_ONE => 'Calender', AppConstant::NUMERIC_TWO => 'Forum List', AppConstant::NUMERIC_FOUR => 'Show']) ?>
                <?php echo $form->field($model, 'latePasses')->textInput(); ?>
                <?php echo $form->field($model, 'courseAsTemplate')->checkboxList([AppConstant::NUMERIC_TWO => 'Mark as group template course', AppConstant::NUMERIC_ONE => 'Mark as global template course', AppConstant::NUMERIC_FOUR => 'Mark as self-enroll course']) ?>

    </div>
<?php ActiveForm::end(); ?>