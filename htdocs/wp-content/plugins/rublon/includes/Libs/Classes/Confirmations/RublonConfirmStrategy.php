<?php

namespace Rublon_WordPress\Libs\Classes\Confirmations;

use Rublon\Core\Api\RublonAPICredentials;
use Rublon\RublonCallback;

abstract class RublonConfirmStrategy
{
    const STRATEGY_CLASS_PREFIX = 'RublonConfirmStrategy_';

    protected $action = '';
    protected $adminUI = true;
    protected $label = '';
    protected $confirmMessage = '';

    protected $pageNowInit = '';
    protected $pageNowAction = '';

    protected static $instance = array();
    protected static $confirmMessageValues = array();

    protected $context;
    protected $oldValue;
    protected $newValue;

    static protected $instances = array();

    function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    function getContext()
    {
        return $this->context;
    }

    function setOldValue($value)
    {
        $this->oldValue = $value;
        return $this;
    }

    function getOldValue()
    {
        return $this->oldValue;
    }

    function setNewValue($value)
    {
        $this->newValue = $value;
        return $this;
    }

    function getNewValue()
    {
        return $this->newValue;
    }

    function pluginsLoaded()
    {
        //
    }

    function hasAdminUI()
    {
        return $this->adminUI;
    }

    function getAction()
    {
        return $this->action;
    }

    function getLabel()
    {
        return $this->label;
    }

    function getConfirmMessage()
    {
        return __($this->confirmMessage, 'rublon');
    }

    function callbackSuccess(RublonCallback $callback)
    {
        if ($dataKey = $callback->getConsumerParam(RublonConfirmations::PARAM_DATA_KEY)) {
            if (RublonAPICredentials::CONFIRM_RESULT_YES == $callback->getCredentials()->getConfirmResult()) {
                $this->callbackSuccessYes($callback);
            } else {
                $this->callbackSuccessNo($callback);
            }
        }
    }

    function callbackSuccessYes(RublonCallback $callback)
    {
        $dataKey = $callback->getConsumerParam(RublonConfirmations::PARAM_DATA_KEY);
        $url = $callback->getConsumerParam(RublonConfirmations::PARAM_ACTION_URL);
        RublonConfirmations::redirectParentFrame($url, $this->getAction(), __('Operation has been confirmed.', 'rublon'), $dataKey);
    }

    function callbackSuccessNo(RublonCallback $callback)
    {
        $url = $callback->getConsumerParam(RublonConfirmations::PARAM_FALLBACK_URL);
        RublonConfirmations::redirectParentFrame($url, $this->getAction(), __('Operation has been canceled.', 'rublon'));
    }

    function isConfirmationRequired()
    {
        return false;
    }

    function isConfirmationNeeded()
    {
        return false;
    }

    function checkChanges()
    {
        return 1;
    }


    function getFallbackUrl()
    {
        return null;
    }

    /**
     *
     * @param string $action
     * @return RublonConfirmStrategy
     */
    static function getStrategyInstance($action)
    {
        if (!isset(self::$instances[$action])) {
            if ($className = self::getStrategyClassName($action)) {
                self::$instances[$action] = new $className;
            }
        }
        if (!empty(self::$instances[$action])) {
            return self::$instances[$action];
        }
    }

    static function getStrategyClassName($action)
    {
        $className = self::STRATEGY_CLASS_PREFIX . $action;
        if (!class_exists($className)) {
            require_once dirname(__FILE__) . '/Strategy/' . $action . '.php';
        }
        if (class_exists($className)) {
            return $className;
        }
    }

    function isThePage()
    {
        global $pagenow;
        return (is_admin() AND !empty($pagenow) AND $pagenow == $this->pageNowInit);
    }

    function isTheAction()
    {
        global $pagenow;
        return (is_admin() AND !empty($pagenow) AND $pagenow == $this->pageNowAction);
    }

    function appendScript($selector = NULL)
    {

    }

    function checkForAction()
    {

    }
}
