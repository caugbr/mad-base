<?php

class OptionsForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'base';
    private static $activeRecord = 'Options';
    private static $primaryKey = 'id';
    private static $formName = 'form_OptionsForm';

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
        $this->form->setFormTitle("Cadastro de option");


        $id = new THidden('id');
        $name = new TEntry('name');
        $readable_name = new TEntry('readable_name');
        $value = new TEntry('value');
        $field_type = new TCombo('field_type');
        $edit_options = new TText('edit_options');

        $name->addValidation("Nome", new TRequiredValidator()); 
        $field_type->addValidation("Tipo de campo", new TRequiredValidator()); 

        $field_type->addItems(["text"=>"Texto","combo"=>"Combo"]);
        $field_type->setValue('text');
        $field_type->setDefaultOption(false);
        $name->setMaxLength(100);
        $readable_name->setMaxLength(120);

        $id->setSize(200);
        $name->setSize('100%');
        $value->setSize('100%');
        $field_type->setSize('100%');
        $readable_name->setSize('100%');
        $edit_options->setSize('100%', 70);

        $row1 = $this->form->addFields([$id,new TLabel("Nome:", '#ff0000', '14px', null, '100%'),$name]);
        $row1->layout = ['col-sm-12'];

        $row2 = $this->form->addFields([new TLabel("Nome legível:", null, '14px', null, '100%'),$readable_name]);
        $row2->layout = ['col-sm-12'];

        $row3 = $this->form->addFields([new TLabel("Valor:", null, '14px', null, '100%'),$value]);
        $row3->layout = ['col-sm-12'];

        $row4 = $this->form->addFields([new TLabel("Tipo de campo:", '#ff0000', '14px', null, '100%'),$field_type]);
        $row4->layout = ['col-sm-12'];

        $row5 = $this->form->addFields([new TLabel("Opções de edição:", null, '14px', null, '100%'),$edit_options]);
        $row5->layout = ['col-sm-12'];

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fas:save #ffffff');
        $this->btn_onsave = $btn_onsave;
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fas:eraser #dd5a43');
        $this->btn_onclear = $btn_onclear;

        $btn_onshow = $this->form->addAction("Voltar", new TAction(['Config', 'onShow']), 'fas:arrow-left #000000');
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

            $object = new Options(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
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
            TApplication::loadPage('Config', 'onShow', $loadPageParam); 

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

                $object = new Options($key); // instantiates the Active Record 

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

