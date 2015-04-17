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
        'upgrade'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'cdmUrl' => '',
    );

    /**
     * Install the plugin's options
     *
     * @return void
     */
    public function hookInstall() {
        $this->_installOptions();
    }

    /**
     * Uninstall the options
     *
     * @return void
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    public function hookUpgrade()
    {
        set_option('cdmKnownSchema',serialize(array('dc'=>'Dublin Core','ucldc_schema'=>'UCLDC Schema')));
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
      if(!empty($_REQUEST['cdmUrl']))
	set_option('cdmUrl',$_REQUEST['cdmUrl']);        
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
            'label' => __('Cdm Link'),
            'uri' => url('cdm-link'),
            'resource' => 'CdmLink_Index',
            'privilege' => 'index'
        );
        return $nav;
    }
}