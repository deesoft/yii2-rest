<?php

namespace dee\rest;

use Yii;

/**
 * ResourceAction
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ResourceAction extends \yii\base\Action
{
    /**
     * @var array
     */
    public $patterns = [
        'POST,PUT {id}' => 'update',
        'PATCH {id}' => 'patch',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'query',
        'OPTIONS' => 'options',
    ];

    /**
     * @var array
     */
    public $extraPatterns = [];

    /**
     * @var array
     */
    private $_rules = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $patterns = array_merge($this->patterns, $this->extraPatterns);
        foreach ($patterns as $pattern => $action) {
            $rule = $this->createRule($pattern, $action);
            $this->_rules[10 * count($rule['params']) + count($rule['verbs'])] = $rule;
        }
        krsort($this->_rules);
    }

    /**
     *
     * @param string $pattern
     * @param string $action
     * @return array
     */
    public function createRule($pattern, $action)
    {
        $rule = ['action' => $action, 'params' => []];
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
            $rule['verbs'] = explode(',', $matches[1]);
            if (isset($matches[4]) && preg_match_all('/\\{(.*?)\\}/', $matches[4], $params) && isset($params[1])) {
                foreach ($params[1] as $param) {
                    if (($pos = strpos($param, '=')) === false) {
                        $rule['params'][] = $param;
                    } else {
                        $rule['params'][substr($param, 0, $pos)] = substr($param, $pos + 1);
                    }
                }
            }
        } else {
            $rule['verbs'] = [];
        }
        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $controller = $this->controller;
        $method = Yii::$app->request->getMethod();
        $params = Yii::$app->request->getQueryParams();
        foreach ($this->_rules as $rule) {
            if (empty($rule['verbs']) || in_array($method, $rule['verbs'])) {
                $match = true;
                $args = [];
                foreach ($rule['params'] as $param => $value) {
                    if (is_int($param) && isset($params[$value])) {
                        $args[] = $params[$value];
                    } elseif (isset($params[$param]) && $params[$param] === $value) {
                        $args[] = $value;
                    } else {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    return call_user_func_array([$controller, $rule['action']], $args);
                }
            }
        }
    }
}