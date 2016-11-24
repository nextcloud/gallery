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
		<p><?php p($l->t("Sorry, this link doesn't seem to work anymore.")) ?></p>

		<p><?php p($l->t('Reasons might be:')); ?></p>
		<ul>
			<li><?php p($l->t('the item was removed')); ?></li>
			<li><?php p($l->t('the link has expired')); ?></li>
			<li><?php p($l->t('sharing is disabled')); ?></li>
		</ul>
		<?php if (isset($_['message'])): ?>
		</br>
		<p><?php p($l->t('Here is the error message returned by the server: ')); ?>
			<strong><?php p($_['message'] . ' (' . $_['code'] . ')'); ?></strong></p>
		<?php endif; ?>
		</br>
		<p><?php p(
				$l->t('For more information, please ask the person who has sent you this link.')
			); ?></p>
	</li>
</ul>
