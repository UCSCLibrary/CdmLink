<?php

$head = array('bodyclass' => 'cdm-link primary', 
              'title' => html_escape(__('CdmLink | Export documents')));
echo head($head);
echo flash(); 
echo $form;
?>
<div id="cdm-search-div" class="field">

<h2>Find Items to Export</h2>
<div class="field">
<label for="cdm-search-box">Search for:</label>
<input type="text" id="cdm-search-box" />
</div>

<div class="field">
<label for="cdm-field-select">Search in field:</label>
<select id="cdm-field-select" name="cdm-field-select">
<option value="all">All</option>
</select>
</div>

<div class="field">
<label for="cdm-field-select">Select type of search:</label>
<select id="cdm-mode-select" name="cdm-mode-select">
<option value="all">Contains all words in search entry</option>
<option value="any">Contains any word in search entry</option>
<option value="phrase">Contains entire search entry</option>
<option value="exact">Matches search entry exactly</option>
</select>
</div>

<div class="field">
<button type="notsubmit" id="cdm-search-button">Search</button>
<div id='cdm-preview'></div>
</div>

</div>

<?
echo foot(); 
?>