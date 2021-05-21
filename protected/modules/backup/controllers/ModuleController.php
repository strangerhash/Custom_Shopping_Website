<?php
/**
 *
 *@copyright : ToXSL Technologies Pvt. Ltd. < www.toxsl.com >
 *@author     : Shiv Charan Panjeta < shiv@toxsl.com >
 *
 * All Rights Reserved.
 * Proprietary and confidential :  All information contained herein is, and remains
 * the property of ToXSL Technologies Pvt. Ltd. and its partners.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 */
namespace app\modules\backup\controllers;

use app\components\TController;
use app\models\User;
use app\modules\backup\helpers\MysqlBackup;
use app\modules\backup\models\UploadForm;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class ModuleController extends TController
{

    public $menu = [];

    public $tables = [];

    public $fp;

    public $file_name;

    public $enableZip = true;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'index',
                    'create',
                    'delete',
                    'restore',
                    'download'
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'restore',
                            'create',
                            'delete',
                            'download'
                        ],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return (User::isAdmin());
                        }
                    ]
                ]
            ]
        ];
    }

    protected function getPath()
    {
        $m = Yii::$app->request->get('m');
        $sql = new MysqlBackup($m);
        return $sql->getPath();
    }

    public function actionCreate($m = null, $data = 1)
    {
        $sql = new MysqlBackup();
        $sql->setModule($m);

        $tables = $sql->getTables();
        
        var_dump($tables); exit;

        if (! $sql->startBackup()) {

            // render error
            Yii::$app->user->setFlash('success', "Error");
            return $this->render('index');
        }

        foreach ($tables as $tableName) {
            try {

                $sql->getColumns($tableName);
            } catch (\Exception $e) {
                echo $e->getTraceAsString();
            }
        }
        if ($data) {
            foreach ($tables as $tableName) {
                try {
                    $sql->getData($tableName);
                } catch (\Exception $e) {
                    echo $e->getTraceAsString();
                }
            }
        }

        $sql->endBackup();

        $this->redirect(array(
            'index'
        ));
    }

    public function actionClean($redirect = true)
    {
        $ignore = array(
            'tbl_user',
            'tbl_user_role',
            'tbl_event'
        );

        // logout so there is no problme later .
        Yii::$app->user->logout();

        $sql = new MysqlBackup();

        $sql->clean($ignore);

        $message .= ' are deleted.';
        Yii::$app->session->setFlash('success', $message);
        return $this->redirect(array(
            'index'
        ));
    }

    public function actionDelete($file)
    {
        $list = $this->getFileList($file);
        $file = $list[0];

        $this->updateMenuItems();
        if (isset($file)) {

            $sqlFile = $this->path . basename($file);

            if (is_file($sqlFile))

                unlink($sqlFile);
        } else
            throw new HttpException(404, Yii::t('app', 'File not found'));
        return $this->redirect(\yii::$app->request->referrer);
    }

    protected function getFileList($ext = '*.sql')
    {
        $path = $this->path;
        echo $path;
        $dataArray = array();
        $list = array();
        $list_files = glob($path . $ext);
        if ($list_files) {
            $list = array_map('basename', $list_files);
            sort($list);
        }
        return $list;
    }

    public function actionIndex()
    {
        $this->layout = null;
        $this->updateMenuItems();

        $list = Yii::$app->getModules();

        $dataArray = [];
        foreach ($list as $module => $class) {
            $columns = array();
            $columns['id'] = $module;
            $columns['name'] = $module;

            $dataArray[] = $columns;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $dataArray
        ]);

        return $this->render('index', array(
            'dataProvider' => $dataProvider
        ));
    }

    public function actionList($m)
    {
        $this->layout = null;
        $this->updateMenuItems();

        $list = $this->getFileList();

        $list = array_merge($list, $this->getFileList('*.zip'));

        $dataArray = [];
        foreach ($list as $id => $filename) {
            $columns = array();
            $columns['id'] = $id;
            $columns['name'] = basename($filename);
            $columns['size'] = filesize($this->path . $filename);

            $columns['create_time'] = date('Y-m-d H:i:s', filectime($this->path . $filename));
            $columns['modified_time'] = date('Y-m-d H:i:s', filemtime($this->path . $filename));
            if (date('M-d-Y' . ' \a\t ' . ' g:i A', filemtime($this->path . $filename)) > date('M-d-Y' . ' \a\t ' . ' g:i A', filectime($this->path . $filename))) {
                $columns['modified_time'] = date('M-d-Y' . ' \a\t ' . ' g:i A', filemtime($this->path . $filename));
            }

            $dataArray[] = $columns;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => array_reverse($dataArray),
            'sort' => [
                'attributes' => [
                    'modified_time' => SORT_ASC
                ]
            ]
        ]);

        return $this->render('index', array(
            'dataProvider' => $dataProvider
        ));
    }

    public function actionRestore($file = null)
    {
        ini_set('max_execution_time', 0);
        // ini_set('memory_limit', '8192M');
        $message = 'OK';
        $this->layout = null;
        $this->updateMenuItems();
        $list = $this->getFileList();
        $list = array_merge($list, $this->getFileList('*.zip'));
        foreach ($list as $id => $filename) {
            $columns = array();
            $columns['id'] = $id;
            $columns['name'] = basename($filename);
            $columns['size'] = filesize($this->path . $filename);
            $columns['create_time'] = date('Y-m-d H:i:s', filectime($this->path . $filename));
            $columns['modified_time'] = date('Y-m-d H:i:s', filemtime($this->path . $filename));
            if (date('M-d-Y' . ' \a\t ' . ' g:i A', filemtime($this->path . $filename)) > date('M-d-Y' . ' \a\t ' . ' g:i A', filectime($this->path . $filename))) {
                $columns['modified_time'] = date('M-d-Y' . ' \a\t ' . ' g:i A', filemtime($this->path . $filename));
            }
            $dataArray[] = $columns;
        }

        ArrayHelper::multisort($dataArray, 'create_time', SORT_DESC);
        if (count($dataArray) > 0) {
            $last_time = $dataArray['0']['create_time'];
            $current = date('Y-m-d H:i:s');
            $diff = (strtotime($current) - strtotime($last_time)) / 60;
            if ($diff > 10) {
                $this->actionCreate();
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $dataArray
        ]);

        if (isset($file)) {
            $sql = new MysqlBackup();
            $sqlZipFile = $this->path . basename($file);
            $sqlFile = $sql->unzip($sqlZipFile);
            $message = $sql->execSqlFile($sqlFile);
            if ($message == 'OK')
                \yii::$app->session->setFlash('success', 'Restored Successfully.');
            else
                \yii::$app->session->setFlash('success', $message);
        } else {
            \yii::$app->session->setFlash('success', 'Select a file.');
            $message = 'NOK';
        }

        return $this->render('restore', array(
            'error' => $message,
            'dataProvider' => $dataProvider
        ));
    }

    public function actionUpload()
    {
        // $this->layout='main';
        $model = new UploadForm();
        if (isset($_POST['UploadForm'])) {
            $model->attributes = $_POST['UploadForm'];
            $model->upload_file = \yii\web\UploadedFile::getInstance($model, 'upload_file');
            if ($model->upload_file->saveAs($this->path . $model->upload_file)) {
                // redirect to success page
                return $this->redirect(array(
                    'index'
                ));
            }
        }

        return $this->render('upload', array(
            'model' => $model
        ));
    }

    protected function updateMenuItems($model = null)
    {
        $m = Yii::$app->request->get('m');

        switch ($this->action->id) {
            case 'restore':
                {
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'View Site'),
                        'url' => Yii::$app->HomeUrl
                    );
                }
            case 'create':
                {
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'List Backup'),
                        'url' => array(
                            'index'
                        )
                    );
                }
                break;
            case 'upload':
                {
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'Create Backup'),
                        'url' => array(
                            'create'
                        )
                    );
                }
                break;

            default:
                {
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'List Backup'),
                        'url' => array(
                            'index'
                        )
                    );
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'Create Backup'),
                        'url' => array(
                            'create',
                            Yii::$app->request->getQueryParams()
                        )
                    );
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'Upload Backup'),
                        'url' => array(
                            'upload'
                        )
                    );
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'Restore Backup'),
                        'url' => array(
                            'restore'
                        )
                    );
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'Clean Database'),
                        'url' => array(
                            'clean'
                        )
                    );
                    $this->menu[] = array(
                        'label' => Yii::t('app', 'View Site'),
                        'url' => Yii::$app->HomeUrl
                    );
                }
                break;
        }
    }
}