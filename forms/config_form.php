
<div class="field">
    <div id="cdm-server-url-label" class="two columns alpha">
        <label for="cdm-server-url"><?php echo __('ContentDM server url'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formText('cdmServerUrl',get_option('cdmServerUrl'),array()); ?>
        <p class="explanation"><?php echo __('Enter the URL of your Content DM Server'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-website-url-label" class="two columns alpha">
        <label for="cdm-website-url-label"><?php echo __('ContentDM website url'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formText('cdmWebsiteUrl',get_option('cdmWebsiteUrl'),array()); ?>
        <p class="explanation"><?php echo __('Enter the URL of your Content DM website'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-max-recs-label" class="two columns alpha">
        <label for="cdm-max-recs-label"><?php echo __('Max records'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formText('cdmMaxRecs',get_option('cdmMaxRecs'),array()); ?>
        <p class="explanation"><?php echo __('Enter the maximum number or results to return on search functions'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-limit-image-size-label" class="two columns alpha">
        <label for="cdm-limit-image-size-label"><?php echo __('Limit image size'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmLimitImageSize',true,array('checked'=>get_option('cdmLimitImageSize'))); ?>
        <p class="explanation"><?php echo __('Limit the size of imported high resolution images'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-concat-images-label" class="two columns alpha">
        <label for="cdm-concat-images-label"><?php echo __('Merge Images to PDF'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmConcatImages',true,array('checked'=>get_option('cdmConcatImages'))); ?>
        <p class="explanation"><?php echo __('For compound objects with one image per page,'.
        ' merge the pages into a single pdf file'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-concat-pdfs-label" class="two columns alpha">
        <label for="cdm-concat-pdfs-label"><?php echo __('Merge PDFs'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmConcatPdfs',true,array('checked'=>get_option('cdmConcatPdfs'))); ?>
        <p class="explanation"><?php echo __('For compound objects with multiple pdf attachments, merge pdf attachments into a single pdf file.'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-auto-sync-label" class="two columns alpha">
        <label for="cdm-auto-sync-label"><?php echo __('Autosync'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmAutoSync',true,array('checked'=>get_option('cdmAutoSync'))); ?>
        <p class="explanation"><?php echo __('Update imported omeka items when origin item in Content DM changes. (Changes to imported items made manually in the Omeka dashboard may be overwritten if autosync is enabled.'); ?></p>
    </div>
</div>
