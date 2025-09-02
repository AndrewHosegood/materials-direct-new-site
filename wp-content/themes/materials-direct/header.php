<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Materials_Direct
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'materials-direct' ); ?></a>

	<header id="masthead" class="header">
		<div class="header__top">
			<ul class="header__contact-details">
					<li class="header__phone"><i class="header__icon-phone"></i><a href="tel:+44(0)1908222211">+44 (0)1908 222 211</a></li>
					<li class="header__mail"><i class="header__icon-mail-line"></i><a href="mailto:info@materials-direct.com"> info@materials-direct.com</a></li>				
			</ul>
		</div>
		<div class="site-branding header__left">
			<?php the_custom_logo(); ?>
		</div>
		<div class="header__right">
			<nav id="site-navigation" class="main-navigation">
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'materials-direct' ); ?></button>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
					)
				);
				?>
			</nav>
			<?php echo do_shortcode('[ivory-search id="112" title="Custom Search Form"]'); ?>
		</div>
	</header><!-- #masthead -->
