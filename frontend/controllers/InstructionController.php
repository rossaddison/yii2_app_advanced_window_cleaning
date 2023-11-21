<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use frontend\models\Instruction;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class InstructionController extends Controller
{
    
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' =>VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'timestamp' => 
                [
                'class' =>TimestampBehavior::class,
                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['modified_date'],
                                    ActiveRecord::EVENT_BEFORE_UPDATE => ['modified_date'],
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
                  'allow' => true,
                  'verbs' => ['POST']
                ],  
                ],
            ], 
        ];
    }

    /**
     * 
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Instruction::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
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
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Instruction();

        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getId()]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getId()]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 
     * @param int $id
     * @return Response
     * @throws \yii\web\HttpException
     */
    public function actionDelete(int $id)
    {
      try{
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
        } catch(\yii\db\IntegrityException $e) {
              throw new \yii\web\HttpException(404, Yii::t('app','First delete the daily clean detail where this instruction has been linked to then you will be able to delete this file. Exception: ').$e);
        }
    }

    /**
     * 
     * @param int $id
     * @return Instruction
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Instruction::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
