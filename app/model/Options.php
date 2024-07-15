<?php

class Options extends TRecord
{
    const TABLENAME  = 'options';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('readable_name');
        parent::addAttribute('value');
        parent::addAttribute('field_type');
        parent::addAttribute('edit_options');
    
    }

     /**
     * Method editField - retorna o campo HTML para editar o valor
     * @returns option value
     */
    public static function editField($opt)
    {
        $field = '';
        if ($opt->field_type == 'combo') {
            if ((preg_match("/^(\[|\{)/", $opt->edit_options)) && json_decode($opt->edit_options) !== false) {
                $field = new TCombo($opt->name);
                $field->addItems((array)json_decode($opt->edit_options));
            } else {
                list($obj, $prop) = explode('-', $opt->edit_options);
                // eval("\$field = new TDBCombo('{$opt->name}', self::$database, '{$obj}', 'id', '{{$prop}}','id asc' , new TCriteria());");
                eval("\$field = new TDBCombo('{$opt->name}', '{self::$database}', '{$obj}', 'id', '{{$prop}}','id asc' , new TCriteria());");
            }
        } else {
            $field = new TEntry($opt->name);
        }
        return $field;
    }
 
     /**
     * Method get - retorna um array de options
     * @param $partial - string a ser buscada no nome das options
     * @returns options array
     */
    public static function findOptions($partial)
    {
        Util::open();
        $objs = self::where('name', 'LIKE', "%{$partial}%")->load();
        Util::close();
        return $objs;
    }
 
     /**
     * Method get - remove options que casarem com a string enviada
     * @param $partial - string a ser buscada no nome das options
     * @returns options array
     */
    public static function removeOptions($partial)
    {
        $objs = self::findOptions($partial);
        if (count($objs)) {
            Util::open();
            foreach ($objs as $obj) {
                $obj->delete();
            }
            Util::close();
        }
    
    }
 
     /**
     * Method get - retorna o valor da option pelo nome
     * @param $name - nome da option
     * @param $default - valor retornado caso a options não exista
     * @param $prop - nome da propriedade desejada
     * @param $adm - Option administrativa?
     * @returns option value
     */
    public static function getValue($name, $default = NULL, $prop = 'value', $adm = false)
    {
        $prefix = $adm ? 'admin_' : '';
        Util::open();
        $obj = self::where('name', '=', $prefix . $name)->first();
        Util::close();
        if ($obj) {
            return $obj->{$prop};
        }
        return $default;
    }
    public static function getAdmValue($name, $default = NULL, $prop = 'value')
    {
        return self::getValue($name, $default, $prop, true);
    }

     /**
     * Method set - define o valor da option pelo nome
     * @param $name - nome da option
     * @param $value - novo valor da option
     * @param $adm - Option administrativa?
     * @returns void
     */
    public static function setValue($name, $value, $adm = false)
    {
        $prefix = $adm ? 'admin_' : '';
        Util::open();
        $obj = self::where('name', '=', $prefix . $name)->first();
        $ret = false;
        if ($obj) {
            $obj->value = $value;
            $obj->store();
            $ret = true;
        }
        Util::close();
        return $ret;
    }
    public static function setAdmValue($name, $value)
    {
        return self::setValue($name, $value, true);
    }

     /**
     * Method addValue - add a new global option
     * @param $name - nome da option
     * @param $value - valor da option
     * @param $adm - Option administrativa?
     * @returns void
     */
    public static function addValue($name, $value, $adm = false)
    {
        Util::open();
        $prefix = $adm ? 'admin_' : '';
        $obj = new self();
        $obj->name = Util::toVarName($prefix . $name);
        $obj->readable_name = $name;
        $obj->value = $value;
        $obj->field_type = 'text';
        $obj->edit_options = '';
        $obj->project_id = NULL;
        $obj->store();
        Util::close();
    }
    public static function addAdmValue($name, $value)
    {
        self::addValue($name, $value, true);
    }

     /**
     * Method setOrAddValue - set an existent option or add a new global option
     * @param $name - nome da option
     * @param $value - valor da option
     * @param $adm - Option administrativa?
     * @returns void
     */
    public static function setOrAddValue($name, $value, $adm = false)
    {
        if (self::isSet($name, $adm)) {
            return self::setValue($name, $value, $adm);
        }
        return self::addValue($name, $value, $adm);
    }
    public static function setOrAddAdmValue($name, $value)
    {
        return self::setOrAddValue($name, $value, true);
    }

     /**
     * Method remove - remove option by name
     * @param $name - nome da option
     * @param $adm - Option administrativa?
     * @returns void
     */
    public static function removeValue($name, $adm = false)
    {
        $id = self::getValue($name, 0, 'id', $adm);

        if($id)
        {
            Util::open();
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
            Util::close();
        }
    }
    public static function removeAdmValue($name)
    {
        return self::removeValue($name, true);
    }

    public static function isSet($name, $adm = false)
    {
        $val = self::getValue($name, '__unset__', 'value', $adm);
        return ('__unset__' !== $val);
    }

     /**
     * Method restDelete - remove option by id
     * @param $param - ajax params
     * @returns void
     */
    public static function restDelete($param) {
        if (!empty($param['id'])) {
            Util::open();
            $obj = self::find($param['id']);
            if ($obj) {
                $obj->delete();
            }
            Util::close();
            return true;
        }
        return false;
    }

                                                        
}

