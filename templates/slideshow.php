<div id="slideshow">
	<div class="icon-loading-dark"></div>
	<input type="button" class="svg next icon-view-next"/>
	<input type="button" class="svg play icon-view-play"/>
	<input type="button" class="svg pause icon-view-pause hidden"/>
	<input type="button" class="svg previous icon-view-previous"/>
	<input type="button" class="svg exit icon-view-close"/>
	<div class="notification"></div>
	<div class="slideshow-menu">
		<input type="button"
			   class="menuItem svg downloadImage icon-view-download hidden"/>
		<input type="button"
			   class="menuItem svg changeBackground icon-view-toggle-background hidden"/>
		<input type="button"
			   class="menuItem svg deleteImage icon-view-delete hidden">
		<div id="slideshow-shared-button" class="menuItem button">
			<img class="svg" src="<?php print_unescaped(
				image_path('gallery', 'share-white.svg')
			); ?>" alt="<?php p($l->t("Share")); ?>"/>
		</div>
		<a class="share" data-item-type="folder" data-item=""
		   title="<?php p($l->t("Share")); ?>"
		   data-possible-permissions="31">
		</a>
	</div>
	<div class="progress icon-view-pause"/>
	<div class="name">
		<div class="title"></div>
	</div>
	<div class="bigshotContainer"></div>
</div>
