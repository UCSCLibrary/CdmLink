var cdmflag = false;
var searchUrl = 'search/collection';
var baseUrl;
jQuery(document).ready(implementSearch);
jQuery(document).ready(function(){
  baseUrl = jQuery('input#baseurl').val();
  jQuery('#cdmcollection').change(function(){
    jQuery('#cdm-field-select').html('<option value="all">All</option>');
    url = baseUrl+'fields/collection'+jQuery('#cdmcollection').val();
    jQuery.get(url,function(data){
      fields = JSON.parse(data);
      jQuery.each(fields,function(index,value){
	option = '<option value="'+index+'" >'+value+'</option>';
	jQuery('#cdm-field-select').append(option);
      });
    });
  });

  jQuery('input#cdmExportAll').click(function(){
    if (this.checked) {
      console.log('checked');
      jQuery('div#cdm-search-div input').prop('disabled',true);
      jQuery('div#cdm-search-div button').prop('disabled',true);
      jQuery('fieldset#fieldset-submit_buttons').show();
    } else {
      jQuery('div#cdm-search-div input').prop('disabled',false);
      jQuery('div#cdm-search-div button').prop('disabled',false);
    }
  });
  

  jQuery('.cdm-link #content form').submit(function(e){
    if(!cdmflag){
      cdmflag = true;
      e.preventDefault();
      jQuery('.cdm-link #preview-list input[type=checkbox]').each(function(index,value){
	if(!jQuery(this).is(':checked')) {
	  jQuery(this).siblings('input[type=hidden]').remove();
	}
      });
      jQuery(this).submit();
    }
  });
});

function implementSearch() {
  jQuery('#content>#cdm-search-div').insertAfter('#fieldset-sourcefields');
  
  jQuery('#cdm-search-button').click(function(e){
    e.preventDefault();
    jQuery('#cdm-preview').html('<h3>Searching...</h3><p>Retrieving documents containing your search terms. For large datasets, this could take a minute. Thanks for your patience.</p>');

    searchTerm = jQuery('#cdm-search-box').val();
    collection = jQuery('#cdmcollection').val();
    jQuery.get(
      baseUrl+searchUrl+collection+'/search/'+searchTerm,
      function(jsonData) {
	data = jQuery.parseJSON(jsonData);
	if(data.length > 0) {
	  jQuery('#cdm-preview').html('<div id="select-buttons"><button id="select-all" class="select-button">Select All</button><button id="select-none" class="select-button">Select None</button></div><label id="numDocLi">'+data.length+' Documents <div style="font-size:0.8em"></div></label><br><ul id="preview-list"></ul>');
	}else {
	  jQuery('#cdm-preview').html('<h3>No Results</h3><p>Your search returned no results.</p>');
	  return;
	}
	jQuery.each(data,function(index,value) {
	  prevLi = '<li id="preview-'+value['pointer']+'">';
	  prevLi += '<input type="checkbox" class="cdm-check" checked="checked" name="cdm-items[]" value="'+value['pointer']+'" />';
	  prevLi += '<input type="hidden" name="cdm-collections[]" value="'+value['collection']+'" />';
	  prevLi += '<img src="'+encodeURI(value['thumbnail'])+'?path=%2Fviews%2Fitem%5B3%5D%2Fcontent" />';
	  prevLi += "<p>"+value['title']+"</p>";
	  prevLi += "</li>";
	  jQuery('#preview-list').append(prevLi);
	});
	bindButtonActions();
	jQuery('.content>form').append(jQuery('#preview-list'));
	jQuery('#fieldset-submit_buttons').show();
      }
    );
    
  });
}

function bindButtonActions() {
  jQuery('#select-all').click(function(e) {
    e.preventDefault();
    jQuery('.cdm-check').prop('checked',true);
  });
  jQuery('#select-none').click(function(e) {
    e.preventDefault();
    jQuery('.cdm-check').attr('checked',false);
  });
}
