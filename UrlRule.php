<?php

namespace dee\rest;

use Yii;

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
    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = $this->extraPatterns + $this->patterns;
        $prefixRoute = trim($this->prefixRoute, '/') . '/';
        $rules = [];
        foreach ($this->controller as $urlName => $controller) {
            $controller = $prefixRoute . trim($controller, '/');
            $prefix = trim($this->prefix . '/' . $urlName, '/');
            foreach ($patterns as $pattern => $action) {
                if (!isset($except[$action]) && (empty($only) || isset($only[$action]))) {
                    $rules[] = $this->createRule($pattern, $prefix, $controller . '/' . $action);
                }
            }
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            if (($result = $rule->parseRequest($manager, $request)) !== false) {
                Yii::trace("Request parsed with URL rule: {$rule->name}", __METHOD__);

                return $result;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                return $url;
            }
        }

        return false;
    }
}