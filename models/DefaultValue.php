<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%cloud_drive}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $google_file_link
 * @property string|null $yandex_file_path
 * @property string|null $general_message_template
 * @property string|null $message_template
 * @property int $user
 *
 * @property User $user_fk
 */
class DefaultValue extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%default_value}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['user'], 'required'],
            [['user'], 'integer'],
            [['google_file_link', 'yandex_file_path', 'general_message_template', 'message_template'], 'string'],
            [['user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(),
                'targetAttribute' => ['user' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создано',
            'updated_at' => 'Изменено',
            'google_file_link' => 'Ссылка на файл электронной таблицы на Google-диске',
            'yandex_file_path' => 'Путь к файлу электронной таблицы на Yandex-диске',
            'general_message_template' => 'Шаблон общего сообщения сотрудникам',
            'message_template' => 'Шаблон сообщения сотрудникам',
            'user' => 'Пользователь',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserFk()
    {
        return $this->hasOne(User::className(), ['id' => 'user']);
    }
}