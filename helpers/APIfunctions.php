<?php

function cdm_search($collection,$terms) 
{
    $searchStrings = '';
/*    echo('<pre>');
    print_r($terms);
    echo('</pre>');
    die();*/
    foreach($terms as $term) {
        if($term['field']=='all')
            $term['field']=='CISOSEARCHALL';
        str_replace('!','',$term['string']);
        str_replace('^','',$term['string']);
        $searchStrings .= $term['field'].'^'.$term['string'].'^'.$term['mode'].'^'.$term['operator'];
        $searchStrings .= '!';
    }
    $searchStrings = rtrim($searchStrings,'!');
    $fields = 'title!descri!itemcn';
    $sortby = 'title';
    $maxrecs = get_option('cdmMaxRecs');
    $firstRecordNumber = 1;
    $suppressCompoundPages = 1;
    $docptr = 0;
    $suggest = 0;
    $facets = 0;
    $showunpub = 0;
    $denormalizeFacets = 0;
    
    $cdmUrl = get_option('cdmServerUrl');
    $cdmUrl.= '/dmwebservices/index.php?q=dmQuery/';
    $cdmUrl .= $collection.'/'; 
    $cdmUrl .= $searchStrings.'/';
    $cdmUrl .= $fields.'/';
    $cdmUrl .= $sortby.'/';
    $cdmUrl .= $maxrecs.'/';
    $cdmUrl .= $firstRecordNumber.'/';
    $cdmUrl .= $suppressCompoundPages.'/';
    $cdmUrl .= $docptr.'/';
    $cdmUrl .= $suggest.'/';
    $cdmUrl .= $facets.'/';
    $cdmUrl .= $showunpub.'/';
    $cdmUrl .= $denormalizeFacets.'/';
    $cdmUrl .= 'json';

    $response = json_decode(file_get_contents($cdmUrl),true);
    $records = $response['records'];
    $results = array();

    foreach($records as $record) {
        $results[] = array(
            'title'=>$record['title'],
            'description'=>$record['descri'],
            'pointer'=>$record['pointer'],
            'collection'=>$record['collection'],
            'thumbnail'=> get_option('cdmWebsiteUrl').'/utils/getthumbnail/collection'.$record['collection'].'/id/'.$record['pointer']
        );
    }
    return $results;
}

function cdm_get_collections() 
{
/*
http://CdmServer.com:port/dmwebservices/index.php?q=dmGetCollectionList/format
Example
https://server16019.contentdm.oclc.org/dmwebservices/index.php?q=dmGetCollectionList/json
 */
    $url = get_option('cdmServerUrl');
    $url .= "/dmwebservices/index.php?q=dmGetCollectionList/json";
    $collections = json_decode(file_get_contents($url),true);
    $options  = array('/all'=>'All Collections');
    foreach($collections as $collection)
        $options[$collection['alias']]=$collection['name'];
    return $options;    
}

function cdm_child_descend($node,$pages=false) {
    if(!is_array($pages) )
        $pages = array();
    if(empty($node) )
        return false;
    foreach($node as $field=>$value) {
        if($field === 'page') {
            foreach($value as $page){
                $pages[]=array(
                    'title'=>$page['pagetitle'],
                    'pointer'=>$page['pageptr'],
                    'filename'=>$page['pagefile']
                );
            }
        }
        if($field === 'node') 
            $pages = cdm_child_descend($value,$pages);
    }
    return $pages;
}

function cdm_get_public_url($collection,$find) {
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=dmGetItemUrl'.$collection.'/'.$find.'/json';
    $urlInfo = json_decode(file_get_contents($url),true);      
    return $urlInfo['URL'];
}

function cdm_get_child_pages($collection,$pointer)
{
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=dmGetCompoundObjectInfo'.$collection.'/'.$pointer.'/json';
    $objectMeta = json_decode(file_get_contents($url),true);  
    if(isset($objectMeta['error']))
        return array();
    $pages = cdm_child_descend($objectMeta,array());
    return $pages;
}

function cdm_get_transcript($collection,$pointer) 
{
    $pages = cdm_get_child_pages($collection,$pointer);
    $transcript = "";
    foreach($pages as $page) { 
        $url = get_option('cdmServerUrl');
        $url .= '/dmwebservices/index.php?q=dmGetItemInfo'.$collection.'/'.$page['pointer'].'/json';
        $pageMeta = json_decode(file_get_contents($url),true);
        if(isset($pageMeta['transc'])) {
            $pageMeta['transc'] = is_array($pageMeta['transc']) ? $pageMeta['transc'] : array($pageMeta['transc']);
            foreach($pageMeta['transc'] as $pageTranscript) 
                $transcript .= $pageTranscript.' ';
        }
    }
    return $transcript ? $transcript : false;
}

