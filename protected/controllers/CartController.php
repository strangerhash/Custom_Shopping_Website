<?php

/**

 *@copyright : Satyendra Pandey

 *@author	 : Satyendra Pandey  < pandeysatyendra870@gmail.com >

 *

 * All Rights Reserved.

 * Proprietary and confidential :  All information contained herein is, and remains

 * the property of Satyendra Pandey  and his partners.

 * Unauthorized copying of this file, via any medium is strictly prohibited.

 *

 */

namespace app\controllers;



use Yii;

use app\models\Cart;


use app\models\search\Cart as CartSearch;


use app\components\TController;

use yii\web\NotFoundHttpException;

use yii\filters\AccessControl;

use yii\filters\AccessRule;

use app\models\User;

use yii\web\HttpException;

use app\components\TActiveForm;
use app\models\Product;

/**

 * CartController implements the CRUD actions for Cart model.

 */

class CartController extends TController

{

    public function behaviors()
    {

        return [

            'access' => [

                'class' => AccessControl::className(),

                'ruleConfig' => [

                    'class' => AccessRule::className()

                ],

                'rules' => [

                    [

                        'actions' => [

                            'clear',

                            'delete',

                        ],

                        'allow' => true,

                        'matchCallback' => function () {

                            return User::isAdmin();
                        }

                    ],

                    [

                        'actions' => [

                            'index',

                            'add',

                            'view',

                            'update',

                            'clone',

                            'ajax',

                            'mass'

                        ],

                        'allow' => true,

                        'roles' => [

                            '@'

                        ]

                    ],

                    [

                        'actions' => [



                            'view',
                            'remove'

                        ],

                        'allow' => true,

                        'roles' => [

                            '?',

                            '*'

                        ]

                    ]

                ]

            ]

        ];
    }





    /**

     * Lists all Cart models.

     * @return mixed

     */

    public function actionIndex()

    {


        $searchModel = new CartSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $this->updateMenuItems();

        return $this->render('index', [

            'searchModel' => $searchModel,

            'dataProvider' => $dataProvider,

        ]);
    }



    /**

     * Displays a single Cart model.

     * @param integer $id

     * @return mixed

     */

    public function actionView($id)

    {

        $model = $this->findModel($id);

        $this->updateMenuItems($model);

        return $this->render('view', ['model' => $model]);
    }



    /**

     * Creates a new Cart model.

     * If creation is successful, the browser will be redirected to the 'view' page.

     * @return mixed

     */

    public function actionAdd(/* $id*/)

    {

        $model = new Cart();

        $model->loadDefaultValues();

        $model->state_id = Cart::STATE_ACTIVE;



        /* if (is_numeric($id)) {

            $post = Post::findOne($id);

            if ($post == null)

            {

              throw new NotFoundHttpException('The requested post does not exist.');

            }

            $model->id = $id;

                

        }*/



        $model->checkRelatedData([


            'created_by_id' => User::class,


            'product_id' => Product::class,


        ]);

        $post = \yii::$app->request->post();

        if (\yii::$app->request->isAjax && $model->load($post)) {

            \yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return TActiveForm::validate($model);
        }

        if ($model->load($post) && $model->save()) {

            return $this->redirect($model->getUrl());
        }

        $this->updateMenuItems();

        return $this->render('add', [

            'model' => $model,

        ]);
    }



    /**

     * Updates an existing Cart model.

     * If update is successful, the browser will be redirected to the 'view' page.

     * @param integer $id

     * @return mixed

     */

    public function actionUpdate($id)

    {

        $model = $this->findModel($id);



        $post = \yii::$app->request->post();

        if (\yii::$app->request->isAjax && $model->load($post)) {

            \yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return TActiveForm::validate($model);
        }

        if ($model->load($post) && $model->save()) {

            return $this->redirect($model->getUrl());
        }

        $this->updateMenuItems($model);

        return $this->render('update', [

            'model' => $model,

        ]);
    }



    /**

     * Clone an existing Cart model.

     * If update is successful, the browser will be redirected to the 'view' page.

     * @param integer $id

     * @return mixed

     */

    public function actionClone($id)

    {

        $old = $this->findModel($id);



        $model = new Cart();

        $model->loadDefaultValues();

        $model->state_id = Cart::STATE_ACTIVE;



        //$model->id  = $old->id$model->product_id  = $old->product_id//$model->state_id  = $old->state_id$model->type_id  = $old->type_id//$model->created_on  = $old->created_on$model->updated_on  = $old->updated_on//$model->created_by_id  = $old->created_by_id


        $post = \yii::$app->request->post();

        if (\yii::$app->request->isAjax && $model->load($post)) {

            \yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return TActiveForm::validate($model);
        }

        if ($model->load($post) && $model->save()) {

            return $this->redirect($model->getUrl());
        }

        $this->updateMenuItems($model);

        return $this->render('update', [

            'model' => $model,

        ]);
    }



