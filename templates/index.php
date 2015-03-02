<?php
/**
 * @type $_ array
 */
?>

<div id="app">
	<?php
	if (isset($_['code'])) {
		if ($_['code'] === 404) {
			print_unescaped($this->inc('part.filenotfounderror'));
		} else {
			print_unescaped($this->inc('part.linkerror'));
		}
	} else {
		print_unescaped($this->inc('part.content'));
	}
	?>
</div>
