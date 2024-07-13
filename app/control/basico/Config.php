<?php

class Config extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = '';
    private static $activeRecord = '';
    private static $primaryKey = '';
    private static $formName = 'form_Config';

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param = null)
    {
        parent::__construct();

        if(!empty($param['target_container']))
        {
            $this->adianti_target_container = $param['target_container'];
        }

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Configurações");

        self::$database = 'base';

        $this->form->addFields([TElement::tag('h5', 'Parâmetros', ["style" => "margin: 10px 0 0; font-size: 16px; font-weight: 600;"])], []);

        $button_cadastrar = new TButton('button_button_cadastrar');
        $button_cadastrar->setAction(new TAction(['OptionsForm', 'onShow']), "Novo parâmetro");
        $button_cadastrar->addStyleClass('btn-default');
        $button_cadastrar->setImage('fas:plus #69aa46');

        $tabs = new TElement('div');
        $tabs->setProperty('class', 'items-tabs');
        $tabs->setProperty('style', 'margin-left: 3rem;');

        $normal_items = new TCheckButton('normal-items');
        $normal_items->setUseSwitch(true);
        $normal_items_label = TElement::tag('label', 'Parâmetros');
        $normal_items_label->setProperty('for', $normal_items->getId());
        $normal_items_label->setProperty('style', 'vertical-align: top; margin: auto 1rem auto 0.25rem');

        $admin_items = new TCheckButton('admin-items');
        $admin_items->setUseSwitch(true);
        $admin_items_label = TElement::tag('label', 'Administrativos');
        $admin_items_label->setProperty('for', $admin_items->getId());
        $admin_items_label->setProperty('style', 'vertical-align: top; margin: auto 1rem auto 0.25rem');

        $tabs->add($normal_items);
        $tabs->add($normal_items_label);
        $tabs->add($admin_items);
        $tabs->add($admin_items_label);

        $this->form->addFields([], [$button_cadastrar, $tabs]);

        TScript::create("setTimeout(() => \$single('#{$normal_items->getId()}').click(), 500);");

        TTransaction::open(self::$database);
        $opts = Options::all();
        TTransaction::close();
        $this->formFields = [];
        if (count($opts)) {
            foreach ($opts as $op) {
                $is_admin = false;
                $cls = '';
                if (substr($op->name, 0, 6) == 'admin_') {
                    $is_admin = true;
                    $op->readable_name = str_replace('admin_', '', $op->readable_name);
                }
                if (in_array($op->name, $this->formFields)) {
                    print "DUPLICADO: {$op->name}\n";
                    print_r($this->formFields);
                }
                $this->formFields[] = $op->name;

                $input = Options::editField($op);
                $input->setValue($op->value);
                $input->setSize('65%');

                $button_reg_edit = new TButton('button_reg_edit_' . $op->name);
                $act = new TAction(['OptionsForm', 'onEdit']);
                $act->setParameter('key', $op->id);
                $button_reg_edit->setAction($act, "");
                $button_reg_edit->addStyleClass('btn btn-default btn-sm');
                $button_reg_edit->setImage('far:edit #478fca');

                $button_delete = new TButton('button_remove_' . $op->name);
                $button_delete->setImage('fa:trash-alt #dd5a43');
                $button_delete->addStyleClass('btn-default delete-option');
                $button_delete->{"data-id"} = $op->id;

                if ($is_admin) {
                    $input->{"data-admin"} = 1;
                    $button_reg_edit->{"data-admin"} = 1;
                    $button_delete->{"data-admin"} = 1;

                    $button_edit = new TButton('button_edit_' . $op->name);
                    $button_edit->setImage('fa:edit #69aa46');
                    $button_edit->addStyleClass('btn-default edit-admin-var');
                    $button_edit->{"data-name"} = $op->name;
                    $button_edit->{"title"} = 'Clique para editar';
                    $row = $this->form->addFields([new TLabel($op->readable_name, '#333333', '14px', null), ""], [$input, $button_reg_edit, $button_delete, $button_edit]);
                    $row->class = 'admin-item';
                } else {
                    $row = $this->form->addFields([new TLabel($op->readable_name, '#333333', '14px', null), ""], [$input, $button_reg_edit, $button_delete]);
                    $row->class = 'normal-item';
                }
            }
        } else {
            $warning = TElement::tag('p', "Não há opções na database");
            $warning->style = "color: orange; font-size: 18px; text-align: center; padding: 3em 0";
            $this->form->addFields([$warning]);
        }


        $btn_onaction = $this->form->addAction("Salvar", new TAction([$this, 'onAction']), 'fas:rocket #ffffff');
        $this->btn_onaction = $btn_onaction;
        $btn_onaction->addStyleClass('btn-primary'); 

        // create the form actions

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        if(empty($param['target_container']))
        {
            $container->add(TBreadCrumb::create(["Básico","Configurações"]));
        }
        $container->add($this->form);

        parent::add($container);

    }

    public function onShow($param = null)
    {               

    } 

    public function onAction($param = null) 
    {
        $this->form->validate();
        $dt = (array) $this->form->getData();
        foreach ($dt as $k => $v) {
            if (in_array($k, $this->formFields)) {
                Options::setValue($k, $v);
            }
        }

        $message = 'As configurações foram salvas';
        TToast::show('success', $message, 'topRight', 'far:check-circle');
    }

    public static function deleteOption($param)
    {
        TTransaction::open(self::$database);
        $objeto = Options::find($param['option_id']);
        if($objeto)
        {
            $objeto->delete();    
        }
        TTransaction::close();

    }

}

