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
        $this->_collection = $collection;
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
            $meta = cdm_get_item_meta($collection,$pointer);

            $item = new Item();
            $item->public = $this->_public;
            $item->collection_id = $this->_omekaCollection;
            $item->save();

            foreach($meta as $field=>$values) {
                $field = strpos($field,'-') ? substr($field,0,strpos($field,'-')) : $field ;
                $elementTable = get_db()->getTable('Element');
                $elementSet = $field==='Transcript' ? 'Item Type Metadata' : 'Dublin Core';
                    $field = str_replace('Transcript','Transcription',$field);
                $element = $elementTable->findbyElementSetNameAndElementName($elementSet,$field);
//                if(!is_object($element))
//                    echo('FIELD:'.$field.":$value \n");
                if($field === 'filename' && !empty($values)){
                    $filename = is_array($values) ? $values[0] : $values;
                }
                $values = is_array($values) ? $values : array();
                foreach($values as $value){
                    if(empty($value))
                        continue;
                    $eText = new ElementText();
                    $eText->element_id = $element->id;
                    $eText->record_id = $item->id;
                    $eText->record_type = 'Item';
                    $eText->text = $value;
                    $eText->html = 0;
                    $eText->save();
                }
            }
            $filename = isset($filename) ? $filename : false;
            cdm_insert_item_files($item,$collection,$pointer,$filename);

            //set up an sync record
            //do this even if sync is not activated, so it can be enabled for these items later
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