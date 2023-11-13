<?php
declare(strict_types=1); 

namespace frontend\models;

use yii\base\Model;

/**
 * Observer form
 */
class AssignObserverRoleForm extends Model
{
    public $user_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['user_id', 'required'],
        ];
    }

    /**
     * User selected
     *
     * @return bool whether the user was selected
     */
    public function user_validated()
    {
        if (!$this->validate()) {
            return null;
        }
        return true;
    }
}
