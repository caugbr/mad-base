<?php

class BuilderPermissionService{
    
    public static function checkPermission()
    {
        if (TSession::getValue('login') !== 'admin')
        {
            throw new Exception(_bt('Permission denied'));
        }
    }

    public static function canManageRecordByUnit($record)
    {
        $unit_column_name = $record->getCreatedByUnitIdColumn();
    
        if($unit_column_name && TSession::getValue('userunitids'))
        {
            $pk = $record->getPrimaryKey();
            
            if (($record->{$unit_column_name} && !in_array($record->{$unit_column_name}, TSession::getValue('userunitids'))) || ($record->{$pk} && !$record->{$unit_column_name}))
            {
                throw new Exception(_t('No permission to manage this record!'));
            }
        }
    }
}