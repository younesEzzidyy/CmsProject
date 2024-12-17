<?php
define('WP_CACHE', true);

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ' pp4zg@DH8gJ]H- 3mnSF5J(})MIV ~8B+1|f1vBFaUhIJx,yScvOp)26o6G,u=|' );
define( 'SECURE_AUTH_KEY',  'B<A(1V$nV|Sr.QcGz+#HO*0F?jSaV_)*;Mj<J>I}f&y$aBd4tE7(zsBbgd]#!e@}' );
define( 'LOGGED_IN_KEY',    '8AL?r#qW4%yns.Zt3@KpT3DJ7zd<sSczhWZE+^|}>yq; oF$fkT3&l7CSp}ok%X^' );
define( 'NONCE_KEY',        ');PI:,Bp +[fzP@68HFR1jaB]_R,7mJplwu0(Ob]]`o:GD4yuC-.7jV9YQJP(}Zn' );
define( 'AUTH_SALT',        '4=n]E*hL{fsw&R.DMX+/Y4`M(g58=Z7g2qW,Pwa2a?93hJxs7c  uiyVFmwOhp.>' );
define( 'SECURE_AUTH_SALT', 'x$t~{avv)^a/Q|.?p[Mpxt}e]6NX~<2`O!97T~sEdr2/712I|J&mL>ekqL<=e7T<' );
define( 'LOGGED_IN_SALT',   ',!(y.@;5Ltmeih3<K,yj~!9dRBWx f;_/P9dK_)zt_`dh[s~ K/E0%9C{9MP Uq<' );
define( 'NONCE_SALT',       'l-_`XwurWf[~r0 $4?MD/5I<*C[dn{-y&p=D1tl)v~u|}kQyNx%dAdq(uJ*h`%#9' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
