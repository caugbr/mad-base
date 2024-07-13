<?php

// Para adicionar seus hooks, a classe deve conter um método chamado 'addHooks'.
// Buscaremos pelo método 'addHooks' em todas as classes do sistema, executando todos.
class ActionsService
{
    // inicializa e agenda a adição dos hooks para 'afterInit'
    public static function start()
    {
        $isset = Options::getAdmValue('actions_set');
        if (!$isset) {
            Actions::add('afterInit', ['ActionsService', 'add']);
            Options::setOrAddAdmValue('actions_set', 1);
        }
    }

    // limpa e adiciona os hooks
    public static function add()
    {
        Actions::clearHooks();
        sleep(1);
        self::addAllHooks();
    }

    // busca pelo método 'addHooks' em todas as classes
    public static function addAllHooks()
    {
        $classes = Util::allClasses(['model']);
        foreach ($classes as $className) {
            if (class_exists($className) && method_exists($className, 'addHooks')) {
                call_user_func([$className, 'addHooks']);
            }
        }
    }
}
