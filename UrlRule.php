<?php

namespace dee\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\web\CompositeUrlRule;

/**
 * Description of UrlRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class UrlRule extends \yii\rest\UrlRule
{
    /**
     * @var string the common prefix string shared by all patterns.
     */
    public $prefixRoute;

    /**
     * @inheritdoc
     */
    public $patterns = [
        'PUT {id}' => 'update',
        'PATCH {id}' => 'patch',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'index',
        '{id}' => 'options',
        '' => 'options',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->controller)) {
            throw new InvalidConfigException('"controller" must be set.');
        }

        $controllers = [];
        $prefixRoute = empty($this->prefixRoute) ? '' : trim($this->prefixRoute, '/') . '/';
        foreach ((array) $this->controller as $urlName => $controller) {
            if (is_int($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $controllers[$urlName] = $prefixRoute . $controller;
        }
        $this->controller = $controllers;

        $this->prefix = trim($this->prefix, '/');

        CompositeUrlRule::init();
    }
}