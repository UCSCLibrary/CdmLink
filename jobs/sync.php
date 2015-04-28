<?php
/**
 * CdmLink sync job
 *
 * @package CdmLink
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The CdmLink syc job class.
 *
 * @package CdmLink
 */
class CdmLink_SyncJob extends Omeka_Job_AbstractJob
{
    public function perform()
    {
        Zend_Registry::get('bootstrap')->bootstrap('Acl');

        //require the helpers
        require_once(dirname(dirname(__FILE__)).'/helpers/APIfunctions.php');

        $syncs = get_db()->getTable('CdmSync')->findAll();
        foreach($syncs as $sync) {
            if($sync->modified == cdm_get_modified($sync->collection,$sync->pointer))
                continue;
            cdm_sync_item($sync->collection,$sync->pointer,$sync->item_id);
        }
    }
}