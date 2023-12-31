<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use frontend\models\Carousal;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * CarousalController implements the CRUD actions for Carousal model.
 */
class CarousalController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' =>VerbFilter::class,
                'actions' => [
                   // 'delete' => ['POST'],
                ],
            ],
           'access' => 
                [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['index','create', 'update','delete','view'],
                'rules' => [
                [
                  'allow' => true,
                  'roles' => ['admin'],
                ],
                [
                  'allow' => false,
                  'roles' => ['?'],
                ],  
                [
                  'allow' => true,
                  'verbs' => ['POST']
                ],  
                ],
            ], 
        ];
    }

    /**
     * Lists all Carousal models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Carousal::find(),
        ]);
        //Yii::$app->params['uploadPath'] = Yii::getAlias('@app\images');
        //Yii::$app->params['uploadUrl'] = Yii::base();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Carousal model.
     * @param integer $id
     * @return string
     */
    public function actionView(int $id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Carousal();
        if ($model->load(Yii::$app->request->post())) {
            $uploadedFile = UploadedFile::getInstance($model, 'image');
            if (!is_null($uploadedFile)) {
                $model->image_source_filename = $uploadedFile->name;
                $model->image_web_filename = Yii::$app->security->generateRandomString().".".$uploadedFile->extension;
                if ($model->validate()) { 
                    $basepath = \Yii::getAlias('@webroot');
                    if (Yii::$app->user->identity->attributes['username']  === 'demo') {
                       $path = $basepath . "/images/demo/".Yii::$app->session['demo_image_timestamp_directory']."/". $model->image_web_filename;
                    }
                    else
                    {
                       $path = $basepath . "/images/" . $model->image_web_filename;
                    }
                    $uploadedFile->saveAs($path); 
                }                   
            }
            if ($model->save())
            {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('create', ['model' => $model,
        ]);
    }
   
   /**
    * @param int $id
    * @return mixed
    */   
   public function actionUpdate(int $id)
   {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $uploadedFile = UploadedFile::getInstance($model, 'image');
            if (!is_null($uploadedFile)) {
                $model->image_source_filename = $uploadedFile->name;
                $model->image_web_filename = Yii::$app->security->generateRandomString().".".$uploadedFile->extension;
                if ($model->validate()) {                
                    Yii::$app->params['uploadPath'] = Yii::$app->basePath;
                    $basepath = \Yii::getAlias('@webroot');
                    if (Yii::$app->user->identity->attributes['username']  === 'demo') {
                       $path = $basepath . "/images/demo/".Yii::$app->session['demo_image_timestamp_directory']."/". $model->image_web_filename;
                    }
                    else {
                       $path = $basepath . "/images/" . $model->image_web_filename;   
                    }
                    $uploadedFile->saveAs($path); 
                }                   
            }
            if ($model->save())
            {
                $id = (int)$model->id;
                return $this->redirect(['view', 'id' => $id]);
            } else {
                return $this->render('update', ['model' => $model]);
            }
        }
        return $this->render('update', ['model' => $model]);
    }    
    
    /**
     * @param int $id
     * @return Carousal
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id) : Carousal
    {
        if (($model = Carousal::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException((string)Yii::t('app','The requested page does not exist.'));
        }
    }
    
    /**
     * 
     * @param int $id
     * @return Response|string
     * @throws \yii\web\HttpException
     */
    public function actionDelete(int $id)
    {
        try{
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
        } catch(\yii\db\IntegrityException $e) {
            $merge = Yii::t('app','This image or file is linked. You will have to remove this link first. Exception: '). (string)$e;
            throw new \yii\web\HttpException(404, $merge);
        }
    }
}
