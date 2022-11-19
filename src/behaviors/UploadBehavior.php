<?php

namespace sadi01\moresettings\behaviors;

use Closure;
use yii\db\ActiveRecord;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * UploadBehavior automatically uploads file and fills the specified attribute
 * with a value of the name of the uploaded file.
 *
 * To use UploadBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use sadi01\moresettings\behaviors\UploadBehavior;
 *
 * function behaviors()
 * {
 *     return [
 *         [
 *             'class' => UploadBehavior::className(),
 *             'attribute' => 'file',
 *             'scenarios' => ['insert', 'update'],
 *             'path' => '@webroot/upload/{id}',
 *             'url' => '@web/upload/{id}',
 *         ],
 *     ];
 * }
 * ```
 */
class UploadBehavior extends Behavior
{
    /**
     * @event Event an event that is triggered after a file is uploaded.
     */
    const EVENT_AFTER_UPLOAD = 'afterUpload';

    /**
     * @var string|\closure the attribute which holds the attachment.
     * The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model) {
     *     // compute attribute
     *     return $attribute;
     * }
     * ```
     */
    public $attribute;

    /**
     * @var string|\closure old value of attribute.
     * The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model) {
     *     // compute oldAttribute
     *     return $oldAttribute;
     * }
     * ```
     */
    public $oldAttribute;

    /**
     * @var array extra attributes.
     */
    public $extraAttributes;
    /**
     * @var array the scenarios in which the behavior will be triggered
     */
    public $scenarios = [];
    /**
     * @var string the path or path alias to the directory in which to save files.
     */
    public $path;
    /**
     * @var string the base path or path alias to the directory in which to save files.
     */
    public $basePath;
    /**
     * @var string the base URL or path alias for this file
     */
    public $url;
    /**
     * @var bool Getting file instance by name
     */
    public $instanceByName = false;
    /**
     * @var string name of instance file
     */
    public $instanceName;
    /**
     * @var boolean|callable generate a new unique name for the file
     * set true or anonymous function takes the old filename and returns a new name.
     * @see self::generateFileName()
     */
    public $generateNewName = true;
    /**
     * @var boolean If `true` current attribute file will be deleted
     */
    public $unlinkOnSave = true;
    /**
     * @var boolean If `true` current attribute file will be deleted after model deletion.
     */
    public $unlinkOnDelete = true;
    /**
     * @var boolean $deleteTempFile whether to delete the temporary file after saving.
     */
    public $deleteTempFile = true;
    /**
     * @var boolean $deleteBasePathOnDelete whether to delete the basePath directory after delete owner model.
     */
    public $deleteBasePathOnDelete = false;

    /**
     * @var UploadedFile the uploaded file instance.
     */
    private $_file;

    /**
     * @var string
     */
    private $isAttributeChanged = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            throw new InvalidConfigException('The "attribute" property must be set.');
        }
        if ($this->path === null) {
            throw new InvalidConfigException('The "path" property must be set.');
        }
        if ($this->deleteBasePathOnDelete && $this->basePath === null) {
            throw new InvalidConfigException('The "basePath" property must be set.');
        }
        if ($this->url === null) {
            throw new InvalidConfigException('The "url" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * This method is invoked before validation starts.
     */
    public function beforeValidate()
    {
        if ($this->attribute instanceof \Closure) {
            $this->attribute = call_user_func($this->attribute, $this->owner);
        }
        if ($this->attribute === null) return false;

        /** @var ActiveRecord $model */
        $model = $this->owner;
        $file = null;

        if ($model->hasAttribute($this->attribute)) {
            $file = $model->getAttribute($this->attribute);
        } elseif (in_array($this->attribute, $this->extraAttributes)) {
            $file = $model->{$this->attribute};
        }

        if (in_array($model->scenario, $this->scenarios)) {
            if ($file instanceof UploadedFile) {
                $this->_file = $file;
            } else {
                if ($this->instanceByName === true) {
                    $this->_file = UploadedFile::getInstanceByName($this->instanceName ? $this->instanceName : $this->attribute);
                } else {
                    $this->_file = UploadedFile::getInstance($model, $this->attribute);
                }
            }
            if ($this->_file instanceof UploadedFile) {
                $this->_file->name = $this->getFileName($this->_file);
                if ($model->hasAttribute($this->attribute)) {
                    $model->setAttribute($this->attribute, $this->_file);
                } elseif (in_array($this->attribute, $this->extraAttributes)) {
                    $model->{$this->attribute} = $this->_file;
                }
            }
        }
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     */
    public function beforeSave()
    {
        if ($this->attribute === null) return false;

        /** @var ActiveRecord $model */
        $model = $this->owner;

        if ($this->oldAttribute instanceof \Closure) {
            $this->oldAttribute = call_user_func($this->oldAttribute, $model);
        }

        if ($model->hasAttribute($this->attribute)) {
            $this->isAttributeChanged = $model->isAttributeChanged($this->attribute);
        } elseif (in_array($this->attribute, $this->extraAttributes)) {
            $this->isAttributeChanged = ($this->oldAttribute !== $model->{$this->attribute});
        }

        if (in_array($model->scenario, $this->scenarios)) {
            if ($this->_file instanceof UploadedFile) {
                if (!$model->getIsNewRecord() && $this->isAttributeChanged) {
                    if ($this->unlinkOnSave === true) {
                        $this->delete($this->attribute, true);
                    }
                }
                if ($model->hasAttribute($this->attribute)) {
                    $model->setAttribute($this->attribute, $this->_file->name);
                } elseif (in_array($this->attribute, $this->extraAttributes)) {
                    $model->{$this->attribute} = $this->_file->name;
                }

            } else {
                // Protect attribute
                unset($model->{$this->attribute});
            }
        } else {
            if (!$model->getIsNewRecord() && $this->isAttributeChanged) {
                if ($this->unlinkOnSave === true) {
                    $this->delete($this->attribute, true);
                }
            }
        }
    }

    /**
     * This method is called at the end of inserting or updating a record.
     * @throws \yii\base\InvalidParamException
     */
    public function afterSave()
    {
        if ($this->attribute === null) return false;

        if ($this->_file instanceof UploadedFile) {
            $path = $this->getUploadPath($this->attribute);
            if (is_string($path) && FileHelper::createDirectory(dirname($path))) {
                $this->save($this->_file, $path);
                $this->afterUpload();
            } else {
                throw new InvalidParamException("Directory specified in 'path' attribute doesn't exist or cannot be created.");
            }
        }
    }

    /**
     * This method is invoked before deleting a record.
     */
    public function beforeDelete()
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;

        if ($this->attribute instanceof \Closure) {
            $this->attribute = call_user_func($this->attribute, $model);
        }

        if ($this->oldAttribute instanceof \Closure) {
            $this->oldAttribute = call_user_func($this->oldAttribute, $model);
        }
    }

    /**
     * This method is invoked after deleting a record.
     */
    public function afterDelete()
    {
        $attribute = $this->attribute;
        if ($this->unlinkOnDelete) {
            (!$attribute || $this->deleteBasePathOnDelete) ? $this->deleteDir(): $this->delete($attribute);
        }
    }

    /**
     * Returns file path for the attribute.
     * @param string $attribute
     * @param boolean $old
     * @return string|null the file path.
     */
    public function getUploadPath($attribute, $old = false)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;

        $path = $this->resolvePath($this->path);
        if ($model->hasAttribute($attribute)) {
            $fileName = ($old === true) ? $model->getOldAttribute($attribute) : $model->$attribute;
        } elseif (in_array($attribute, $this->extraAttributes)) {
            $fileName = ($old === true) ? $this->oldAttribute : $model->$attribute;
        }

        return $fileName ? Yii::getAlias($path . '/' . $fileName) : null;
    }

    /**
     * Returns file url for the attribute.
     * @param string $attribute
     * @return string|null
     */
    public function getUploadUrl($attribute)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        $url = $this->resolvePath($this->url);
        $fileName = '';
        if ($model->hasAttribute($attribute)) {
            $fileName = $model->getOldAttribute($attribute);
        } elseif (in_array($attribute, $this->extraAttributes)) {
            $fileName = $model->$attribute;
        }

        return $fileName ? Yii::getAlias($url . '/' . $fileName) : null;
    }

    /**
     * Returns the UploadedFile instance.
     * @return UploadedFile
     */
    protected function getUploadedFile()
    {
        return $this->_file;
    }

    /**
     * Replaces all placeholders in path variable with corresponding values.
     */
    protected function resolvePath($path)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        return preg_replace_callback('/{([^}]+)}/', function ($matches) use ($model) {
            $name = $matches[1];
            $attribute = ArrayHelper::getValue($model, $name);
            if (is_string($attribute) || is_numeric($attribute)) {
                return $attribute;
            } else {
                return $matches[0];
            }
        }, $path);
    }

    /**
     * Saves the uploaded file.
     * @param UploadedFile $file the uploaded file instance
     * @param string $path the file path used to save the uploaded file
     * @return boolean true whether the file is saved successfully
     */
    protected function save($file, $path)
    {
        return $file->saveAs($path, $this->deleteTempFile);
    }

    /**
     * Deletes old file.
     * @param string $attribute
     * @param boolean $old
     */
    protected function delete($attribute, $old = false)
    {
        $path = $this->getUploadPath($attribute, $old);
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Deletes basePath directory.
     */
    protected function deleteDir()
    {
        if ($this->basePath) {
            $basePath = Yii::getAlias($this->resolvePath($this->basePath));
            FileHelper::removeDirectory($basePath);
        }
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getFileName($file)
    {
        if ($this->generateNewName) {
            return $this->generateNewName instanceof Closure
                ? call_user_func($this->generateNewName, $file)
                : $this->generateFileName($file);
        } else {
            return $this->sanitize($file->name);
        }
    }

    /**
     * Replaces characters in strings that are illegal/unsafe for filename.
     *
     * #my*  unsaf<e>&file:name?".png
     *
     * @param string $filename the source filename to be "sanitized"
     * @return boolean string the sanitized filename
     */
    public static function sanitize($filename)
    {
        return str_replace([' ', '"', '\'', '&', '/', '\\', '?', '#'], '-', $filename);
    }

    /**
     * Generates random filename.
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFileName($file)
    {
        return uniqid() . '.' . $file->extension;
    }

    /**
     * This method is invoked after uploading a file.
     * The default implementation raises the [[EVENT_AFTER_UPLOAD]] event.
     * You may override this method to do postprocessing after the file is uploaded.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterUpload()
    {
        $this->owner->trigger(self::EVENT_AFTER_UPLOAD);
    }
}
