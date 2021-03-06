<?php

use app\components\useraction\UserAction;

use app\modules\comment\widgets\CommentsWidget;
use yii\helpers\VarDumper;

/* @var $this yii\web\View */

/* @var $model app\models\Product */


$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products'), 'url' => ['index']];

$this->params['breadcrumbs'][] = (string)$model;

?>

<div class="wrapper">

   <div class="card">

      <div class="product-view">

         <?php echo  \app\components\PageHeader::widget(['model' => $model]); ?>

      </div>

   </div>

   <div class="card">

      <div class="card-body">
         <div class="row">
            <div class="col-md-2 pr0">
               <div class="profileimage">
                  <?= $model->displayImage($model->thumb_main_file, ['class' => 'profile-pic'], 'default.png', true); ?>
               </div>
            </div>

            <div class="col-md-10">

               <?php echo \app\components\TDetailView::widget([

                  'id'   => 'product-detail-view',

                  'model' => $model,

                  'options' => ['class' => 'table table-bordered'],

                  'attributes' => [

                     'id',
                     'title:html',
                     'description:html',
                     'image_file',
                     [

                        'attribute' => 'category_id',

                        'format' => 'raw',

                        'value' => $model->getRelatedDataLink('category_id'),


                     ],
                     [

                        'attribute' => 'menu_id',

                        'format' => 'raw',

                        'value' => $model->getRelatedDataLink('menu_id'),


                     ],
                     'price',
                     [

                        'attribute' => 'state_id',

                        'format' => 'raw',

                        'value' => $model->getStateBadge(),
                     ],
                     [

                        'attribute' => 'type_id',

                        'value' => $model->getType(),

                     ],
                     'created_on:datetime',
                     'updated_on:datetime',
                     [

                        'attribute' => 'created_by_id',

                        'format' => 'raw',

                        'value' => $model->getRelatedDataLink('created_by_id'),

                     ],

                  ],

               ]) ?>

               <?php echo $model->description; ?>

               <?php
               echo UserAction::widget([

                  'model' => $model,

                  'attribute' => 'state_id',

                  'states' => $model->getStateOptions()

               ]);

               ?>

            </div>

         </div>


         <br><br><br>
         <div class="jumbotron text-center">Product Images <div>

               <?php $productImageArray = json_decode($model->image_file);

               if (isset($productImageArray)) {

                  foreach ($productImageArray as $key => $value) {
               ?>

                     <?= $model->displayImage($productImageArray[$key], ['class' => 'profile-pic'], 'default.png', true); ?>

               <?php }
               } ?>




               <div class="card">

                  <div class="card-body">

                     <div class="product-panel">

                        <?php

                        $this->context->startPanel();


                        $this->context->addPanel('Feeds', 'feeds', 'Feed', $model /*,null,true*/);


                        $this->context->endPanel();

                        ?>

                     </div>

                  </div>

               </div>


            </div>