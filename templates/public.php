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
		'gallery',
		'galleryutility',
		'galleryconfig',
		'galleryinfobox',
		'galleryview',
		'breadcrumb',
		'album',
		'thumbnail',
		'vendor/eventsource-polyfill/dist/eventsource.min',
		'eventsource',
		'vendor/marked/marked.min',
		'vendor/bigshot/bigshot',
		'slideshow',
		'slideshowcontrols',
		'slideshowzoomablepreview'
	]
);
style(
	$_['appName'],
	[
		'styles',
		'mobile',
		'public',
		'gallerybutton',
		'github-markdown',
		'slideshow'
	]
);

?>
<div id="notification-container">
	<div id="notification" style="display: none;"></div>
</div>
<header>
	<div id="header">
		<a href="<?php print_unescaped(link_to('', 'index.php')); ?>" title="" id="owncloud">
			<div class="logo-wide svg">
				<h1 class="hidden-visually"><?php p($theme->getName()); ?></h1>
			</div>
		</a>

		<div id="logo-claim" style="display:none;"><?php p($theme->getLogoClaim()); ?></div>
		<div class="header-right">
			<span id="details">
				<?php
				if ($_['server2ServerSharing']) {
					?>
					<span id="save" data-protected="<?php p($_['protected']) ?>"
						  data-owner="<?php p($_['displayName']) ?>"
						  data-name="<?php p($_['filename']) ?>">
									<button id="save-button"><?php p(
											$l->t('Add to your ownCloud')
										) ?></button>
									<form class="save-form hidden" action="#">
										<input type="text" id="remote_address"
											   placeholder="example.com/owncloud"/>
										<button id="save-button-confirm"
												class="icon-confirm svg"></button>
									</form>
								</span>
				<?php } ?>
				<a id="download" class="button">
					<img class="svg" src="<?php print_unescaped(
						image_path($_['appName'], "download.svg")
					); ?>" alt=""/>
						<span id="download-text"><?php p($l->t('Download')) ?>
						</span>
				</a>
			</span>
		</div>
	</div>
</header>
<div class="content-wrapper">
	<div id="content" class="app-<?php p($_['appName']) ?>"
		 data-albumname="<?php p($_['albumName']) ?>">
		<div id="app">
			<div id="controls">
				<div id="breadcrumbs"></div>
				<!-- toggle for opening shared picture view as file list -->
				<div id="openAsFileListButton" class="button">
					<img class="svg" src="<?php print_unescaped(
						image_path('core', 'actions/toggle-filelist.svg')
					); ?>" alt="<?php p($l->t('File list')); ?>"/>
				</div>
				<div id="sort-name-button" class="button left-sort-button">
					<img class="svg" src="<?php print_unescaped(
						image_path($_['appName'], 'nameasc.svg')
					); ?>" alt="<?php p($l->t('Sort by name')); ?>"/>
				</div>
				<div id="sort-date-button" class="button right-sort-button">
					<img class="svg" src="<?php print_unescaped(
						image_path($_['appName'], 'dateasc.svg')
					); ?>" alt="<?php p($l->t('Sort by date')); ?>"/>
				</div>
				<span class="right">
					<div id="album-info-button" class="button">
						<span class="ribbon black"></span>
						<img class="svg" src="<?php print_unescaped(
							image_path('core', 'actions/info.svg')
						); ?>" alt="<?php p($l->t('Album information')); ?>"/>
					</div>
					<div class="album-info-content markdown-body"></div>
				</span>
			</div>
			<div id="gallery" class="hascontrols"
				 data-requesttoken="<?php p($_['requesttoken']) ?>"
				 data-token="<?php isset($_['token']) ? p($_['token']) : p(false) ?>">
			</div>
			<div id="emptycontent" class="hidden"><?php p(
					$l->t(
						"No pictures found! If you upload pictures in the files app, they will be displayed here."
					)
				); ?>
			</div>
		</div>
	</div>
</div>
<footer>
	<p class="info"><?php print_unescaped($theme->getLongFooter()); ?></p>
</footer>
