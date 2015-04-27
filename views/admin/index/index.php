<?php

$head = array('bodyclass' => 'cdm-link primary', 
              'title' => html_escape(__('CdmLink | Import documents')));
echo head($head);
echo flash(); 
echo $form;
?>
<div id="cdm-search-div" class="field">
<input type="text" id="cdm-search-box" />
<select id="cdm-mode-select" name="cdm-mode-select">
<option value="all">Contains all words</option>
<option value="any">Contains any word</option>
<option value="phrase">Contains entire phrase</option>
<option value="exact">Matches exactly</option>
</select>
<select id="cdm-field-select" name="cdm-field-select">
<option value="all">All</option>
</select>
<button type="notsubmit" id="cdm-search-button">Search</button>
<div id='cdm-preview'></div>
</div>
<?
echo foot(); 
?>