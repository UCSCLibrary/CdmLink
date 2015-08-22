<?php
/**
 * CdmLink import form 
 *
 * @package  CdmLink
 * @copyright   2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * CdmLink form class
 */
class Cdm_Form_Import extends Omeka_Form
{
    /**
     * Construct the import form.
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
            $this->addElement('hash','cdm_import_token');
              
        $this->addElement('hidden','base-url',public_url('cdm-link/index/'));

	// Omeka Collection:
        $this->addElement('select', 'cdmomekacollection', array(
							'label'         => __('Omeka Collection'),
							'description'   => __('To which Omeka collection would you like to add the Cdm document(s)?'),
							'value'         => '0',
							'order'         => 1,
							'multiOptions'       => $this->_getOmekaCollectionOptions()
							)
			  );
 
	// Cdm Collection:
        $cdmCollections = array('CONNECTION ERROR');
        try{
            $cdmCollections = cdm_get_collections();
        } catch(Exception $e) {
            //do nothing
        }

        $this->addElement('select', 'cdmcollection', array(
							'label'         => __('ContentDM Collection'),
							'description'   => __('From which ContentDM collection would you like to import content?'),
							'value'         => '0',
							'order'         => 2,
							'multiOptions'       => $cdmCollections
							)
			  );

        // Visibility (public vs private):
        $this->addElement('checkbox', 'cdmpublic', array(
            'label'         => __('Public Visibility'),
            'description'   => __('Would you like to make the imported items public on your Omeka site?'),
            'checked'         => 'checked',
            'order'         => 6
        )
        );
        
        // Submit:
        $this->addElement('submit', 'cdmimportsubmit', array(
            'label' => __('Import Item(s)'),
	    'order'         => 8
        ));

	//Display Groups:
        $this->addDisplayGroup(
			       array(
				     'cdmomekacollection',
				     'cdmpublic'
				     ),
			       'destination-fields'
			       );
        $this->addDisplayGroup(
			       array(
				     'cdmcollection',
				     ),
			       'source-fields'
			       );
	
        $this->addDisplayGroup(
			       array(
				     'cdmimportsubmit'
				     ), 
			       'submit_buttons'
			       );
    }

    /**
     *Process the form data and import the photos as necessary
     *
     *@return bool $success true if successful 
     */
    public static function ProcessPost()
    {
        try {

            if(self::_import())
                return('Your Cdm documents are now being imported. You will recieve an email when your import is complete. This process may take some time if you are importing items with large attachments. You may continue to work while the import completes. You may notice some strange behavior while the items are importing, but it will all be over soon.');

        } catch(Exception $e) 
                {
                    throw new Exception('Error initializing cdm import: '.$e->getMessage());
                }

        return(true);

    }

    private static function _import()
    {
        require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php');

        //process optional values
        if(isset($_REQUEST['cdmcollection']))
            $cdmCollection = $_REQUEST['cdmcollection'];
        else
            $cdmCollection = 0;

        if(isset($_REQUEST['cdmomekacollection']))
            $omekaCollection = $_REQUEST['cdmomekacollection'];
        else
            $omekaCollection = 0;

        if(isset($_REQUEST['cdmpublic']))
            $public = $_REQUEST['cdmpublic'];
        else 
            $public = false;

        if(isset($_REQUEST['cdm-items']))
            $pointers = $_REQUEST['cdm-items'];
        else 
            $pointers = array();

        if(isset($_REQUEST['cdm-collections']))
            $collections = $_REQUEST['cdm-collections'];
        else 
            $collections = array();

        $items = array();
        foreach($pointers as $index=>$pointer) {
            $item['pointer'] = $pointer;
            $item['alias'] = $collections[$index];
            $items[]=$item;
        }

        $user = current_user();
        if(!$user)
            throw new Exception('Could not retrieve logged in user');
        $email = $user->email;

        //set up options to pass to background job
        $options = array(
            'omekaCollection'=>$omekaCollection,
            'public'=>$public,
            'items'=>serialize($items),
            'collections'=>serialize($collections),
            'email'=>$email
        );
        /*
        echo '<pre>';
        print_r($_POST);
        die('</pre>');
        */
        //attempt to start the job
        try{
            $dispacher = Zend_Registry::get('job_dispatcher');
            $dispacher->sendLongRunning('CdmLink_ImportJob',$options);
        } catch (Exception $e) {
            throw($e);
        }
        return(true);
    }

    /**
     * Get an array to be used in formSelect() containing all collections.
     * 
     * @return array $options An associative array mapping collection IDs
     *to their titles for display in a dropdown menu
     */
    private function _getOmekaCollectionOptions()
    {
        $collectionTable = get_db()->getTable('Collection');
        //$options = array('-1'=>'Create New Collection','0'=>'Assign No Collection'); TODO set up autocreate collection
        $options  = array('0'=>'Assign No Collection');
        $pairs = $collectionTable->findPairsForSelectForm();
        foreach($pairs as $key=>$value)
            $options[$key]=$value;
        return $options;
    }
} 
