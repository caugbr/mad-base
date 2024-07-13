<?php
namespace Mad\Core;

/**
 * @version    1.0
 * @package    core
 * @author     Matheus Agnes Dias
 */
class BuilderApplication
{
    private static $verifyActionPermission;
    private static $hideAction = true;

    public static function setVerifyActionPermission(Callable $callback, $type = 'hide')
    {
        self::$verifyActionPermission = $callback;
        self::$hideAction = $type == 'hide' ? true : false;
    }

    public static function getVerifyActionPermission()
    {
        return self::$verifyActionPermission;
    }

    public static function isHideAction()
    {
        return self::$hideAction;
    }
}
