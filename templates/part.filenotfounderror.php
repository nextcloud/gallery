<?php
/**
 * @var $_ array
 */
/**
 * @var $l \OCP\IL10N
 */
style(
	$_['appName'],
	[
		'error'
	]
);
?>
<ul>
	<li class="error error-broken-link">
		<p><?php p($l->t("Sorry, this file could not be found.")) ?></p>

		<p><?php p($l->t('Reasons might be:')); ?></p>
		<ul>
			<li><?php p($l->t('the wrong file ID was provided')); ?></li>
			<li><?php p($l->t('the file was removed')); ?></li>
			<li><?php p($l->t('the file is corrupt')); ?></li>
			<li><?php p($l->t('the encryption key is missing')); ?></li>
		</ul>
		<?php if (isset($_['message'])): ?>
		</br>
		<p><?php p($l->t('Here is the error message returned by the server: ')); ?>
			<strong><?php p($_['message'] . ' (' . $_['code'] . ')'); ?></strong></p>
		<?php endif; ?>
		</br>
		<p><?php p(
				$l->t('For more information, please contact your friendly Nextcloud administrator.')
			); ?></p>
	</li>
</ul>
