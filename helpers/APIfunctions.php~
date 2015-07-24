<?php

function cdm_search($collection,$terms,$maxrecs = false) 
{
    $searchStrings = '';
/*    echo('<pre>');
    print_r($terms);
    echo('</pre>');
    die();*/
    $newterms = array();
    foreach($terms as $term) {
        if(count($strings = preg_split('/\s+/', $term['string'])) > 0) {
            foreach ($strings as $string) {
                $newterms[] = $term;
                end($newterms);
                $newterms[key($newterms)]['string'] = $string;
            }
        } else {
            $newterms[] = $term;
        }
    }
    foreach($newterms as $term) {
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
    $maxrecs = $maxrecs ? $maxrecs : get_option('cdmMaxRecs');
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
    if(!isset($response['records']))
        return false;
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

function cdm_get_collections($label="All Collections",$slug="/all") 
{
/*
http://CdmServer.com:port/dmwebservices/index.php?q=dmGetCollectionList/format
Example
https://server16019.contentdm.oclc.org/dmwebservices/index.php?q=dmGetCollectionList/json
 */
    $url = get_option('cdmServerUrl');
    $url .= "/dmwebservices/index.php?q=dmGetCollectionList/json";
    try{
        $collections = json_decode(file_get_contents($url),true);
    } catch (Exception $e) {
        return array('CONNECTION ERROR');
    } 
    $options  = array($slug=>$label);
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

function cdm_get_item_meta($collection,$pointer,$all=false,$fields = false,$fieldmap = false)
{
    $rawMeta = cdm_get_raw_item_meta($collection,$pointer);
    $fields = $fields ? $fields : cdm_get_fields($collection);
    $fieldmap = $fieldmap ? $fieldmap : cdm_get_field_map($collection);
    $meta = array();
    foreach($rawMeta as $field => $value){
        if ($field == 'master')
            $meta['filename'] = $value;
        if(!array_key_exists($field,$fieldmap)) {
            if($all) 
                $meta[$field]=$value;
            continue; 
        }
        $meta[$fieldmap[$field]][]=$value;
    }
    $meta['Transcript'][]= cdm_get_transcript($collection,$pointer);
    $meta['Relation'][]= cdm_get_public_url($collection,$rawMeta['find']);
    return $meta;
}

function cdm_get_raw_item_meta($collection,$pointer)
{
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=dmGetItemInfo'.$collection.'/'.$pointer.'/json';

    $rawMeta = json_decode(file_get_contents($url),true);
    return $rawMeta;
}

/**
 *  outputCSV creates a line of CSV and outputs it to browser    
 */
function outputCSV($array) {
    $fp = fopen('php://output', 'w'); // this file actual writes to php output
    fputcsv($fp, $array);
    fclose($fp);
}
 
/**
 *  getCSV creates a line of CSV and returns it. 
 */
function getCSV($array) {
    ob_start(); // buffer the output ...
    outputCSV($array);
    return ob_get_clean(); // ... then return it as a string!
}

function cdm_get_modified($collection,$pointer) {
    $url = get_option('cdmServerUrl');
    $url .= '/dmwebservices/index.php?q=GetItemDmmodified'.$collection.'/'.$pointer.'/json';
    $response = json_decode(file_get_contents($url),true);
    return $response[0];
}

function cdm_download_file($url,$filename) {
    $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
    set_time_limit(30);
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
    $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name."_pages.pdf";
    $filestring = "";
    $files = array();
    foreach($urls as $name=>$url) {
        if(!is_array(getimagesize($url)))
            continue;
        //download image file
        $files[] = cdm_download_file($url,$name);
        //append filename to command string
        $filestring .= " ".end($files);
    }
    if(empty($files)||count($files)<2)
        return false;

    $name = $name ? $name : 'cdm_'.rand();
    $command = "convert$filestring ".$filename;
    set_time_limit(30);
    //execute convert command to create pdf
    exec($command);

    //delete temp files
    foreach($files as $file)
        unlink($file);

    //return pdf filename
    return $filename;
}

function cdm_concat_pdfs($urls,$name=false) {
    $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name."_docs.pdf";
    $filestring = "";
    $files = array();
    foreach($urls as $name=>$url) {
        if(!strpos($name,'pdf'))
            continue;
        //download pdf
        $files[] = cdm_download_file($url,$name);
        //append filename to command string
        $filestring .= " ".end($files);
    }
    if(empty($files)||count($files)<2)
        return false;

    $name = $name ? $name : 'cdm_'.rand();
    $command = "convert$filestring ".$filename;
    set_time_limit(30);
    //execute convert command to create pdf
    exec($command);

    //delete temp files
    foreach($files as $file)
        unlink($file);

    //return pdf filename
    return $filename;
}

function cdm_get_nonimages($files) {
    $nonImages = array();
    foreach($files as $name=>$file) 
        if(!is_array(getimagesize($file)))
            $nonImages[$name] = $file;
    return $nonImages;
}

function cdm_get_nonpdfs($files) {
    $nonPdfs = array();
    foreach($files as $name=>$file) 
        if(!strpos($name,'pdf'))
            $nonPdfs[$name] = $file;
    return $nonPdfs;
}

function cdm_get_item_files($collection,$pointer,$filename = false)
{
//    $filename = $filename ? $filename : $pointer;
    $urls = array();

    $childPages = cdm_get_child_pages($collection,$pointer);
    if(empty($childPages)) {
        //this means it is a simple object        
        if(!$filename){
            $filename=$pointer;
            $mainUrl = cdm_get_file_url($collection,$pointer,$filename);
            $headers = get_headers($mainUrl);
            foreach($headers as $header) {
                if(strpos($header,'Content')===0){
                    if(strpos($header,'application/pdf'))
                        $filename.='.pdf';
                    if(strpos($header,'image/jpeg'))
                    $filename.='.jpg';
                }
            }
        }
        $mainUrl = cdm_get_file_url($collection,$pointer,$filename);
        $urls[$filename] = $mainUrl;
    }
    foreach($childPages as $childPage) {
        $filename = str_replace('tiff','jpg',$childPage['filename']);                
        $filename = str_replace('tif','jpg',$filename);
        $filename = str_replace('jp2','jpg',$filename);
        $filename = str_replace('png','jpg',$filename);
        $filename = str_replace('gif','jpg',$filename);
        $filename = str_replace('bmp','jpg',$filename);
        $urls[$filename] = cdm_get_file_url($collection,$childPage['pointer'],$childPage['filename']);
    }
    return $urls;
}

function cdm_insert_item_files($item,$collection,$pointer,$filename) {
    //add the new files
    $urls = cdm_get_item_files($collection,$pointer,$filename);
    $paths = array();

    if(get_option('cdmConcatImages')) {
        $pdf = cdm_concat_images($urls,$pointer);
        if($pdf) {
            $imagePdfPath = $pdf;
            $urls = cdm_get_nonimages($urls);
        }
    }

    if(get_option('cdmConcatPdfs')) {
        $pdf = cdm_concat_pdfs($urls,$pointer);
        if($pdf){
            $pdfPath = $pdf;
            $urls = cdm_get_nonpdfs($urls);
        }
    }
    
    foreach ($urls as $filename=>$url) 
        $paths[] = cdm_download_file($url,$filename);

    if(isset($pdfPath))
        $paths[] = $pdfPath;

    if(isset($imagePdfPath))
        array_unshift($paths,$imagePdfPath);

    insert_files_for_item($item,'Filesystem',$paths,array('ignore_invalid_files'=>true));

    foreach($paths as $path)
        unlink($path);
}

function cdm_get_file_url($collection,$pointer,$filename=false) {

    $url = get_option('cdmServerUrl');
 //    $url .= "/dmwebservices/index.php?q=dmGetImageInfo".$collection."/".$pointer.'/json';
 //JSON version of this API command is mysteriously broken. XML version works fine.
    $url .= "/dmwebservices/index.php?q=dmGetImageInfo".$collection."/".$pointer.'/xml';
    $response = file_get_contents($url);
    
    $p = xml_parser_create();
    xml_parse_into_struct($p, $response, $vals, $index);
    xml_parser_free($p);

    if(isset($index['IMAGEINFO'])) {
        $width = $vals[$index['WIDTH'][0]]['value'];
        $height = $vals[$index['HEIGHT'][0]]['value'];
        $maxWidth = get_option('cdmMaxWidth');
        if($width > $maxWidth || strpos($vals[$index['FILENAME'][0]]['value'],'jp2')) {
            if(get_option('cdmLimitImageSize'))
                $scale = $width > $maxWidth ? floor($maxWidth/$width) : 1;
            $height = $scale * $height;
            $width = $scale * $width;
            $url = get_option('cdmWebsiteUrl');
            $url .= '/utils/ajaxhelper/?CISOROOT='.$collection.'&CISOPTR='.$pointer.'&action=2&DMX=0&DMY=0&DMWIDTH='.floor($width).'&DMSCALE='.floor($scale*100).'&DMHEIGHT='.floor($height);
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
        if($eSet->name=='Item Type Metadata' && $element->name=='Text')
            $eText->delete();
    }

    //delete all files
    $files = $item->getFiles();
    foreach($files as $file) {
        $file->delete();
    }
    
    //add all the new files and meta to the item
    cdm_add_meta_and_files($item,$collection,$pointer);
}

function cdm_add_meta_and_files($item,$collection,$pointer) {
    $filename=false;
    $meta = cdm_get_item_meta($collection,$pointer);
    foreach($meta as $field=>$values) {
        $field = strpos($field,'-') ? substr($field,0,strpos($field,'-')) : $field ;
        $elementTable = get_db()->getTable('Element');
        if($field==='Transcript'){
            $elementSet = 'Item Type Metadata';
            $field = 'Text';
            $documentType = get_db()->getTable('ItemType')->findByName('Document');
            $item->item_type_id = $documentType->id;
            $item->save();

        }else {
            $elementSet = 'Dublin Core';
        }
        $element = $elementTable->findbyElementSetNameAndElementName($elementSet,$field);
        if($field === 'filename' && !empty($values)){
            $filename = is_array($values) ? $values[0] : $values;
        }
        $values = is_array($values) ? $values : array();
        foreach($values as $value){
            if(empty($value))
                continue;
            $eText = new ElementText();
            $eText->element_id = $element->id;
            $eText->record_id = $item->id;
            $eText->record_type = 'Item';
            $eText->text = $value;
            $eText->html = 0;
            $eText->save();
        }
    }
    
    if($filename){
        $filename = str_replace('tiff','jpg',$filename);
        $filename = str_replace('tif','jpg',$filename);
        $filename = str_replace('jp2','jpg',$filename);
        $filename = str_replace('png','jpg',$filename);
        $filename = str_replace('gif','jpg',$filename);
        $filename = str_replace('bmp','jpg',$filename);
            }
    cdm_insert_item_files($item,$collection,$pointer,$filename);
}

function cdm_get_fields($collection)
{
    if($collection=='all')
        return false;
    $url = get_option('cdmServerUrl');
//    $url .= "/dmwebservices/index.php?q=dmGetCollectionFieldInfo/";
    $url .= "/dmwebservices/index.php?q=dmGetCollectionFieldInfo";
    $url.= $collection;
    $url .= "/json";
    $fields = json_decode(file_get_contents($url),true);
    $rv = array();
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