function cdm_get_item_meta($collection,$pointer)
{
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=dmGetItemInfo'.$collection.'/'.$pointer.'/json';

    $rawMeta = json_decode(file_get_contents($url),true);
    $fieldmap = cdm_get_field_map($collection);
    $meta = array();
    foreach($rawMeta as $field => $value){
        if ($field == 'master')
            $meta['filename'] = $value;
        if(!array_key_exists($field,$fieldmap))
            continue;
        $meta[$fieldmap[$field]][]=$value;
    }
    $meta['Transcript'][]= cdm_get_transcript($collection,$pointer);
    $meta['Relation'][]= cdm_get_public_url($collection,$rawMeta['find']);
    return $meta;
}

function cdm_get_modified($collection,$pointer) {
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=GetItemDmmodified'.$collection.'/'.$pointer.'/json';
    $response = json_decode(file_get_contents($url),true);
    return $response[0];
}

function cdm_download_file($url,$filename) {
    set_time_limit(0);
    $file = fopen($filename, 'w+');
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_URL            => $url,
        CURLOPT_BINARYTRANSFER => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FILE           => $file,
        CURLOPT_TIMEOUT        => 50,
    CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'
    ));
    $response = curl_exec($curl);
    if($response === false) {
        throw new Exception('Curl error: ' . curl_error($curl));
    }
    fclose($file);
    return $filename;
}

function cdm_concat_images($urls,$name=false) {
    $filestring = "";
    foreach($urls as $name=>$url) {
        echo 'checking is image';
        if(!is_array(getimagesize($url)))
            continue;
        echo 'its an image';
        //download image file
        $files[] = cdm_download_file($url,$name);
        //append filename to command string
        $filestring .= " ".end($files);
    }
    $name = $name ? $name : 'pdf'.rand();
    $command = "convert$filestring ".sys_get_tmp_dir().$name.'.pdf';
    set_time_limit(0);
    //execute convert command to create pdf
    exec($command);

    //delete temp files
    $command = 'rm -f'.$filestring;
    exec($command);

    //return pdf filename
    return $name.".pdf";
}

function cdm_get_nonimages($files) {
    $nonImages = array();
    foreach($files as $name=>$file) 
        if(!is_array(getimagesize($file)))
            $nonImages[$name] = $file;
    return $nonImages;
}

function cdm_get_item_files($collection,$pointer,$filename = false)
{
//    $filename = $filename ? $filename : $pointer;
    $urls = array();
    if($filename){
/*        if(get_option('cdmAvoidTifs'))
            if(rtrim($filename,'tif')!==$filename || rtrim($filename,'tiff') !== $filename) {
                $url = get_option('cdmWebsiteUrl');
                $url .= '/utils/ajaxhelper/?CISOROOT='.$collection.'&CISOPTR='.$pointer;
                $url .= '&action=2&DMSCALE='.get_option('cdmScaleTifs');
            }
*/
        
        $urls[str_replace('jp2','jpg',$filename)] = cdm_get_file_url($collection,$pointer,$filename);
    } 

    $childPages = cdm_get_child_pages($collection,$pointer);
    foreach($childPages as $childPage) {
        $urls[str_replace('jp2','jpg',$childPage['filename'])] = cdm_get_file_url($collection,$childPage['pointer'],$childPage['filename']);
    }
    return $urls;
}

function cdm_insert_item_files($item,$collection,$pointer) {
    //add the new files
    $urls = cdm_get_item_files($collection,$pointer);
    $paths = array();
    if(get_option('cdmCreatePDFs') == true) {
        $pdf = cdm_concat_images($urls);
        if($pdf)
            $paths[] = $pdf;
        if($other = cdm_get_nonimages($urls))
            foreach ($other as $filename => $url) 
                $paths[] = cdm_download_file($url,$filename);
         //TODO assign temporary filename here, in case the temp folder for some reason already has a file of the same name
    } else {
        foreach ($urls as $filename=>$url) 
            $paths[] = cdm_download_file($url,$filename);
    }
    insert_files_for_item($item,'Filesystem',$paths,array('ignore_invalid_files'=>true));
    foreach($paths as $path)
        unlink($path);
}

