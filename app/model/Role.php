<?php

class Role extends TRecord
{
    const TABLENAME  = 'role';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('capabilities');
        parent::addAttribute('denied');
        parent::addAttribute('group_names');
        parent::addAttribute('frontpage_id');
    
    }

    /**
     * Method getUserRoles
     */
    public function getUserRoles()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('role_id', '=', $this->id));
        return UserRole::getObjects( $criteria );
    }

    public function set_user_role_user_to_string($user_role_user_to_string)
    {
        if(is_array($user_role_user_to_string))
        {
            $values = SystemUsers::where('id', 'in', $user_role_user_to_string)->getIndexedArray('name', 'name');
            $this->user_role_user_to_string = implode(', ', $values);
        }
        else
        {
            $this->user_role_user_to_string = $user_role_user_to_string;
        }

        $this->vdata['user_role_user_to_string'] = $this->user_role_user_to_string;
    }

    public function get_user_role_user_to_string()
    {
        if(!empty($this->user_role_user_to_string))
        {
            return $this->user_role_user_to_string;
        }
    
        $values = UserRole::where('role_id', '=', $this->id)->getIndexedArray('user_id','{user->name}');
        return implode(', ', $values);
    }

    public function set_user_role_role_to_string($user_role_role_to_string)
    {
        if(is_array($user_role_role_to_string))
        {
            $values = Role::where('id', 'in', $user_role_role_to_string)->getIndexedArray('id', 'id');
            $this->user_role_role_to_string = implode(', ', $values);
        }
        else
        {
            $this->user_role_role_to_string = $user_role_role_to_string;
        }

        $this->vdata['user_role_role_to_string'] = $this->user_role_role_to_string;
    }

    public function get_user_role_role_to_string()
    {
        if(!empty($this->user_role_role_to_string))
        {
            return $this->user_role_role_to_string;
        }
    
        $values = UserRole::where('role_id', '=', $this->id)->getIndexedArray('role_id','{role->id}');
        return implode(', ', $values);
    }

    /**
     * Method onBeforeDelete
     */
    public function onBeforeDelete()
    {
    

        if(UserRole::where('role_id', '=', $this->id)->first())
        {
            throw new Exception("Não é possível deletar este registro pois ele está sendo utilizado em outra parte do sistema");
        }
    
    }

    // nome do role pelo id
    public static function nameById($roleid)
    {
        $rec = new self($roleid);
        return $rec->name ?? '';
    }

    // id do role pelo nome
    public static function idByName($role_name)
    {
        $rec = self::where('name', '=', $role_name)->first();
        return $rec->id ?? 0;
    }

    // testa de o role (name ou id) possui a capability (SsystemProgram) enviada (name)
    public static function roleHasCap($role, $cap)
    {
        $id = is_numeric($role) ? $role : self::idByName($role);
        Util::open();
        $rec = new self($id);
        Util::close();
        $ret = $rec->capabilities == 'all' ? true : preg_match("/\b{$cap}\b/", $rec->capabilities);
        if (preg_match("/\b{$cap}\b/", $rec->denied)) {
            $ret = false;
        }
        return $ret;
    }

    // devolve um array com informações sobre as capabilities do role enviado (id)
    public static function getGroupsArray($roleid)
    {
        Util::open();
        $rec = self::find($roleid);
        $arr = [];
        if ($rec) {
            if (!empty(trim($rec->group_names))) {
                $group_names = explode(",", trim($rec->group_names));
                Util::open('permission');
                if ($group_names[0] == 'all') {
                    $objs = SystemGroup::all();
                    foreach($objs as $obj) {
                        $arr[] = [ "id" => $obj->id, "name" => $obj->name ];
                    }
                } else {
                    foreach($group_names as $name) {
                        $obj = SystemGroup::where('name', '=', $name)->first();
                        $arr[] = [ "id" => $obj->id, "name" => $name ];
                    }
                }
            }
        }
        Util::close();
        return $arr;
    }

    // devolve um array com informações sobre os grupos do role enviado (id)
    public static function getCapsArray($roleid)
    {
        Util::open();
        $rec = self::find($roleid);
        Util::close();
        $arr = [];
        if ($rec) {
            $caps = explode(" ", $rec->capabilities);
            $deny = explode(" ", $rec->denied);
            $arr = [];
            Util::open('permission');
            $allCaps = SystemProgram::all();
            Util::close();
            if ($caps[0] == 'all') {
                foreach($allCaps as $cap) {
                    if (!in_array($cap->controller, $deny)) {
                        $arr[] = [
                            "id" => $cap->id,
                            "name" => $cap->name,
                            "controller" => $cap->controller,
                        ];
                    }
                }
            } else {
                foreach($allCaps as $cap) {
                    if (in_array($cap->controller, $caps)) {
                        $arr[] = [
                            "id" => $cap->id,
                            "name" => $cap->name,
                            "controller" => $cap->controller,
                        ];
                    }
                }
            }
        }
        return $arr;
    }

    // testa se o usuário (enviado ou o user atual) possui certa capability
    public static function userCan($obj, $func = '', $user = -1)
    {
        if ($func == '__construct') {
            $func = '';
        }
        $userid = $user > 0 ? $user : TSession::getValue('userid');
        Util::open();
        $role = UserRole::where('user_id', '=', $userid)->first();
        Util::close();
        $roleid = $role ? $role->roles_id : 0;
        $cap = empty($func) ? $obj : "{$obj}::{$func}";
        return self::roleHasCap($roleid, $cap);
    }

    public static function getUsers($role_id)
    {
        Util::open();
        $users = UserRole::where('roles_id', '=', $role_id)->load();
        Util::close();
        return array_map(function($e) {
            return $e->id;
        }, $users);
    }

    public static function getUserRole($userid)
    {
        $role = UserRole::getRoleId($userid);
        return $role;
    }

    public static function forceUpdate($usr, $param)
    {
        if (!empty($param['role_id'])) {
            UserRole::setRoleId($usr->id, $param['role_id']);
        }
        self::updateUserRoles(true);
        return $usr;
    }

    public static function updateUserRoles($force = false)
    {
        $opt = Options::getAdmValue('roles_are_set');
        if ($force || !$opt) {
            Util::open('permission');
            $usrs = SystemUsers::all();
            foreach($usrs as $usr) {
                Util::open();
                if (!($role = UserRole::getRoleId($usr->id))) {
                    continue;
                }

                $caps = self::getCapsArray($role);
                Util::open('permission');
                $usr->clearParts();
                foreach ($caps as $cap) {
                    $usr->addSystemUserProgram(new SystemProgram($cap['id']));
                }

                $grps = self::getGroupsArray($role);
                Util::open('permission');
                foreach($grps as $grp) {
                    $usr->addSystemUserGroup(new SystemGroup($grp['id']));
                }

                $default_frontpage = SystemProgram::findByController('TarefaKanbanView');
                $usr->frontpage_id = empty($role->frontpage_id) ? $default_frontpage : $role->frontpage_id;
                $usr->store();
                if (!$opt) {
                    Util::open();
                    Options::setOrAddAdmValue('roles_are_set', 1);
                }
            }
        }
        Util::close();
    }

    public static function addHooks()
    {
        Actions::add('init', ['Role', 'updateUserRoles']);
        Actions::add('userForm', ['Role', 'addUserFormFields']);
        Actions::add('editUser', ['Role', 'addRoleId']);
        Actions::add('beforeSaveUser', ['Role', 'addCapsAndGroupsToSave']);
        Actions::add('saveUser', ['Role', 'forceUpdate']);
        Actions::add('userDatagridColumns', ['Role', 'addRoleColumn']);
    }

    public static function addRoleColumn($datagrid)
    {
        $column_role = new TDataGridColumn('role', "Role", 'left');
        $column_role->setTransformer(function($value, $object, $row, $cell = null, $last_row = null)
        {
            $role = UserRole::getRoleName($object->id);
            return $role;
        });
        $datagrid->addColumn($column_role);
        return $datagrid;
    }

    public static function addCapsAndGroupsToSave($data)
    {
        $caps = Role::getCapsArray($data->role_id);
        $group_names = Role::getGroupsArray($data->role_id);

        $data->group_names = array_map(function($e) { return $e['id']; }, $group_names);
        $data->system_programs = array_map(function($e) { return $e['id']; }, $caps);

        return $data;
    }

    public static function addRoleId($obj)
    {
        $obj->role_id = UserRole::getRoleId($obj->id);
        return $obj;
    }

    public static function addUserFormFields($form)
    {
        $role_id = new TDBCombo('role_id', Util::$database, 'Role', 'id', '{name}','name asc' , new TCriteria());
        $role_id->setValue('2');
        $role_id->setDefaultOption(false);
        $form->addContent([new TFormSeparator("Nível do usuário", '#333333', '18', '#eeeeee')]);
        $row = $form->addFields([$role_id]);
        $row->layout = ['col-sm-12'];
        return $form;
    }

                                                                
}

