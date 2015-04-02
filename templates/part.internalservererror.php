<?php
/**
 * @var $_ array
 */
/**
 * @var $l OC_L10N
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
		<p><?php p($l->t("Sorry, but the server could not fulfill your request.")) ?></p>

		<p><?php p($l->t('Possible reasons for the problem:')); ?></p>
		<ul>
			<li><?php p($l->t('a conflicting app was installed')); ?></li>
			<li><?php p($l->t('a required component is missing or was disconnected')); ?></li>
			<li><?php p($l->t('the filesystem is not readable')); ?></li>
		</ul>
		</br>
		<p><?php p($l->t('Here is the error message returned by the server: ')); ?>
			<strong><?php p($_['message'] . ' (' . $_['code'] . ')'); ?></strong></p>
		</br>
		<p><?php p($l->t('For more information, please contact your friendly ownCloud administrator.')); ?></p>
	</li>
</ul>
