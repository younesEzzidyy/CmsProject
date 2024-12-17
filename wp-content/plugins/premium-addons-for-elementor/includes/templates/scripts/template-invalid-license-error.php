<?php
/**
 * Templates Loader Error
 */

use PremiumAddons\Includes\Templates;

?>
<div class="elementor-library-error">
	<div class="elementor-library-error-message">
	<?php
		echo wp_kses_post( __( 'You are using a nulled version of Premium Addons Pro. This makes your site vulnerable for attacks.', 'premium-addons-for-elementor' ) );
	?>
	</div>
	<div class="elementor-library-error-link">
	<?php
		printf(
			'<a class="template-library-activate-license" href="%1$s" target="_blank">%2$s</a>',
			esc_url( 'https://premiumaddons.com/pro' ),
			wp_kses_post( 'Get Premium Addons Pro' )
		);
		?>
	</div>
</div>