function cdm_get_file_url($collection,$pointer,$filename=false) {

    $url = get_option('cdmServerUrl');
//    $url .= "/dmwebservices/index.php?q=dmGetImageInfo".$collection."/".$pointer.'/json';
//JSON version of this API command is broken for no reason. Thanks contentDM!
    $url .= "/dmwebservices/index.php?q=dmGetImageInfo".$collection."/".$pointer.'/xml';
    $response = file_get_contents($url);
    
    $p = xml_parser_create();
    xml_parse_into_struct($p, $response, $vals, $index);
    xml_parser_free($p);

    if(isset($index['IMAGEINFO'])) {
        $width = $vals[$index['WIDTH'][0]]['value'];
        $height = $vals[$index['HEIGHT'][0]]['value'];
        if($width > 800 || strpos('jp2',$vals[$index['FILENAME'][0]]['value'])) {
            $scale = $width>800 ? 800/$width : 1;
            $newHeight = $scale * $height;
            $url = get_option('cdmWebsiteUrl');
            $url .= '/utils/ajaxhelper/?CISOROOT='.$collection.'&CISOPTR='.$pointer.'&action=2&DMX=0&DMY=0&DMWIDTH=800&DMSCALE='.floor($scale*100).'&DMHEIGHT='.floor($newHeight);
            return $url;
        }
    }
    
    $filename = $filename ? $filename : 'cdm_'.rand();
    $url = get_option('cdmWebsiteUrl');
    $url .= '/utils/getfile/collection'.$collection;
    $url .= '/id/'.$pointer;
    $url .= '/filename/'.$filename;
    return $url;
}

function cdm_sync_item($collection,$pointer,$item_id) {
    
    $item = get_record_by_id('Item',$item_id);
    $eTexts = get_db()->getTable('ElementText')->findByRecord($item);
            
    //delete all element texts
    foreach($eTexts as $eText) {
        $element = get_record_by_id('Element',$eText->element_id);
        $eSet = $element->getElementSet();
        if($eSet->name=='Dublin Core')
            $eText->delete();
        if($eSet->name=='Item Type Metadata' && $element->name=='Transcript')
            $eText->delete();
    }

    //delete all files
    $files = $item->getFiles();
    foreach($files as $file) {
        $file->delete();
    }

    //add new metadata
    $meta = cdm_get_item_meta($collection,$pointer);
    foreach($meta as $field=>$values) {
        $elementTable = get_db()->getTable('Element');
        $elementSet = $field==='Transcript' ? 'Item Type Metadata' : 'Dublin Core';
        $element = $elementTable->findbyElementSetNameAndElementName($elementSet,$field);
        foreach($values as $value){
            $eText = new ElementText();
            $eText->element_id = $element->id;
            $eText->item_id = $item->id;
            $eText->text = $value;
            $eText->save();
        }    
    }
    cdm_insert_item_files($item,$collection,$pointer);
}

function cdm_get_fields($collection)
{
    if($collection=='all')
        return false;
    $url = get_option('cdmServerUrl');
    $url .= "/dmwebservices/index.php?q=dmGetCollectionFieldInfo/";
    $url.= $collection;
    $url .= "/json";
    $fields = json_decode(file_get_contents($url),true);
//      print_r($fields);
    foreach($fields as $field) 
        $rv[$field['nick']]=$field['name'];
    
    return $rv;
}

function cdm_get_field_map($collection)
{

    $url = get_option('cdmServerUrl');
    $url .= "/dmwebservices/index.php?q=dmGetDublinCoreFieldInfo/json";
    $dcFields = json_decode(file_get_contents($url),true);

    foreach($dcFields as $dcField) {
        $dcmap[$dcField['nick']]=$dcField['name'];
        $fieldmap[$dcField['nick']]=$dcField['name'];
    } 

    $url = get_option('cdmServerUrl');
    $url .= "/dmwebservices/index.php?q=dmGetCollectionFieldInfo";
    $url.= $collection;
    $url .= "/json";
    $fields = json_decode(file_get_contents($url),true);

    foreach($fields as $field){
        if ($field['dc'] === 'BLANK' || $field['dc']=='') 
            continue;
        $dc = $dcmap[$field['dc']];        
        $fieldmap[$field['nick']]=$dc;
    }
    
    return $fieldmap;

}


/*
IDEA: use dmGetCompoundObjectInfo to get compound info (if any) and put it in the structmap field
Signature
http://CdmServer.com:port/dmwebservices/index.php?q=dmGetCompoundObjectInfo/alias/pointer/format

 */

?>
