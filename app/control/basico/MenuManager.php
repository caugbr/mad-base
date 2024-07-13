<?php

class MenuManager extends TPage
{
    protected $wrapper;
    private $menuArr = [];
    private static $formName = 'form_EditMenu';
    private static $formTitle = 'Organizar menu';
    private static $menuPath = 'menu.xml';
    private static $original = 'menu-original.xml';
    // private static $saveAs = 'menu2.xml';
    
    public function __construct($param)
    {
        parent::__construct();

        if(!empty($param['target_container'])) {
            $this->adianti_target_container = $param['target_container'];
        }
        
        $this->saveOriginal();
        
        $this->wrapper = new TElement('div');
        $this->wrapper->id = self::$formName;
        $this->wrapper->add(TElement::tag('h3', self::$formTitle));
        
        $this->menuArr = self::arrayFromXml(self::getXml());
        
        $ul = new TElement('ul');
        $ul->class = "module-menu";

        foreach ($this->menuArr as $mpos => $module) {
            $li = new TElement('li');
            $li->class = "menu-item";
            $li->{"data-module"} = $mpos;
            if (!empty($module['action'])) {
                $li->{"data-action"} = $module['action'];
            }

            $mod_title = TElement::tag('div', $module['label']);
            $mod_title->class = "module-label";
            $mod_title->style = "display: inline-block;";

            $mod_icon = self::editIconLink($module['icon'], $mpos);
                    
            $edit_mod = TElement::tag('a', '<span class="fa fa-edit" style="color: lightblue;"></span>');
            $edit_mod->href = '#';
            $edit_mod->class = 'edit-item';
                    
            $del_mod = TElement::tag('a', '<span class="fa fa-minus" style="color: red;"></span>');
            $del_mod->href = '#';
            $del_mod->class = 'delete-item';

            $li->add($mod_icon);
            $li->add($mod_title);
            $li->add($edit_mod);
            $li->add($del_mod);

            if (!empty($module['menu'])) {
                $sul = new TElement('ul');
                $sul->class = "module-submenu";
                foreach ($module['menu'] as $ipos => $item) {
                    $itm_title = TElement::tag('div', $item['label']);
                    $itm_title->class = "item-label";
                    $itm_title->style = "display: inline-block;";

                    $itm_icon = self::editIconLink($item['icon'], $mpos, $ipos);
                    
                    $edit_item = TElement::tag('a', '<span class="fa fa-edit" style="color: lightblue;"></span>');
                    $edit_item->href = '#';
                    $edit_item->class = 'edit-item';
                    
                    $del_item = TElement::tag('a', '<span class="fa fa-minus" style="color: red;"></span>');
                    $del_item->href = '#';
                    $del_item->class = 'delete-item';

                    $sli = new TElement('li');
                    $sli->class = "menu-item";
                    $sli->{"data-module"} = $mpos;
                    $sli->{"data-item"} = $ipos;
                    $sli->{"data-action"} = $item['action'];
                    $sli->add($itm_icon);
                    $sli->add($itm_title);
                    $sli->add($edit_item);
                    $sli->add($del_item);
                    $sul->add($sli);
                }
                $saddMod = TElement::tag('a', '<span class="fa fa-plus" style="color: red;"></span> Novo item');
                $saddMod->href = '#';
                $saddLi = TElement::tag('li', $saddMod);
                $saddLi->class = "add-item";
                $sul->add($saddLi);
                $li->add($sul);
            }
            $ul->add($li);
        }
        
        $addMod = TElement::tag('a', '<span class="fa fa-plus" style="color: red;"></span> Novo item');
        $addMod->href = "#";
        $addLi = TElement::tag('li', $addMod);
        $addLi->class = "add-item";
        $ul->add($addLi);
        
        $actionButton = new TButton('save_button');
        $actionButton->setLabel('Salvar menu');
        $actionButton->id = 'save_button';
        $actionButton->class = 'btn btn-sm btn-primary';
        
        $buttons = [$actionButton];
        
        if ($this->isDiff()) {
            $resetButton = new TButton('reset_button');
            $resetButton->setLabel('Restaurar menu');
            $resetButton->id = 'reset_button';
            $resetButton->class = 'btn btn-sm btn-default';
            $buttons[] = $resetButton;
        }

        $footer = TElement::tag('div', $buttons);
        $footer->class = 'menu-footer-actions';
        
        $this->wrapper->add($ul);
        $this->wrapper->add($footer);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        if(empty($param['target_container']))
        {
            $container->add(TBreadCrumb::create(["Básico","Organizar menu"]));
        }
        $container->add($this->wrapper);

        parent::add($container);
        
        TScript::create("$('.module-menu').sortable({ connectWith: '.module-menu', items: 'li:not(.add-item)' });");
        TScript::create("$('.module-submenu').sortable({ connectWith: '.module-submenu', items: 'li:not(.add-item)' });");
        
        // editar icones
        $popup = new TElement('div');
        $popup->class = "icon-popup";
        $popup->style = "display: none;";

        $row = new TElement('div');
        $row->class = "row";

        $col1 = new TElement('div');
        $col1->class = "col-sm-4";

        $col2 = new TElement('div');
        $col2->class = "col-sm-8";
        
        $label = new TLabel("Ícone atual:", null, '14px', null, '100%');
        $icon = new TIcon('icon');

        $col1->add($label);
        $col2->add($icon);
        $row->add($col1);
        $row->add($col2);
        $popup->add($row);

        parent::add($popup);
    }
    
