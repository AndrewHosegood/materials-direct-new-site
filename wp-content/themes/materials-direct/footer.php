<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Materials_Direct
 */

?>

	<footer class="footer">
		<div class="container footer__container">
			<div class="footer__col">
				<img src="/wp-content/uploads/2025/09/materials-direct-logo-white.svg" alt="Materials-direct" class="footer__logo">
				<p class="footer__strapline">We supply custom cut technical materials – FAST!</p>
				<p class="footer__content">Let us assist you on your next project!</p>
				<p class="footer__content">A division of Universal Science UK</p>
				<a class="footer__link" href="https://universal-science.com">www.universal-science.com</a>
				<div class="footer__logos">
					<img src="/wp-content/uploads/2025/09/pay-options.svg" alt="Pay Options" class="footer__pay-options-logo">
					<img src="/wp-content/uploads/2025/09/NQA_ISO9001_BW_UKAS.svg" alt="ISO9001" class="footer__iso-logo">
					<img src="/wp-content/uploads/2025/09/cyber-essentials-logo.png" alt="Cyber Essentials" class="footer__cyber-logo">
				</div>
			</div>
			<div class="footer__col">
				<h4 class="footer__subheading">Manufacturing Services</h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'manufacturing-services',
						'menu_id'        => 'manufacturing-services',
						'container'      => false,
						'menu_class'     => 'footer__menu',
					)
				);
				?>
				<h4 class="footer__subheading">Service Sectors</h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'service-sectors',
						'menu_id'        => 'service-sectors',
						'container'      => false,
						'menu_class'     => 'footer__menu',
					)
				);
				?>
			</div>
			<div class="footer__col">
				<h4 class="footer__subheading">Useful Links</h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'useful-links',
						'menu_id'        => 'useful-links',
						'container'      => false,
						'menu_class'     => 'footer__menu',
					)
				);
				?>
				<h4 class="footer__subheading">Page Navigation</h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'page-navigation',
						'menu_id'        => 'page-navigation',
						'container'      => false,
						'menu_class'     => 'footer__menu',
					)
				);
				?>
			</div>
			<div class="footer__col">
				<h4 class="footer__subheading">Mailing List</h4>
				<?php echo do_shortcode('[contact-form-7 id="6aaccac" title="Contact Form Footer"]'); ?>
				
			</div>
		</div>

		

		<div class="container">
			<hr class="footer__underline">
			<h4 class="footer__subheading">Reviews</h4>
			<?php echo do_shortcode( '[grw id=149]' ); ?>
		</div>

		<div class="footer__baseline">
			<div class="container footer__baseline-container">
				<p class="footer__copyright">© Materials Direct. All Rights Reserved.</p>
				<span class="footer__baseline-right">
					<a href="#"><i class="fa-brands fa-facebook-f footer__baseline-icon"></i></a>
					<a href="#"><i class="fa-brands fa-twitter footer__baseline-icon"></i></a>
					<a href="#"><i class="fa-solid fa-play footer__baseline-icon"></i></a>
					<a href="#"><i class="fa-brands fa-linkedin-in footer__baseline-icon"></i></a>

					<a id="back_to_top" class="footer__button" href=""><i class="fa-solid fa-chevron-up"></i></a>
				</span>
			</div>
		</div>

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
