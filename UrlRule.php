<?php

namespace dee\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;

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
    public $routePrefix;

    /**
     * @var array list of possible patterns and the corresponding actions for creating the URL rules.
     * The keys are the patterns and the values are the corresponding actions.
     * The format of patterns is `Verbs Pattern`, where `Verbs` stands for a list of HTTP verbs separated
     * by comma (without space). If `Verbs` is not specified, it means all verbs are allowed.
     * `Pattern` is optional. It will be prefixed with [[prefix]]/[[controller]]/,
     * and tokens in it will be replaced by [[tokens]].
     */
    public $patterns = [
        'PUT,POST {id}' => 'update',
        'PATCH {id}' => 'patch',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'index',
        '{id}' => 'options',
        '' => 'options',
    ];

    /**
     * @var boolean whether to automatically pluralize the URL names for controllers.
     * If true, a controller ID will appear in plural form in URLs. For example, `user` controller
     * will appear as `users` in URLs.
     * @see controller
     */
    public $pluralize = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->controller)) {
            throw new InvalidConfigException('"controller" must be set.');
        }

        $routePrefix = empty($this->routePrefix) ? '' : trim($this->routePrefix, '/') . '/';
        $controllers = [];
        foreach ((array) $this->controller as $urlName => $controller) {
            if (is_integer($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $controllers[$urlName] = $routePrefix . $controller;
        }
        $this->controller = $controllers;

        $this->prefix = trim($this->prefix, '/');

        $this->rules = $this->createRules();
    }
}