    public static function saveOriginal()
    {
        if (!file_exists(self::$original)) {
            file_put_contents(self::$original, self::getXml());
        }
    }
    
    public static function getXml()
    {
        return file_get_contents(self::$menuPath);
    }
    
    public static function setXml($xml)
    {
        $path = self::$menuPath;
        file_put_contents($path, $xml);
        return file_exists($path);
    }
    
    public static function resetXml()
    {
        file_put_contents(self::$menuPath, file_get_contents(self::$original));
    }
    
    public static function isDiff()
    {
        return (file_get_contents(self::$menuPath) != file_get_contents(self::$original));
    }
    
    // função executa ao clicar no submit
    public static function saveMenu($param = null)
    {
        $arr = json_decode($param['menu'], true);
        $xml = self::xmlFromArray($arr);
        $ok = self::setXml($xml);
        return ["success" => $ok, "message" => $ok ? "O menu foi alterado com sucesso." : "Houve um erro!"];
    }

    // função executa ao clicar no submit
    public static function resetMenu()
    {
        self::resetXml();
        $ok = !self::isDiff();
        return ["success" => $ok, "message" => $ok ? "O menu foi restaurado com sucesso." : "Houve um erro!"];
    }
    
    public static function editIconLink($icon, $module, $item = -1) {
        $label = "fa-fw" == trim($icon) ? '[+]' : "<span class='" . self::iconClass($icon, true) . "'></span>";
        $ico = TElement::tag('a', $label);
        $ico->href = 'javascript://';
        $ico->class = 'edit-icon';
        $ico->{"data-module"} = $module;
        $ico->{"data-item"} = $item;
        return $ico;
    }
    
    public static function iconClass($str, $clear = false) {
        $cls = str_replace(":", " fa-", trim($str));
        if ($clear) {
            return str_replace(" fa-fw", "", $cls);
        }
        return $cls;
    }
    
    public static function arrayFromXml($xmlString) {
        $xml = simplexml_load_string($xmlString);
        return self::xml2arr($xml);
    }

    public static function xmlFromArray($array) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><menu></menu>');
        self::arr2xml($array, $xml);
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private static function xml2arr($xml) {
        $result = [];
        foreach ($xml->menuitem as $menuitem) {
            $item = [
                'label' => (string) $menuitem['label'],
                'icon' => (string) $menuitem->icon,
                'action' => (string) $menuitem->action
            ];
            if (isset($menuitem->menu)) {
                $item['menu'] = self::xml2arr($menuitem->menu);
            }
            $result[] = $item;
        }
        return $result;
    }

    private static function arr2xml($array, &$xml) {
        foreach ($array as $item) {
            $menuitem = $xml->addChild('menuitem');
            $menuitem->addAttribute('label', $item['label']);

            if (!empty($item['icon'])) {
                $menuitem->addChild('icon', $item['icon']);
            }

            if (!empty($item['action'])) {
                $menuitem->addChild('action', $item['action']);
            }

            if (isset($item['menu'])) {
                $submenu = $menuitem->addChild('menu');
                self::arr2xml($item['menu'], $submenu);
            }
        }
    }

}
