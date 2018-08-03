<?php
/**
 * @var $_ array
 */
/**
 * @var $l \OCP\IL10N
 */
script(
	$_['appName'],
	'merged'
);
style(
	$_['appName'],
	[
		'styles',
		'mobile',
		'public',
		'gallerybutton',
		'github-markdown',
		'slideshow',
		'gallerybutton'
	]
);
style('files_sharing', 'public');

?>
<div id="app-content" data-albumname="<?php p($_['albumName']) ?>">
	<div id="controls">
		<div id="breadcrumbs"></div>
		<!-- sorting buttons -->
		<div class="buttons">
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
			<!-- toggle for opening the current album as file list -->
			<div id="filelist-button" class="button view-switcher gallery">
				<div id="button-loading" class="hidden"></div>
				<img class="svg" src="<?php print_unescaped(
					image_path('core', 'actions/toggle-filelist.svg')
				); ?>" alt="<?php p($l->t('Picture view')); ?>"/>
			</div>
		</div>
	</div>
	<div id="gallery" class="hascontrols"
		 data-requesttoken="<?php p($_['requesttoken']) ?>"
		 data-token="<?php isset($_['token']) ? p($_['token']) : p(false) ?>">
	</div>
	<div id="emptycontent" class="hidden"></div>
</div>