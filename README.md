<p align="center">
        <img src="https://raw.githubusercontent.com/Sadi01/yii2-more-settings/master/src/img/settings.png" height="80px">
    <h1 align="center">Handle settings for Yii 2</h1>
    <br>
</p>

For license information check the [LICENSE](LICENSE.md)-file.

Installation
------------

### 1. Download

The preferred way to install this extension is through [composer](http://getcomposer.org/download/):

```
composer require --prefer-dist sadi01/yii2-more-settings:"*"
```

### 2. Configuration

To use this extension, you have to configure the PostService class in your application configuration:

```php
return [
    //....
    'modules' => [
        'moresettings' => [
            'class' => 'sadi01\moresettings\SettingsModule',
            'rootAlias' => '@webroot',
            'webAlias' => '@web',
        ],
    ]
];
```

### 3. Update database schema

The last thing you need to do is updating your database schema by applying the
migrations. Make sure that you have properly configured `db` application component
and run the following command:

```bash
$ php yii migrate/up --migrationPath=@vendor/sadi01/yii2-more-settings/src/migrations
```


How To Use
-------------

Manage setting categories and settings :
```php
http://yourdomain/moresettings/default/index
http://yourdomain/moresettings/setting-cat/index
```

Get value of setting:
```php
use sadi01\moresettings\models\SettingValue;

SettingValue::getValue('settingCategory', 'settingName')
```

Setting widget for change value of setting:
```php
use sadi01\moresettings\widgets\SettingsWidget

<?=
SettingsWidget::widget([
    'model' => $model,
    'categoryName' => 'mainSettings', // name of Setting category
    'settingName' => 'apiBaseUrl' // name of setting
])
?>
```