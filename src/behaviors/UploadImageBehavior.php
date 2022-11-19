<?php

namespace common\behaviors;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\imagine\Image;

/**
 * UploadImageBehavior automatically uploads image, creates thumbnails and fills
 * the specified attribute with a value of the name of the uploaded image.
 *
 * To use UploadImageBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use common\behaviors\UploadImageBehavior;
 *
 * function behaviors()
 * {
 *     return [
 *         [
 *             'class' => UploadImageBehavior::className(),
 *             'attribute' => 'file',
 *             'scenarios' => ['insert', 'update'],
 *             'placeholder' => '@app/modules/user/assets/images/userpic.jpg',
 *             'path' => '@webroot/upload/{id}/images',
 *             'url' => '@web/upload/{id}/images',
 *             'thumbPath' => '@webroot/upload/{id}/images/thumb',
 *             'thumbUrl' => '@web/upload/{id}/images/thumb',
 *             'thumbs' => [
 *                   'thumb' => ['width' => 400, 'quality' => 90],
 *                   'preview' => ['width' => 200, 'height' => 200],
 *              ],
 *         ],
 *     ];
 * }
 * ```
 */
class UploadImageBehavior extends UploadBehavior
{
    /**
     * @var string
     */
    public $placeholder;
    /**
     * @var boolean
     */
    public $createThumbsOnSave = true;
    /**
     * @var boolean
     */
    public $createThumbsOnRequest = false;
    /**
     * @var array the thumbnail profiles
     * - `width`
     * - `height`
     * - `quality`
     */
    public $thumbs = [
        'thumb' => ['width' => 200, 'height' => 200, 'quality' => 90],
    ];

