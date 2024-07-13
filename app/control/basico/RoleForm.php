<?php

class RoleForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'base';
    private static $activeRecord = 'Role';
    private static $primaryKey = 'id';
    private static $formName = 'form_RoleForm';

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        if(!empty($param['target_container']))
        {
            $this->adianti_target_container = $param['target_container'];
        }

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Nível de usuário");


        $id = new THidden('id');
        $name = new TEntry('name');
        $capabilities = new TEntry('capabilities');
        $denied = new TEntry('denied');
        $group_names = new TEntry('group_names');
        $frontpage_id = new TEntry('frontpage_id');

        $name->addValidation("Nome", new TRequiredValidator()); 
        $capabilities->addValidation("Permitir", new TRequiredValidator()); 
        $group_names->addValidation("Grupos", new TRequiredValidator()); 

        $name->setMaxLength(100);
        $id->setSize(200);
        $name->setSize('100%');
        $denied->setSize('100%');
        $group_names->setSize('100%');
        $capabilities->setSize('100%');
        $frontpage_id->setSize('100%');

        $capabilities = new TDBCheckGroup('capabilities', self::$database, 'SystemProgram', 'controller', '{name}', 'name asc' , new TCriteria());
        $denied = new TDBCheckGroup('denied', self::$database, 'SystemProgram', 'controller', '{name}', 'name asc' , new TCriteria());
        $group_names = new TDBCheckGroup('group_names', self::$database, 'SystemGroup', 'name', '{name}','name asc' , new TCriteria());
        $frontpage_id = new TDBCombo('frontpage_id', self::$database, 'SystemProgram', 'id', '{name}','name asc' , new TCriteria());

        $capabilities->style = 'width: 100%';
        $denied->setSize('100%');
        $group_names->setSize('100%');
        $frontpage_id->setSize('100%');

        $items = $capabilities->getItems();
        $items = ['all' => 'Tudo'] + $items;
        $capabilities->addItems($items);
        $capabilities->setValue('all');

        $items = $group_names->getItems();
        $items = ['all' => 'Todos'] + $items;
        $group_names->addItems($items);

        $row1 = $this->form->addFields([$id,new TLabel("Nome:", '#ff0000', '14px', null, '100%'),$name]);
        $row1->layout = ['col-sm-12'];

        $row2 = $this->form->addFields([new TLabel("Permitir:", '#ff0000', '14px', null, '100%'),$capabilities]);
        $row2->layout = ['col-sm-12'];

        $row3 = $this->form->addFields([new TLabel("Bloquear:", null, '14px', null, '100%'),$denied]);
        $row3->layout = ['col-sm-12'];

        $row4 = $this->form->addFields([new TLabel("Grupos:", '#ff0000', '14px', null, '100%'),$group_names]);
        $row4->layout = ['col-sm-12'];

        $row5 = $this->form->addFields([new TLabel("Página inicial:", null, '14px', null, '100%'),$frontpage_id]);
        $row5->layout = ['col-sm-12'];

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $this->btn_onsave = $btn_onsave;
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');
        $this->btn_onclear = $btn_onclear;

        $btn_onshow = $this->form->addAction("Voltar", new TAction(['RoleHeaderList', 'onShow']), 'fas:arrow-left #000000');
        $this->btn_onshow = $btn_onshow;

        parent::setTargetContainer('adianti_right_panel');

        $btnClose = new TButton('closeCurtain');
        $btnClose->class = 'btn btn-sm btn-default';
        $btnClose->style = 'margin-right:10px;';
        $btnClose->onClick = "Template.closeRightPanel();";
        $btnClose->setLabel("Fechar");
        $btnClose->setImage('fas:times');

        $this->form->addHeaderWidget($btnClose);

        parent::add($this->form);

    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction

            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Role(); // create an empty object 

            $data = $this->form->getData(); // get form data as array

            $data->capabilities = $data->capabilities[0] == 'all' ? 'all' : join(",", $data->capabilities);
            $data->denied = join(",", $data->denied);
            if ($data->group_names[0] == 'all') {
                array_shift($data->group_names);
            }
            $data->group_names = join(",", $data->group_names);

            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $loadPageParam = [];

            if(!empty($param['target_container']))
            {
                $loadPageParam['target_container'] = $param['target_container'];
            }

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            TToast::show('success', "Registro salvo", 'topRight', 'far:check-circle');
            TApplication::loadPage('RoleHeaderList', 'onShow', $loadPageParam); 

                        TScript::create("Template.closeRightPanel();"); 

        }
        catch (Exception $e) // in case of exception
        {
            //</catchAutoCode> 

            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Role($key); // instantiates the Active Record 

                $groups = Role::getGroupsArray($object->id);
                $garr = [];
                foreach ($groups as $grp) {
                    $garr[] = $grp['name'];
                }
                $object->group_names = $garr;

                $caps = explode(",", trim($object->capabilities));
                if (count($caps) == 1 && $caps[0] == 'all') {
                    $object->capabilities = $caps;
                } else {
                    $object->capabilities = SystemProgram::where('controller', 'IN', $caps)->getIndexedArray('id', 'controller');
                }
                $deny = explode(",", trim($object->denied));
                $object->denied = SystemProgram::where('controller', 'IN', $deny)->getIndexedArray('id', 'controller');

                $this->form->setData($object); // fill the form 

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(true);

    }

    public function onShow($param = null)
    {

    } 

    public static function getFormName()
    {
        return self::$formName;
    }

}

