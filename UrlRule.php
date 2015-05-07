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
class UrlRule extends CompositeUrlRule
{
    /**
     * @var string the common prefix string shared by all patterns.
     */
    public $prefix;
    /**
     * @var string the common prefix string shared by all patterns.
     */
    public $prefixRoute;
    /**
     * @var string the suffix that will be assigned to [[\yii\web\UrlRule::suffix]] for every generated rule.
     */
    public $suffix;
    /**
     * @var string|array the action ID (e.g. `user/resource`, `post-comment/resource`) that the rules in this composite rule
     * are dealing with. It should be prefixed with the module ID if the controller is within a module (e.g. `admin/user`).
     *
     * By default, the controller ID will be pluralized automatically when it is put in the patterns of the
     * generated rules. If you want to explicitly specify how the controller ID should appear in the patterns,
     * you may use an array with the array key being as the controller ID in the pattern, and the array value
     * the actual controller ID. For example, `['u' => 'user']`.
     *
     * You may also pass multiple controller IDs as an array. If this is the case, this composite rule will
     * generate applicable URL rules for EVERY specified controller. For example, `['user', 'post']`.
     */
    public $actions;
    /**
     * @var array patterns for supporting extra actions in addition to those listed in [[patterns]].
     * The keys are the patterns and the values are the corresponding action IDs.
     * These extra patterns will take precedence over [[patterns]].
     */
    public $extraPatterns = [];
    /**
     * @var array list of tokens that should be replaced for each pattern. The keys are the token names,
     * and the values are the corresponding replacements.
     * @see patterns
     */
    public $tokens = [
        '{id}' => '<id:\\d[\\d,]*>',
    ];
    /**
     * @var array list of possible patterns and the corresponding actions for creating the URL rules.
     * The keys are the patterns and the values are the corresponding actions.
     * The format of patterns is `Verbs Pattern`, where `Verbs` stands for a list of HTTP verbs separated
     * by comma (without space). If `Verbs` is not specified, it means all verbs are allowed.
     * `Pattern` is optional. It will be prefixed with [[prefix]]/[[controller]]/,
     * and tokens in it will be replaced by [[tokens]].
     */
    public $patterns = [
        '{id}',
        '',
    ];
    /**
     * @var array the default configuration for creating each URL rule contained by this rule.
     */
    public $ruleConfig = [
        'class' => 'yii\web\UrlRule',
    ];
    /**
     * @var boolean whether to automatically pluralize the URL names for controllers.
     * If true, a controller ID will appear in plural form in URLs. For example, `user` controller
     * will appear as `users` in URLs.
     * @see controller
     */
    public $pluralize = false;

    /**
     * @var array
     */
    public static $routeParams = [];
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->actions)) {
            throw new InvalidConfigException('"actions" must be set.');
        }

        $actions = [];
        $this->prefixRoute = trim($this->prefixRoute, '/');
        foreach ((array) $this->actions as $urlName => $action) {
            if (is_integer($urlName)) {
                if(($pos=  strrpos($action, '/'))>0){
                    $controller = substr($action, 0, $pos);
                }  else {
                    $controller = $action;
                }
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $actions[$urlName] = empty($this->prefixRoute) ? $action : $this->prefixRoute . '/' . $action;;
        }
        $this->actions = $actions;

        $this->prefix = trim($this->prefix, '/');

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $patterns = array_merge($this->patterns, $this->extraPatterns);
        $rules = [];
        foreach ($this->actions as $urlName => $action) {
            $prefix = trim($this->prefix . '/' . $urlName, '/');
            foreach ($patterns as $pattern) {
                $rules[$urlName][] = $this->createRule($pattern, $prefix, $action);
            }
        }

        return $rules;
    }

    /**
     * Creates a URL rule using the given pattern and action.
     * @param string $pattern
     * @param string $prefix
     * @param string $action
     * @return \yii\web\UrlRuleInterface
     */
    protected function createRule($pattern, $prefix, $action)
    {
        $config = $this->ruleConfig;
        $config['pattern'] = rtrim($prefix . '/' . strtr($pattern, $this->tokens), '/');
        $config['route'] = $action;
        $config['suffix'] = $this->suffix;

        return Yii::createObject($config);
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        foreach ($this->rules as $urlName => $rules) {
            if (strpos($pathInfo, $urlName) !== false) {
                foreach ($rules as $rule) {
                    /* @var $rule \yii\web\UrlRule */
                    if (($result = $rule->parseRequest($manager, $request)) !== false) {
                        Yii::trace("Request parsed with URL rule: {$rule->name}", __METHOD__);

                        static::$routeParams[$result[0]] = $result[1];
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->actions as $urlName => $action) {
            if ($route == $action) {
                foreach ($this->rules[$urlName] as $rule) {
                    /* @var $rule \yii\web\UrlRule */
                    if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                        return $url;
                    }
                }
            }
        }

        return false;
    }

}