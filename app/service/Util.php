<?php

class Util {

    public static $database = 'base';

    public static function open($db = '') {
        self::close();
        if (empty($db)) {
            $db = self::$database;
        }
        TTransaction::open($db);
    }

    public static function close() {
        try {
            $dbname = self::getDbName();
            if (!empty($dbname)) {
                TTransaction::close(); 
            }
        } catch(Exception $e) { 
            echo "Erro ao fechar a transação: " . $e->getMessage() . "\n";
        }
    }

    public static function getDbName() {
        if ($transaction = TTransaction::get()) {
            $info = TTransaction::getDatabaseInfo();
            $parts = explode("/", $info['name']);
            $name = str_replace(".db", "", array_pop($parts));
            return $name;
        }
        return '';
    }

    public static function toVarName($string) {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace('/&([a-zA-Z])(acute|cedil|circ|grave|ring|tilde|uml|caron);/', '$1', $string);
        $string = preg_replace('/&([a-zA-Z]{2})(lig);/', '$1', $string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', '_', $string);
        $string = preg_replace('/_+/', '_', $string);
        return trim($string, '_');
    }

    public static function readDir($path, $pattern = '') {
        $path = rtrim($path, "/");
        $files = array_diff(scandir($path), ['..', '.']);
        $arr = [];
        foreach ($files as $file) {
            if (is_file("{$path}/{$file}")) {
                if (empty($pattern) || strstr($file, $pattern)) {
                    $arr[] = "{$path}/{$file}";
                }
            } else {
                $arr = array_merge($arr, self::readDir("{$path}/{$file}", $pattern));
            }
        }
        return $arr;
    }

    // retorna todos os nomes de arquivos em model, control e service (opcionalmente), sem o '.php'
    public static function allClasses($from = ['model', 'control', 'service']) {
        $models = in_array('model', $from) ? self::readDir("app/model", ".php") : [];
        $controls = in_array('control', $from) ? self::readDir("app/control", ".php") : [];
        $services = in_array('service', $from) ? self::readDir("app/service", ".php") : [];
        $files = [ ...$models, ...$controls, ...$services ];
        $classes = [];
        foreach ($files as $file) {
            $parts = explode("/", trim($file, "/"));
            $classes[] = explode(".", array_pop($parts))[0];
        }
        return $classes;
    }

}
