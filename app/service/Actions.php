<?php

class Actions
{
    
    public static function add($hook, $callback) {
        if (self::check($hook, $callback)) {
            $index = self::getIndex($hook, $callback);
            if ($index < 0) {
                $acts = self::getHook($hook);
                $acts[] = $callback;
                self::setHook($hook, $acts);
                return count($acts) - 1;
            } else {
                return $index;
            }
        }
        return -1;
    }

    public static function remove($hook, $index = -1) {
        if ($index >= 0) {
            $acts = self::getHook($hook);
            if (isset($acts[$index])) {
                unset($acts[$index]);
                self::setHook($hook, $acts);
                return true;
            }
        }
        return false;
    }

    public static function call($hook, ...$params) {
        $acts = self::getHook($hook);
        if (count($acts)) {
            foreach ($acts as $fnc) {
                call_user_func_array($fnc, $params);
            }
        }
    }

    public static function filter($hook, ...$params) {
        $acts = self::getHook($hook);
        $ret = $params[0] ?? NULL;
        if (count($acts)) {
            foreach ($acts as $fnc) {
                $ret = call_user_func_array($fnc, $params);
                $params[0] = $ret;
            }
        }
        return $ret;
    }

    public static function getHooks($ret = 'names') {
        $hooks = Options::findOptions('admin_hook_');
        if ($ret == 'names') {
            return array_map(function($e) { return $e->name; }, $hooks);
        }
        $arr = [];
        foreach ($hooks as $hook) {
            $arr[$hook->name] = $hook->value;
        }
        return $arr;
    }

    private static function check($hook, $callback) {
        $hookOk = (is_string($hook) && !empty($hook));
        $cbOk = (is_array($callback) && count($callback) == 2 && is_string($callback[0]) && is_callable($callback));
        return ($hookOk && $cbOk);
    }

    private static function getIndex($hook, $callback) {
        $acts = self::getHook($hook);
        foreach ($acts as $i => $act) {
            if (join(":", $act) === join(":", $callback)) {
                return $i;
            }
        }
        return -1;
    }

    private static function getHook($hook) {
        $arr = [];
        $opt = Options::getAdmValue("hook_{$hook}");
        if ($opt) {
            $arr = json_decode($opt);
        }
        return $arr;
    }

    private static function setHook($hook, $arr) {
        return Options::setOrAddAdmValue("hook_{$hook}", json_encode($arr));
    }

    public static function clearHooks($name = '') {
        Options::removeOptions("admin_hook_{$name}");
    }
    
}

?>