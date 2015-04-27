
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
    <div id="cdm-avoid-tifs-label" class="two columns alpha">
        <label for="cdm-avoid-tifs-label"><?php echo __('Limit image size'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmAvoidTifs',get_option('cdmAvoidTifs'),array()); ?>
        <p class="explanation"><?php echo __('Limit the size of imported high resolution images'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-create-pdf-label" class="two columns alpha">
        <label for="cdm-create-pdf-label"><?php echo __('Create PDFs'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmCreatePDFs',get_option('cdmCreatePDFs'),array()); ?>
        <p class="explanation"><?php echo __('For compound objects with one image per page,'.
        ' merge the pages into a single pdf file'); ?></p>
    </div>
</div>

<div class="field">
    <div id="cdm-auto-sync-label" class="two columns alpha">
        <label for="cdm-auto-sync-label"><?php echo __('Autosync'); ?></label>
    </div>
    <div class="inputs five columns omega">
<?php echo get_view()->formCheckbox('cdmAutoSync',get_option('cdmAutoSync'),array()); ?>
        <p class="explanation"><?php echo __('Update imported omeka items when origin item in Content DM changes. (Changes to imported items made manually in the Omeka dashboard may be overwritten if autosync is enabled.'); ?></p>
    </div>
</div>
