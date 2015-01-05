<?php
/**
 * @type $_ array
 */
/**
 * @type $l OC_L10N
 */
script(
	$_['appName'],
	array(
		'album',
		'gallery',
		'thumbnail'
	)
);
style(
	$_['appName'],
	array(
		'styles',
		'mobile',
		'public'
	)
);

?>
<div class="wrapper"><!-- for sticky footer -->
	<header>
		<div id="header">
			<a href="<?php print_unescaped(link_to('', 'index.php')); ?>"
			   title="" id="owncloud">
				<div class="logo-wide svg">
					<h1 class="hidden-visually">
						<?php p($theme->getName()); ?>
					</h1>
				</div>
			</a>

			<div id="logo-claim" style="display:none;"><?php p(
					$theme->getLogoClaim()
				); ?></div>
			<div class="header-right">
				<span id="details">
					<span id="displayName">
					<?php p(
						$l->t('shared by %s', $_['displayName'])
					); ?>
					</span>
						<a id="download" class="button">
							<img class="svg" src="<?php print_unescaped(
								image_path($_['appName'], "download.svg")
							); ?>" alt=""/>
							<span id="download-text"><?php p(
									$l->t('Download')
								) ?>
							</span>
						</a>
				</span>
			</div>
		</div>
	</header>
	<div id="content" data-albumname="<?php p($_['albumName']) ?>">
		<div id="controls">
			<div id="breadcrumbs"></div>
			<!-- toggle for opening shared picture view as file list -->
			<div id="openAsFileListButton" class="button">
				<img class="svg"
					 src="<?php print_unescaped(
						 image_path('core', 'actions/toggle-filelist.svg')
					 ); ?>"
					 alt="<?php p($l->t('File list')); ?>"/>
			</div>
		</div>

		<div id='gallery' class="hascontrols"
			 data-requesttoken="<?php p($_['requesttoken']) ?>"
			 data-token="<?php isset($_['token']) ? p($_['token']) : p(
				 false
			 ) ?>"></div>
	</div>

	<div class="push"></div>
	<!-- for sticky footer -->
</div>

<footer>
	<p class="info">
		<?php print_unescaped($theme->getLongFooter()); ?>
	</p>
</footer>