    protected $videoMimeTypes = ['video/mp4', 'video/x-msvideo', 'video/mpeg', 'video/x-matroska'];
    /**
     * @var string|null
     */
    public $thumbPath;
    /**
     * @var string|null
     */
    public $thumbUrl;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->createThumbsOnSave) {
            if ($this->thumbPath === null) {
                $this->thumbPath = $this->path;
            }
            if ($this->thumbUrl === null) {
                $this->thumbUrl = $this->url;
            }

            foreach ($this->thumbs as $config) {
                $width = ArrayHelper::getValue($config, 'width');
                $height = ArrayHelper::getValue($config, 'height');
                if ($height < 1 && $width < 1) {
                    throw new InvalidConfigException(sprintf(
                        'Length of either side of thumb cannot be 0 or negative, current size ' .
                        'is %sx%s', $width, $height
                    ));
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function afterUpload()
    {
        parent::afterUpload();
        if ($this->createThumbsOnSave) {
            $this->createThumbs();
        }
    }

    /**
     * @throws \yii\base\InvalidParamException
     */
    protected function createThumbs()
    {
        $path = $this->getUploadPath($this->attribute);
        foreach ($this->thumbs as $profile => $config) {
            $thumbPath = $this->getThumbUploadPath($this->attribute, $profile);
            if ($thumbPath !== null) {
                if (!FileHelper::createDirectory(dirname($thumbPath))) {
                    throw new InvalidParamException("Directory specified in 'thumbPath' attribute doesn't exist or cannot be created.");
                }
                if (!is_file($thumbPath)) {
                    $fileMimeType = FileHelper::getMimeType($path);
                    if (in_array($fileMimeType, $this->videoMimeTypes)) {
                        $this->generateVideoThumb($config, $path, $thumbPath);
                    } else {
                        $this->generateImageThumb($config, $path, $thumbPath);
                    }
                }
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @param boolean $old
     * @return string
     */
    public function getThumbUploadPath($attribute, $profile = 'thumb', $old = false)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        $path = $this->resolvePath($this->thumbPath);

        if ($model->hasAttribute($attribute)) {
            $attribute = ($old === true) ? $model->getOldAttribute($attribute) : $model->$attribute;
        } elseif (in_array($attribute, $this->extraAttributes)) {
            $attribute = ($old === true) ? $this->oldAttribute : $model->$attribute;
        }

        $filename = $this->getThumbFileName($attribute, $profile);

        $attributePath = Yii::getAlias($this->resolvePath($this->path)) . '/' . $attribute;
        $fileMimeType = is_file($attributePath) ? FileHelper::getMimeType($attributePath) : null;

        if (in_array($fileMimeType, $this->videoMimeTypes)) {
            return $filename ? Yii::getAlias($path . '/' . $filename . '.png') : null;
        } else {
            return $filename ? Yii::getAlias($path . '/' . $filename) : null;
        }
    }

    /**
     * @param string $attribute
     * @param string $profile
     * @return string|null
     */
    public function getThumbUploadUrl($attribute, $profile = 'thumb')
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;
        $path = $this->getUploadPath($attribute, true);
        if (is_file($path)) {
            if ($this->createThumbsOnRequest) {
                $this->createThumbs();
            }
            $url = $this->resolvePath($this->thumbUrl);
            $fileName = '';
            if ($model->hasAttribute($attribute)) {
                $fileName = $model->getOldAttribute($attribute);
            } elseif (in_array($attribute, $this->extraAttributes)) {
                $fileName = $model->$attribute;
            }

            $thumbName = $this->getThumbFileName($fileName, $profile);

            $fileMimeType = FileHelper::getMimeType($path);
            if (in_array($fileMimeType, $this->videoMimeTypes)) {
                return Yii::getAlias($url . '/' . $thumbName . '.png');
            } else {
                return Yii::getAlias($url . '/' . $thumbName);
            }

        } elseif ($this->placeholder) {
            return $this->getPlaceholderUrl($profile);
        } else {
            return null;
        }
    }

    /**
     * @param $profile
     * @return string
     */
    protected function getPlaceholderUrl($profile)
    {
        list ($path, $url) = Yii::$app->assetManager->publish($this->placeholder);

        $filename = basename($path);
        $thumb = $this->getThumbFileName($filename, $profile);
        $thumbPath = dirname($path) . DIRECTORY_SEPARATOR . $thumb;
        $thumbUrl = dirname($url) . '/' . $thumb;

        if (!is_file($thumbPath)) {
            $this->generateImageThumb($this->thumbs[$profile], $path, $thumbPath);
        }

        return $thumbUrl;
    }

    /**
     * @inheritdoc
     */
    protected function delete($attribute, $old = false)
    {
        $profiles = array_keys($this->thumbs);
        foreach ($profiles as $profile) {
            $path = $this->getThumbUploadPath($attribute, $profile, $old);
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::delete($attribute, $old);
    }

    /**
     * @param $filename
     * @param string $profile
     * @return string
     */
    protected function getThumbFileName($filename, $profile = 'thumb')
    {
        return $profile . '-' . $filename;
    }

    /**
     * @param $config
     * @param $path
     * @param $thumbPath
     */
    protected function generateVideoThumb($config, $path, $thumbPath)
    {
        $ffmpegConfig = (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') ? [
            'ffmpeg.binaries' => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'ffprobe.binaries' => 'C:\\ffmpeg\\bin\\ffprobe.exe'
        ] : [];

        $ffmpeg = FFMpeg::create($ffmpegConfig);
        $video = $ffmpeg->open($path);
        $video
            ->frame(TimeCode::fromSeconds(5))
            ->save($thumbPath);

        if (is_file($thumbPath)) {
            $this->generateImageThumb($config, $thumbPath, $thumbPath);
        }
    }

    /**
     * @param $config
     * @param $path
     * @param $thumbPath
     */
    protected function generateImageThumb($config, $path, $thumbPath)
    {
        $width = ArrayHelper::getValue($config, 'width');
        $height = ArrayHelper::getValue($config, 'height');
        $quality = ArrayHelper::getValue($config, 'quality', 100);
        $mode = ArrayHelper::getValue($config, 'mode', ManipulatorInterface::THUMBNAIL_INSET);
        $bg_color = ArrayHelper::getValue($config, 'bg_color', 'FFF');

        if (!$width || !$height) {
            $image = Image::getImagine()->open($path);
            $ratio = $image->getSize()->getWidth() / $image->getSize()->getHeight();
            if ($width) {
                $height = ceil($width / $ratio);
            } else {
                $width = ceil($height * $ratio);
            }
        }

        // Fix error "PHP GD Allowed memory size exhausted".
        ini_set('memory_limit', '512M');
        Image::$thumbnailBackgroundColor = $bg_color;
        Image::thumbnail($path, $width, $height, $mode)->save($thumbPath, ['quality' => $quality]);
    }
}
