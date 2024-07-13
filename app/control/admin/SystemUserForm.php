<?php

class SystemUserForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'base';
    private static $activeRecord = 'SystemUsers';
    private static $primaryKey = 'id';
    private static $formName = 'form_SystemUsers';

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
        $this->form->setFormTitle("Usuário");

        $criteria_system_unit_id = new TCriteria();
        $criteria_frontpage_id = new TCriteria();
        $criteria_units = new TCriteria();
        $criteria_groups = new TCriteria();
        $criteria_system_programs = new TCriteria();

        $id = new THidden('id');
        $name = new TEntry('name');
        $login = new TEntry('login');
        $email = new TEntry('email');
        $system_unit_id = new TDBCombo('system_unit_id', 'base', 'SystemUnit', 'id', '{name}','name asc' , $criteria_system_unit_id );
        $frontpage_id = new TDBCombo('frontpage_id', 'base', 'SystemProgram', 'id', '{name}','name asc' , $criteria_frontpage_id );
        $password = new TPassword('password');
        $repassword = new TPassword('repassword');
        $units = new TDBCheckGroup('units', 'base', 'SystemUnit', 'id', '{name}','name asc' , $criteria_units );
        $groups = new TDBCheckGroup('groups', 'base', 'SystemGroup', 'id', '{name}','name asc' , $criteria_groups );
        $system_programs = new TCheckList('system_programs');

        $name->addValidation("Nome", new TRequiredValidator()); 
        $login->addValidation("Login", new TRequiredValidator()); 
        $password->addValidation("Password", new TRequiredValidator()); 

        $frontpage_id->enableSearch();
        $system_unit_id->enableSearch();

        $units->setLayout('horizontal');
        $groups->setLayout('horizontal');

        $id->setSize(200);
        $units->setSize(200);
        $groups->setSize(200);
        $name->setSize('100%');
        $login->setSize('100%');
        $email->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $frontpage_id->setSize('100%');
        $system_unit_id->setSize('100%');

        $system_programs->setIdColumn('id');

        $column_system_programs_id = $system_programs->addColumn('id', "ID", 'center' , '33%');
        $column_system_programs_name = $system_programs->addColumn('name', "Nome", 'center' , '33%');
        $column_system_programs_controller_transformed = $system_programs->addColumn('controller', "Caminho do menu", 'center' , '33%');

        $column_system_programs_controller_transformed->setTransformer(function($value, $object, $row)
        {
            $menuparser = new TMenuParser('menu.xml');
            $paths = $menuparser->getPath($value);

            if ($paths)
            {
                return implode(' &raquo; ', $paths);
            }

        });        

        $system_programs->setHeight(250);
        $system_programs->makeScrollable();

        $system_programs->fillWith('base', 'SystemProgram', 'id', 'name asc' , $criteria_system_programs);

        $row1 = $this->form->addFields([$id,new TLabel("Nome", '#ff0000', '14px', null),$name],[new TLabel("Login", '#ff0000', '14px', null),$login],[new TLabel("E-mail", null, '14px', null),$email]);
        $row1->layout = [' col-sm-4',' col-sm-4',' col-sm-4'];

        $row2 = $this->form->addFields([new TLabel("Unidade principal", null, '14px', null),$system_unit_id],[new TLabel("Tela inicial", null, '14px', null),$frontpage_id]);
        $row2->layout = [' col-sm-6',' col-sm-6'];

        $row3 = $this->form->addFields([new TLabel("Senha", null, '14px', null),$password],[new TLabel("Confirma senha", null, '14px', null),$repassword]);
        $row3->layout = [' col-sm-6',' col-sm-6'];

        $row4 = $this->form->addContent([new TFormSeparator("Unidades", '#333333', '18', '#eeeeee')]);
        $row5 = $this->form->addFields([$units]);
        $row5->layout = [' col-sm-12'];

        $row6 = $this->form->addContent([new TFormSeparator("Grupos", '#333333', '18', '#eeeeee')]);
        $row7 = $this->form->addFields([$groups]);
        $row7->layout = [' col-sm-12'];

        $row8 = $this->form->addContent([new TFormSeparator("Programas", '#333333', '18', '#eeeeee')]);
        $row9 = $this->form->addFields([$system_programs]);
        $row9->layout = [' col-sm-12'];

        $this->form = Actions::filter('userForm', $this->form);

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $this->btn_onsave = $btn_onsave;
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');
        $this->btn_onclear = $btn_onclear;

        $btn_onreload = $this->form->addAction("Voltar", new TAction(['SystemUserList', 'onReload']), 'far:arrow-alt-circle-left #478fca');
        $this->btn_onreload = $btn_onreload;

        parent::setTargetContainer('adianti_right_panel');

        $btnClose = new TButton('closeCurtain');
        $btnClose->class = 'btn btn-sm btn-default';
        $btnClose->style = 'margin-right:10px;';
        $btnClose->onClick = "Template.closeRightPanel();";
        $btnClose->setLabel("Fechar");
        $btnClose->setImage('fas:times');

        $this->form->addHeaderWidget($btnClose);

        parent::add($this->form);

        $style = new TStyle('right-panel > .container-part[page-name=SystemUserForm]');
        $style->width = '70% !important';   
        $style->show(true);

    }

    public function onSave($param = null) 
    {
        try 
        {
            // open a transaction with database 'permission'
            Util::open('permission');

            $object = new SystemUsers;
            $object->fromArray( $param );

            $data = $this->form->getData();

            // Hook 'beforeSaveUser'
            $data = Actions::filter('beforeSaveUser', $data, $param);

            Util::open();
            $this->form->setData($data);

            Util::open('permission');

            $senha = $object->password;

            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }

            if( empty($object->id) )
            {
                if (SystemUsers::newFromLogin($object->login) instanceof SystemUsers)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }

                if (SystemUsers::newFromEmail($object->email) instanceof SystemUsers)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }

                if ( empty($object->password) )
                {
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
                }

                $object->active = 'Y';
            }

            if( $object->password )
            {
                if( $object->password !== $param['repassword'] )
                    throw new Exception(_t('The passwords do not match'));

                $object->password = md5($object->password);
            }
            else
            {
                unset($object->password);
            }

            $object->store();
            $object->clearParts();

            if( !empty($param['groups']) )
            {
                foreach( $param['groups'] as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }

            if( !empty($param['units']) )
            {
                foreach( $param['units'] as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }

            if (!empty($data->system_programs))
            {
                foreach ($data->system_programs as $program)
                {
                    $object->addSystemUserProgram( new SystemProgram( $program ) );
                }
            }

            // Hook 'saveUser'
            $object = Actions::filter('saveUser', $object, $param);

            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_user', $data);

            // close the transaction
            Util::close();

            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));

        }
        catch (Exception $e) 
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();    
        }
    }

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                Util::open(); // open a transaction

                $object = new SystemUsers($key); // instantiates the Active Record 
                unset($object->password);

                $object->units = SystemUserUnit::where('system_user_id', '=', $object->id)->getIndexedArray('system_unit_id', 'system_unit_id');

                $object->groups = SystemUserGroup::where('system_user_id', '=', $object->id)->getIndexedArray('system_group_id', 'system_group_id');

                $object->system_programs = SystemUserProgram::where('system_user_id', '=', $object->id)->getIndexedArray('system_program_id', 'system_program_id');

                $object = Actions::filter('editUser', $object);

                $this->form->setData($object); // fill the form 

                Util::close(); // close the transaction 
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

