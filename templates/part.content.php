<?php
/**
 * @type $_ array
 */
/**
 * @type $l OC_L10N
 */
script(
	$_['appName'],
	[
		'album',
		'gallery',
		'thumbnail',
	]
);
style(
	$_['appName'],
	[
		'styles',
		'mobile',
		'gallerybutton'
	]
);
?>
<div id="controls">
	<div id='breadcrumbs'></div>
	<!-- toggle for opening shared picture view as file list -->
	<div id="openAsFileListButton" class="button">
		<img class="svg" src="<?php print_unescaped(
			image_path('core', 'actions/toggle-filelist.svg')
		); ?>" alt="<?php p($l->t('File list')); ?>"/>
	</div>
	<span class="right">
		<button class="share"><?php p($l->t("Share")); ?></button>
		<a class="share" data-item-type="folder" data-item=""
		   title="<?php p($l->t("Share")); ?>"
		   data-possible-permissions="31"></a>
	</span>
</div>
<div id="gallery" class="hascontrols"></div>
<div id="emptycontent" class="hidden"><?php p(
		$l->t(
			"No pictures found! If you upload pictures in the files app, they will be displayed here."
		)
	); ?></div>
<input type="hidden" name="allowShareWithLink" id="allowShareWithLink" value="yes"/>
