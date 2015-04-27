<?php
/**
 * CdmLink plugin
 *
 * @package     CdmLink
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * CdmLink plugin class
 * 
 * @package CdmLink
 */
class CdmLinkPlugin extends Omeka_plugin_AbstractPlugin
{
    public function __toString() 
    {
        return $this->name;
    }
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'initialize',
        'config',
        'config_form',
        'admin_head',
        'define_acl',
        'upgrade',
        'public_head'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'cdmServerUrl' => 'https://server16019.contentdm.oclc.org/',
        'cdmWebsiteUrl' => 'http://digitalcollections.ucsc.edu',
        'cdmMaxRecs' => 200,
        'cdmAvoidTifs' => true,
        'cdmScaleTifs' => 50,
        'cdmCreatePDFs' => true,
        'cdmAutoSync' => true,
        'cdmLastSynced'=>false
    );

    /**
     * Install the plugin's options
     *
     * @return void
     */
    public function hookInstall() {
        $this->_installOptions();
        //add database table of syncs
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->_db->CdmSync}` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `item_id` int(10) unsigned NOT NULL,
                `collection` text,
                `modified` text,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $this->_db->query($sql);

    }

    /**
     * Uninstall the options
     *
     * @return void
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
        $sql = "DROP TABLE IF EXISTS `$db->CdmSync` ";
        $this->_db->query($sql);

    }

    public function hookUpgrade()
    {
        set_option('cdmKnownSchema',serialize(array('dc'=>'Dublin Core','ucldc_schema'=>'UCLDC Schema')));
    }

    public function hookPublicHead()
    {
   
        //if we haven't synced our imports today
        if(get_option('cdmLastSynced')!=date('dmY')){
            $dispacher = Zend_Registry::get('job_dispatcher');
            $dispacher->sendLongRunning('CdmLink_SyncJob',array());
            set_option('cdmLastSynced',date('dmY'));
        }
   
    }

    /**
     * Require the job and helper files
     *
     * @return void
     */
    public function hookInitialize()
    {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'APIfunctions.php';
    }

    /**
     * Process the plugin config form.
     */
    public function hookConfig() { 
        if(!empty($_REQUEST['cdmServerUrl']))
          set_option('cdmServerUrl',$_REQUEST['cdmServerUrl']);        
        if(!empty($_REQUEST['cdmWebsiteUrl']))
          set_option('cdmWebsiteUrl',$_REQUEST['cdmWebsiteUrl']);        
        if(!empty($_REQUEST['cdmAvoidTifs']))
          set_option('cdmAvoidTifs',$_REQUEST['cdmAvoidTifs']);        
        if(!empty($_REQUEST['cdmCreatePDFs']))
          set_option('cdmCreatePDFs',$_REQUEST['cdmCreatePDFs']);        
        if(!empty($_REQUEST['cdmAutoSync']))
          set_option('cdmAutoSync',$_REQUEST['cdmAutoSync']);        
    }
    
    /**
     * Set the options from the config form input.
     */
    public function hookConfigForm() {
        require dirname(__FILE__) . '/forms/config_form.php';
    }

    /**
     * Queue the javascript and css files to help the form work.
     *
     * This function runs before the admin section of the sit loads.
     * It queues the javascript and css files which help the form work,
     * so that they are loaded before any html output.
     *
     * @return void
     */
    public function hookAdminHead() {
        queue_css_file('cdmLink');
        queue_js_file('cdmLink');
    }

    /**
     * Define the plugin's access control list.
     *
     * @param array $args This array contains a reference to
     * the zend ACL under it's 'acl' key.
     * @return void
     */
    public function hookDefineAcl($args)
    {
        $args['acl']->addResource('CdmLink_Index');
    }

    /**
     * Add the CdmLink link to the admin main navigation.
     * 
     * @param array $nav Array of links for admin nav section
     * @return array $nav Updated array of links for admin nav section
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Content DM'),
            'uri' => url('cdm-link'),
            'resource' => 'CdmLink_Index',
            'privilege' => 'index'
        );
        return $nav;
    }
}