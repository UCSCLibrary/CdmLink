
jQuery(document).ready(implementSearch);
var url = 'cdm-link/index/folders/uid/';
var docUrl = 'cdm-link/index/documents?path=';
var searchUrl = '';

function implementSearch() {
    jQuery('#cdm-search-button').click(function(e){
	e.preventDefault();
	jQuery('#cdm-preview').html('<h3>Searching...</h3><p>Retrieving documents containing your search terms. For large datasets, this could take a minute. Thanks for your patience.</p>');

	searchTerm = jQuery('#cdm-search-box').val();
	var url = searchUrl+id+'/search/'+searchTerm;
	jQuery.get(
	    url,
	    function(jsonData) {
		data = jQuery.parseJSON(jsonData);
	    }
	);
	
    });
}

function bindButtonActions() {
    jQuery('#select-all').click(function(e) {
	e.preventDefault();
	jQuery('.import-check').prop('checked',true);
    });
    jQuery('#select-none').click(function(e) {
	e.preventDefault();
	jQuery('.import-check').attr('checked',false);
    });

}

function getPath(id) {

}
