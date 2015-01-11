<?php
/**
 * @type $_ array
 */
?>

<div id="app">
	<?php
	if (isset($_['code'])) {
		print_unescaped($this->inc('part.error'));
	} else {
		print_unescaped($this->inc('part.content'));
	}
	?>
</div>
