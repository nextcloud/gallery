<?php
/**
 * @var $_ array
 */
/**
 * @var $l OC_L10N
 */
script(
	$_['appName'],
	[
		'app',
		'vendor/jquery-touch-events/src/1.0.1/jquery.mobile-events.min',
		'vendor/jquery.ui.touch-punch-custom',
		'gallery',
		'galleryutility',
		'galleryconfig',
		'galleryinfobox',
		'galleryview',
		'breadcrumb',
		'galleryalbum',
		'galleryrow',
		'galleryimage',
		'thumbnail',
		'vendor/modified-eventsource-polyfill/eventsource-polyfill',
		'eventsource',
		'vendor/owncloud/share',
		'vendor/commonmark/dist/commonmark.min',
		'vendor/dompurify/src/purify',
		'vendor/bigshot/bigshot-compressed',
		'slideshow',
		'slideshowcontrols',
		'slideshowzoomablepreview',
		'upload-helper',
		'vendor/owncloud/newfilemenu'
	]
);
script(
	'files',
	[
		'upload',
		'file-upload',
		'jquery.fileupload',
	]
);
style(
	'files',
	[
		'upload'
	]
);
style(
	$_['appName'],
	[
		'styles',
		'share',
		'github-markdown',
		'slideshow',
		'gallerybutton',
		'upload',
		'mobile',
	]
);
?>
<div id="controls">
	<div id='breadcrumbs'></div>
	<div class="left">
		<!-- sorting buttons -->
		<div id="sort-date-button" class="button sorting right-switch-button">
			<div class="flipper">
				<img class="svg asc front" src="<?php print_unescaped(
					image_path($_['appName'], 'dateasc.svg')
				); ?>" alt="<?php p($l->t('Sort by date')); ?>"/>
				<img class="svg des back" src="<?php print_unescaped(
					image_path($_['appName'], 'datedes.svg')
				); ?>" alt="<?php p($l->t('Sort by date')); ?>"/>
			</div>
		</div>
		<div id="sort-name-button" class="button sorting left-switch-button">
			<div class="flipper">
				<img class="svg asc front" src="<?php print_unescaped(
					image_path($_['appName'], 'nameasc.svg')
				); ?>" alt="<?php p($l->t('Sort by name')); ?>"/>
				<img class="svg des back" src="<?php print_unescaped(
					image_path($_['appName'], 'namedes.svg')
				); ?>" alt="<?php p($l->t('Sort by name')); ?>"/>
			</div>
		</div>
	</div>
	<div class="actions creatable">
		<div id="uploadprogresswrapper">
			<div id="uploadprogressbar"></div>
			<button class="stop icon-close" style="display:none">
			<span class="hidden-visually">
				<?php p($l->t('Cancel upload')) ?>
			</span>
			</button>
		</div>
	</div>
	<div id="file_action_panel"></div>
	<span class="right">
		<!-- sharing button -->
		<div id="share-button" class="button">
			<img class="svg" src="<?php print_unescaped(
				image_path('core', 'actions/share.svg')
			); ?>" alt="<?php p($l->t("Share")); ?>"/>
		</div>
		<a class="share" data-item-type="folder" data-item=""
		   title="<?php p($l->t("Share")); ?>"
		   data-possible-permissions="31"></a>
		<!-- info button -->
		<div id="album-info-button" class="button">
			<span class="ribbon black"></span>
			<img class="svg" src="<?php print_unescaped(
				image_path('core', 'actions/info.svg')
			); ?>" alt="<?php p($l->t('Album information')); ?>"/>
		</div>
		<div class="album-info-container">
			<div class="album-info-loader"></div>
			<div class="album-info-content markdown-body"></div>
		</div>
		<!-- button for opening the current album as file list -->
		<div id="filelist-button" class="button view-switcher gallery">
			<div id="button-loading"></div>
			<img class="svg" src="<?php print_unescaped(
				image_path('core', 'actions/toggle-filelist.svg')
			); ?>" alt="<?php p($l->t('File list')); ?>"/>
		</div>
	</span>
</div>
<div id="gallery" data-allow-public-upload="<?php p($_['publicUploadEnabled'])?>" class="hascontrols"></div>
<div id="emptycontent" class="hidden"></div>
<input type="hidden" name="allowShareWithLink" id="allowShareWithLink" value="yes"/>
<input type="hidden" name="mailNotificationEnabled" id="mailNotificationEnabled"
	   value="<?php p($_['mailNotificationEnabled']) ?>"/>
<input type="hidden" name="mailPublicNotificationEnabled" id="mailPublicNotificationEnabled"
	   value="<?php p($_['mailPublicNotificationEnabled']) ?>"/>
<div class="hiddenuploadfield">
	<input type="file" id="file_upload_start" class="hiddenuploadfield" name="files[]"
		   data-url="<?php print_unescaped($_['uploadUrl']); ?>"/>
</div>
