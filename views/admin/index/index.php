<?php

$head = array('bodyclass' => 'cdm-link primary', 
              'title' => html_escape(__('CdmLink | Import documents')));
echo head($head);
echo flash(); 
echo $form;
?>
<div id="cdm-search-div" class="field">
<input type="text" id="cdm-search-box" />
<button type="notsubmit" id="cdm-search-button">Search</button>
</div>
<?
echo foot(); 
?>