<?php
/**
 * Cdmlink
 *
 * @package Cdmlink
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Cdmlink index controller class.
 *
 * @package Cdmlink
 */
class Cdmlink_IndexController extends Omeka_Controller_AbstractActionController
{    

  /**
   * The default action to display the import form and process it.
   *
   * This action runs before loading the main import form. It 
   * processes the form output if there is any, and populates
   * some variables used by the form.
   *
   * @param void
   * @return void
   */

  public function indexAction()
  {
      include_once(dirname(dirname(__FILE__))."/forms/ImportForm.php");
      $form = new Cdm_Form_Import();
      $form->getElement('base-url')->setValue(public_url('cdm-link/index/')); 
      //initialize flash messenger for success or fail messages
      $flashMessenger = $this->_helper->FlashMessenger;

      try{
          if ($this->getRequest()->isPost()){
              if($form->isValid($this->getRequest()->getPost()))
                  $successMessage = Cdm_Form_Import::ProcessPost();
              else 
                  $flashMessenger->addMessage('There was a problem importing your Cdm documents. Please check your Cdm URL settings.','error');
          } 
      } catch (Exception $e){
          $flashMessenger->addMessage($e->getMessage(),'error');
      }

    $backgroundErrors = unserialize(get_option('cdmBackgroundErrors'));
    if(is_array($backgroundErrors))
      foreach($backgroundErrors as $backgroundError)
	{
	  $flashMessenger->addMessage($backgroundError,'error');
	}
    set_option('cdmBackgroundErrors',"");
    
    if(isset($successMessage))
        $flashMessenger->addMessage($successMessage,'success');
    $this->view->form = $form;
  }

  public function exportAction()
  {
      include_once(dirname(dirname(__FILE__))."/forms/ExportForm.php");
      $form = new Cdm_Form_Export();
      $form->getElement('base-url')->setValue(public_url('cdm-link/index/'));     
  
      //initialize flash messenger for success or fail messages
      $flashMessenger = $this->_helper->FlashMessenger;

      if ($this->getRequest()->isPost()){
          if($form->isValid($this->getRequest()->getPost())) {
              //set headers 
              header('Content-type: text/csv');
              header('Content-Disposition: attachment; filename="cdm-export-'.date("Y-m-d-H-i-s").'.csv"');
              //disable view rendering
              $this->_helper->viewRenderer->setNoRender();
              Cdm_Form_Export::Export();
          } else { 
              $flashMessenger->addMessage('There was a problem exporting your Cdm documents. Please check your Cdm URL settings.','error');
          }  
      } 

    $this->view->form = $form;
  }
  
  public function searchAction()
  {
      //require the helpers
      require_once(dirname(dirname(__FILE__)).'/helpers/APIfunctions.php');
      $searchTerms = array();
      $searchTerms[] = array(
          'string' => $this->getParam('search'),
          'field' => $this->hasParam('field') ? $this->getParam('field') : 'all',
          'mode' => $this->hasParam('mode') ? $this->getParam('mode') : 'all',
          'operator' => 'and'
      );
      for ($i=2;$i<5;$i++) {
          if($this->hasParam('search'.$i)) {
              $searchTerms[] = array(
                  'string' => $this->getParam('search'.$i),
                  'field' => $this->hasParam('field') ? $this->getParam('field'.$i) : 'all',
                  'mode' => $this->hasParam('mode') ? $this->getParam('mode'.$i) : 'all',
                  'operator' => $this->hasParam('operator') ? $this->getParam('operator'.$i) : 'and'
              );
          }
      }

      $collection = $this->getParam('collection');
      try{
          $documents = cdm_search($collection,$searchTerms);
      } catch (Exception $e) {
          die('Error connecting to ContentDM. Please check your internet connection and plugin configuration.');
      }
      die(json_encode($documents));
  }

  public function fieldsAction()
  {
      //require the helpers
      require_once(dirname(dirname(__FILE__)).'/helpers/APIfunctions.php');
      try{
          $fields = json_encode(cdm_get_fields('/'.$this->getParam('collection')));
      }catch(Exception $e) {
          die('Error connecting to ContentDM. Please check your internet connection and plugin configuration.');
      }
      die($fields);
  }

}
