<?php
/**
 * CdmLink export form 
 *
 * @package  CdmLink
 * @copyright   2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * CdmLink form class
 */
class Cdm_Form_Export extends Omeka_Form
{
    /**
     * Construct the export form.
     *
     *@return void
     */
    public function init()
    {
        parent::init();
        $this->_registerElements();
    }

    /**
     * Define the form elements.
     *
     *@return void
     */
    private function _registerElements()
    {
        if(version_compare(OMEKA_VERSION,'2.2.1') >= 0)
            $this->addElement('hash','cdm_export_token');
               
	// Cdm Collection:
        $cdmCollections = array('CONNECTION ERROR');
        try{
            $cdmCollections = cdm_get_collections('Select a collection','0');
        } catch(Exception $e) {
            //do nothing
        }

        $this->addElement('select', 'cdmcollection', array(
							'label'         => __('ContentDM Collection'),
							'description'   => __('From which ContentDM collection would you like to export content?'),
							'value'         => '0',
							'order'         => 2,
							'multiOptions'       => $cdmCollections
							)
			  );
        
        // Submit:
        $this->addElement('submit', 'cdmexportsubmit', array(
            'label' => __('Export Item(s)'),
	    'order'         => 8
        ));

	//Display Groups:
        $this->addDisplayGroup(
			       array(
				     'cdmcollection',
				     ),
			       'source-fields'
			       );
	
        $this->addDisplayGroup(
			       array(
				     'cdmexportsubmit'
				     ), 
			       'submit_buttons'
			       );
    }

    /**
     *Process the form data and output csv as necessary
     *
     *@return bool $success true if successful 
     */
    public static function Export()
    {

        if(!$cdmCollection = $_REQUEST['cdmcollection'])
            return;        

        if(isset($_REQUEST['cdm-items']))
            $pointers = $_REQUEST['cdm-items'];
        else 
            return;
        
        if(!is_array($pointers) || count($pointers)==0 )
            return;

        $items = array();

        $fields = cdm_get_fields($cdmCollection);

        //header
        outputCSV($fields);

        foreach($pointers as $index=>$pointer) {
            $line='';
            set_time_limit(10);
            $meta = cdm_get_raw_item_meta($cdmCollection,$pointer);
            foreach($fields as $nick=>$name){
                if(is_array($meta[$nick])){
                    foreach($meta[$nick] as $key=>$value){
                        if(strpos($value,'|')!==FALSE)
                            $meta[$nick][$key]  = str_replace('|','',$value);
                    }
                    $meta[$nick] = implode('|',$meta[$nick]);
                }
                $line[]=$meta[$nick];
            }
            outputCSV($line);
        }
        return true;
    }
}