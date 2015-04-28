<?php
/**
 * A Cdm Sync 
 * 
 * @package CdmSync
 *
 */
class CdmSync extends Omeka_Record_AbstractRecord
{
    public $id;
    /*
     *@var int The item ID of the omeka item
     */
    public $item_id; 

    /*
     *@var int The collection alias in contentDM
     */
    public $collection; 

    /*
     *@var string The identifier of the item within the content DM collection
     */
    public $pointer; 

    /*
     *@var string The last date the content DM item was modified
     */
    public $modified; 

}
?>