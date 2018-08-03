<?php
/**
 * @var $_ array
 */
?>

<div id="app-content">
	<?php
	if (isset($_['code'])) {
		if ($_['code'] === 404) {
			print_unescaped($this->inc('part.filenotfounderror'));
		} elseif ($_['code'] === 500) {
			print_unescaped($this->inc('part.internalservererror'));
		} else {
			print_unescaped($this->inc('part.linkerror'));
		}
	} else {
		print_unescaped($this->inc('part.content'));
	}
	?>
</div>
