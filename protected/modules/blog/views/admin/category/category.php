<?php
   use app\modules\blog\models\Category;
   use app\modules\blog\models\Post;
   use yii\helpers\Url;
   use yii\widgets\ListView;
   ?>
<div class="wrapper" itemscope itemtype="http://schema.org/Blog">
   <section class="content">
      <!-- cx portfolio section start -->
      <div class="portfolio-heading-section">
         <div class="container-fluid">
            <div class="row">
               <div class="col-md-12">
                  <div class="area-heading text-center">
                     <h1 class="area-title"><?= $title ?></h1>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section class="cx-blog-section">
      <div class="container-fluid">
         <div class="row">
            <div class="col-lg-12 col-md-12 text-center">
               <div class="blog-categories">
                  <ul class="list-inline">
                     <?php
                        $categories = Category::findActive ()->all ();
                        if (! empty ( $categories )) {
                        	foreach ( $categories as $category ) {
                        		$count = Post::find ()->where ( [ 
                        				'type_id' => $category->id 
                        		] )->count ();
                        		?>
                     <li><a
                        href="<?= Url::toRoute(['/blog/category/type', 'id' => $category->id, 'title' => $category->title]) ?>"> <?= $category->title ?> <span>(<?= $count?>)</span>
                        </a>
                     </li>
                     <?php
                        }
                        }
                        
                        ?>
                     <li><a href="<?= Url::toRoute(['/blog']) ?>"> All <span>(<?=Post::find ()->where ( [ 'state_id' => Post::STATE_ACTIVE ] )->count ();?>)</span>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="clearfix"></div>
    
            <div class="col-lg-12 col-md-12">
               <?=ListView::widget(['dataProvider' => $dataProvider,'layout' => "{items}<div class='clearfix text-right'>{pager}</div>", 'itemView' => function ($model, $key, $index, $widget) {
                  return $this->render('/post/_userview', ['model' => $model]);}
                  ]);?> 
            </div>
         </div>
      </div>
   </section>
</div>