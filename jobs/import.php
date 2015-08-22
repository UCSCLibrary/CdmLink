<?php
/**
 * CdmLink import job
 *
 * @package CdmLink
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The CdmLink import job class.
 *
 * @package CdmLink
 */
class CdmLink_ImportJob extends Omeka_Job_AbstractJob
{
    private $_omekaCollection;

    private $_public;    
    
    private $_items;
    
    private $_email;

    public function setOmekaCollection($collection) {
        $this->_omekaCollection = $collection;
    }

    public function setPublic($public) {
        $this->_public = $public;
    }

    public function setItems($items) {
        $this->_items = unserialize($items);
    }

    public function setEmail($email) {
        $this->_email = $email;
    }

    public function perform()
    {
        Zend_Registry::get('bootstrap')->bootstrap('Acl');
      
        //require the helpers
        require_once(dirname(dirname(__FILE__)).'/helpers/APIfunctions.php');

        foreach($this->_items as $item) {

            $collection = $item['alias'];
            $pointer = $item['pointer'];

//            echo("$collection - $pointer \n");
//            echo($this->_public."\n");
//            echo($this->_omekaCollection."\n");
            
            //The $filename variable will hold the filename of the file 
            //associated with the primary 
            //contentDM record, if any. This only applies to simple objects.
            //Compound objects have files associated with their pages. These have
            //other filenames not mentioned here, and they are also
            //imported to Omeka during the import process.
            $filename = false;

            $item = new Item();
            $item->public = $this->_public;
            $item->collection_id = $this->_omekaCollection ? $this->_omekaCollection : null;
            $item->save();

            cdm_add_meta_and_files($item,$collection,$pointer);

            //set up an sync record
            //do this even if sync is not activated, so it can be enabled for these items later
//            include_once();
            require_once(dirname(dirname(__FILE__)).'/models/CdmSync.php');
            $sync = new CdmSync();
            $sync->item_id = $item->id;
            $sync->collection = $collection;
            $sync->pointer = $pointer;
            $sync->modified = cdm_get_modified($collection,$pointer);
            $sync->save();
        }

        //email user to let them know it is finished
        $subject = "Cdm -> Omeka: Import Completed";
        $message = "Your import from Cdm into Omeka has completed successfully. Your new items are ready to view in your Omeka dashboard. Have a nice day!";
        mail($this->_email,$subject,$message);
    }

}
