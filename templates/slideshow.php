<div id="slideshow">
	<div class="icon-loading-dark"></div>
	<input type="button" class="svg next icon-view-next icon-shadow icon-white icon-32"/>
	<input type="button" class="svg play icon-play icon-shadow icon-white icon-32"/>
	<input type="button" class="svg pause icon-pause icon-shadow icon-white icon-32 hidden"/>
	<input type="button" class="svg previous icon-view-previous icon-shadow icon-white icon-32"/>
	<input type="button" class="menuItem svg exit icon-close icon-shadow icon-white icon-32"/>
	<div class="notification"></div>
	<div class="slideshow-menu">
		<input type="button"
			   class="menuItem svg downloadImage icon-download icon-shadow icon-white icon-32 hidden"/>
		<input type="button"
			   class="menuItem svg changeBackground icon-toggle-background icon-shadow icon-white icon-32 hidden"/>
		<input type="button"
			   class="menuItem svg deleteImage icon-delete icon-shadow icon-white icon-32 hidden"/>
		<input type="button"
			   class="menuItem svg shareImage icon-share icon-shadow icon-white icon-32 hidden"/>
		<a class="share" data-item-type="folder" data-item=""
		   title="<?php p($l->t("Share")); ?>"
		   data-possible-permissions="31">
		</a>
	</div>
	<div class="progress icon-pause icon-shadow icon-white icon-32"/>
	<div class="name">
		<div class="title"></div>
	</div>
	<div class="bigshotContainer"></div>
</div>
