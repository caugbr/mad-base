<?php

class UserRole extends TRecord
{
    const TABLENAME  = 'user_role';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $user;
    private $role;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('user_id');
        parent::addAttribute('role_id');
    
    }

    /**
     * Method set_system_users
     * Sample of usage: $var->system_users = $object;
     * @param $object Instance of SystemUsers
     */
    public function set_user(SystemUsers $object)
    {
        $this->user = $object;
        $this->user_id = $object->id;
    }

    /**
     * Method get_user
     * Sample of usage: $var->user->attribute;
     * @returns SystemUsers instance
     */
    public function get_user()
    {
    
        // loads the associated object
        if (empty($this->user))
            $this->user = new SystemUsers($this->user_id);
    
        // returns the associated object
        return $this->user;
    }
    /**
     * Method set_role
     * Sample of usage: $var->role = $object;
     * @param $object Instance of Role
     */
    public function set_role(Role $object)
    {
        $this->role = $object;
        $this->role_id = $object->id;
    }

    /**
     * Method get_role
     * Sample of usage: $var->role->attribute;
     * @returns Role instance
     */
    public function get_role()
    {
    
        // loads the associated object
        if (empty($this->role))
            $this->role = new Role($this->role_id);
    
        // returns the associated object
        return $this->role;
    }

    public static function setRoleId($userid, $role)
    {
        if (preg_match("/[a-z]/", $role)) {
            $role = Role::idByName($role);
        }
        $ret = false;
        Util::open();
        $rec = self::where('user_id', '=', $userid)->first();
        if ($rec) {
            $rec->role_id = $role;
            $rec->store();
            $ret = true;
        } else {
            $rec = new self();
            $rec->user_id = $userid;
            $rec->role_id = $role;
            $rec->store();
            $ret = true;
        }
        Util::close();
        return $ret;
    }

    public static function getRoleId($userid)
    {
        Util::open();
        $rec = self::where('user_id', '=', $userid)->first();
        Util::close();
        if ($rec) {
            return $rec->role_id;
        }
        return false;
    }

    public static function getRoleName($userid)
    {
        Util::open();
        $rec = self::where('user_id', '=', $userid)->first();
        if ($rec) {
            return Role::nameById($rec->role_id);
        }
        Util::close();
        return false;
    }

                
}

