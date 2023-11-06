<?php
declare(strict_types=1);

namespace frontend\controllers;

/**
 * @psalm-suppress UndefinedClass Yii
 */
use Yii;

use frontend\models\Tax;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\db\IntegrityException;

class TaxController extends Controller
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
                    'delete' => ['POST'],
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
                ],
            ], 
        ];
    }

    /**
     * Lists all Tax models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Tax::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tax model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Tax model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tax();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $tax_id =  $model->tax_id;
            return $this->redirect(['view', 'id' => $tax_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id) 
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $tax_id =  $model->tax_id;
            return $this->redirect(['view', 'id' => $tax_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
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
        } catch(IntegrityException $e) {
              $merge = Yii::t('app','First delete Daily cleans or costs that this tax code has been' 
                      . 'linked to then you will be able to delete this tax code. Exception: '). (string)$e;
              throw new \yii\web\HttpException(404, $merge);
        }
    }

    /**
     * 
     * @param int $id
     * @return Tax
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id) : Tax
    {
        if (($model = Tax::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException((string)Yii::t('app','The requested page does not exist.'));
        }
    }
}