    /**

     * Deletes an existing Cart model.

     * If deletion is successful, the browser will be redirected to the 'index' page.

     * @param integer $id

     * @return mixed

     */

    public function actionDelete($id)

    {

        $model = $this->findModel($id);



        if (\yii::$app->request->post()) {

            $model->delete();

            return $this->redirect(['index']);
        }

        return $this->render('delete', [

            'model' => $model,

        ]);
    }



    /**

     * Truncate an existing Cart model.

     * If truncate is successful, the browser will be redirected to the 'index' page.

     * @param integer $id

     * @return mixed

     */

    public function actionClear($truncate = true)

    {

        $query = Cart::find();

        foreach ($query->each() as $model) {

            $model->delete();
        }

        if ($truncate) {

            Cart::truncate();
        }

        \Yii::$app->session->setFlash('success', 'Cart Cleared !!!');

        return $this->redirect([

            'index'

        ]);
    }


    public function actionRemove($id)
    {
        $request = Yii::$app->request->get();

        $model = Cart::findOne($request['id']);
        if ($model) {
            $model->delete();
        }
        return $this->redirect(['site/cart']);
    }

    /**

     * Finds the Cart model based on its primary key value.

     * If the model is not found, a 404 HTTP exception will be thrown.

     * @param integer $id

     * @return Cart the loaded model

     * @throws NotFoundHttpException if the model cannot be found

     */

    protected function findModel($id, $accessCheck = true)

    {


        if (($model = Cart::findOne($id)) !== null) {



            if ($accessCheck && !($model->isAllowed()))

                throw new HttpException(403, Yii::t('app', 'You are not allowed to access this page.'));



            return $model;
        } else {

            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function updateMenuItems($model = null)

    {

        switch (\Yii::$app->controller->action->id) {



            case 'add': {

                    $this->menu['manage'] = [

                        'label' => '<span class="glyphicon glyphicon-list"></span>',

                        'title' => Yii::t('app', 'Manage'),

                        'url' => [

                            'index'

                        ]

                        // 'visible' => User::isAdmin ()

                    ];
                }

                break;

            case 'index': {

                    $this->menu['add'] = [

                        'label' => '<span class="glyphicon glyphicon-plus"></span>',

                        'title' => Yii::t('app', 'Add'),

                        'url' => [

                            'add'

                        ]

                        // 'visible' => User::isAdmin ()

                    ];

                    $this->menu['clear'] = [

                        'label' => '<span class="glyphicon glyphicon-remove"></span>',

                        'title' => Yii::t('app', 'Clear'),

                        'url' => [

                            'clear'

                        ],

                        'htmlOptions' => [

                            'data-confirm' => "Are you sure to delete these items?"

                        ],

                        'visible' => User::isAdmin()

                    ];
                }

                break;

            case 'update': {

                    $this->menu['add'] = [

                        'label' => '<span class="glyphicon glyphicon-plus"></span>',

                        'title' => Yii::t('app', 'add'),

                        'url' => [

                            'add'

                        ]

                        // 'visible' => User::isAdmin ()

                    ];

                    $this->menu['manage'] = [

                        'label' => '<span class="glyphicon glyphicon-list"></span>',

                        'title' => Yii::t('app', 'Manage'),

                        'url' => [

                            'index'

                        ]

                        // 'visible' => User::isAdmin ()

                    ];
                }

                break;



            default:

            case 'view': {

                    $this->menu['manage'] = [

                        'label' => '<span class="glyphicon glyphicon-list"></span>',

                        'title' => Yii::t('app', 'Manage'),

                        'url' => [

                            'index'

                        ]

                        // 'visible' => User::isAdmin ()

                    ];

                    if ($model != null) {

                        $this->menu['clone'] = array(

                            'label' => '<span class="glyphicon glyphicon-copy">Clone</span>',

                            'title' => Yii::t('app', 'Clone'),

                            'url' => $model->getUrl('clone'),

                            // 'visible' => User::isAdmin ()

                        );

                        $this->menu['update'] = [

                            'label' => '<span class="glyphicon glyphicon-pencil"></span>',

                            'title' => Yii::t('app', 'Update'),

                            'url' => $model->getUrl('update')

                            // 'visible' => User::isAdmin ()

                        ];

                        $this->menu['delete'] = [

                            'label' => '<span class="glyphicon glyphicon-trash"></span>',

                            'title' => Yii::t('app', 'Delete'),

                            'url' => $model->getUrl('delete')

                            // 'visible' => User::isAdmin ()

                        ];
                    }
                }
        }
    }
}
