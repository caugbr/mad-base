<?php
require_once 'init.php';

// inicializa os hooks
ActionsService::start();

$theme  = $ini['general']['theme'];
$class  = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
$public = in_array($class, $ini['permission']['public_classes']);

// AdiantiCoreApplication::setRouter(array('AdiantiRouteTranslator', 'translate'));

new TSession;
ApplicationTranslator::setLanguage( TSession::getValue('user_language'), true );
BuilderTranslator::setLanguage( TSession::getValue('user_language'), true );

$content = BuilderTemplateParser::init('layout');
$content = ApplicationTranslator::translateTemplate($content);

echo $content;

if (TSession::getValue('logged') OR $public)
{
    if ($class)
    {
        Actions::call('init');
        $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : NULL;
        AdiantiCoreApplication::loadPage($class, $method, $_REQUEST);
    }
}
else
{
    if (isset($ini['general']['public_view']) && $ini['general']['public_view'] == '1')
    {
        if (!empty($ini['general']['public_entry']))
        {
            AdiantiCoreApplication::loadPage($ini['general']['public_entry'], '', $_REQUEST);
        }
    }
    else
    {
        AdiantiCoreApplication::loadPage('LoginForm', '', $_REQUEST);
    }
}

// set theme as a body attribute
$names = ['theme-builder' => 'builder', 'theme3' => 'lte2', 'theme3-adminlte3' => 'lte3', 'theme4' => 'bsb' ];
TScript::create("document.body.setAttribute('data-theme', '{$names[$theme]}');");

Actions::call('afterInit');