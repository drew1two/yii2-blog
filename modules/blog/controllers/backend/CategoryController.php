<?php

namespace modules\blog\controllers\backend;

use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Throwable;
use modules\rbac\models\Permission;
use modules\blog\models\Category;
use modules\blog\models\search\CategorySearch;
use modules\blog\behaviors\CategoryTreeBehavior;
use common\components\behaviors\DelCacheControllerBehavior;

/**
 * Class CategoryController
 * @package modules\blog\controllers\backend
 */
class CategoryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Permission::PERMISSION_MANAGER_POST]
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'delCacheControllerBehavior' => [
                'class' => DelCacheControllerBehavior::class,
                'actions' => ['create', 'update', 'move', 'change-status', 'delete'],
                'tags' => [CategoryTreeBehavior::CACHE_TAG_CATEGORY]
            ]
        ];
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionCreate()
    {
        $model = new Category();
        if (($post = Yii::$app->request->post()) && $model->load($post) && $model->validate()) {
            if (empty($model->parentId)) {
                $model->makeRoot()->save();
            } else {
                /** @var Category $node */
                $node = Category::findOne(['id' => $model->parentId]);
                $model->position = $node->position;
                $model->appendTo($node)->save();
            }
            // Перемещаем в пределах узла
            $model = $this->moveWithinNode($model);
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->position = $model->position ?: Category::POSITION_DEFAULT;
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (($post = Yii::$app->request->post()) && $model->load($post) && $model->save()) {
            Category::changeStatusChildren($model->id);
            Category::changePositionChildren($model->id);
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->position = $model->position ?: Category::POSITION_DEFAULT;
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Return children list
     * @return array|Response
     */
    public function actionChildrenList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($post = Yii::$app->request->post()) {
                $selectList = Category::getChildrenList($post['parent'], $post['id']);
                return [
                    'result' => $this->renderPartial('ajax/selectList', ['selectList' => $selectList]),
                ];
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Move node
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionMove($id)
    {
        $model = $this->findModel($id);
        if (($post = Yii::$app->request->post()) && $model->load($post)) {
            if (empty($model->parentId)) { // Перемещаем как корень
                if (!$model->isRoot()) {
                    $model->makeRoot()->save(false);
                }
            } elseif ($model->id !== $model->parentId) { // Перемещаем в указанный узел
                $node = $this->findModel($model->parentId);
                $model->appendTo($node)->save(false);
            }
            // Перемещаем в пределах узла
            $this->moveWithinNode($model);
            return $this->redirect(['index']);
        }

        $model->parentId = $model->getParentId();
        if ($select = $model->getPrevNodeId()) {
            $typeMove = Category::TYPE_AFTER;
        } elseif ($select = $model->getNextNodeId()) {
            $typeMove = Category::TYPE_BEFORE;
        } else {
            $typeMove = null;
        }

        $model->childrenList = $select;
        $model->typeMove = $typeMove;
        return $this->render('move', [
            'model' => $model,
        ]);
    }

    /**
     * Change status
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Category::SCENARIO_SET_STATUS;
        $model->setStatus();
        if ($model->save(false)) {
            Category::changeStatusChildren($model->id);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->isRoot() ? $model->deleteWithChildren() : $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Move within a node
     * @param Category $model
     * @return Category
     * @throws NotFoundHttpException
     */
    protected function moveWithinNode(Category $model)
    {
        if ($model !== null && !empty($model->childrenList)) {
            $moveModel = $this->findModel($model->id);
            $node = $this->findModel($model->childrenList);
            switch ($model->typeMove) {
                case Category::TYPE_BEFORE:
                    $moveModel->insertBefore($node)->save(false);
                    break;
                case Category::TYPE_AFTER:
                    $moveModel->insertAfter($node)->save(false);
                    break;
                default:
                    $moveModel->insertAfter($node)->save(false);
            }
        }
        return $model;
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
