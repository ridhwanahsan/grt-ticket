<?php
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
define( 'DB_NAME', 'kindi' );

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
define( 'AUTH_KEY',         'yc.YiG@40r]8h!H)43J6U;jLhb~J2eFM$2.V0W,UL)1#FWTz1$=f&fj~K&i7PSj<' );
define( 'SECURE_AUTH_KEY',  '_7no0jlds/$MX9|Bz//EZ_6Vd,;3`NBmW]j[G+f1,omn56EA^8YK73{7n(2:0tHk' );
define( 'LOGGED_IN_KEY',    'uT~SmI3xsMgq/VSdR(eK$,sbl?z&T_! 3tO^Y7##,WwMw}^eGAh6I Ctri7IZ{Se' );
define( 'NONCE_KEY',        'ea0$I2f}e%M%g<FCPl= Z>Q^@d`e=lKv+g9Xj%IUv~},DgfV`2L&nQUG2Yb!J$JO' );
define( 'AUTH_SALT',        'YZt#1cgG_u*pEYr[L1i<3TUugc%L9>91RmC+dHiv;OaJO1:gh&0>d7?2.0aGlhbr' );
define( 'SECURE_AUTH_SALT', 'D#Vagga,8!H-X8g;r.DX&mB!aFlE#smz<]6oJC^Ye%G7a3$(P$b#2}DNsyk=Jc,f' );
define( 'LOGGED_IN_SALT',   'U:xuu@AygO%IQ9bO;-n7D9}N;*UecS-<!F0K!:144WZ#(@{OwTzu,Ztsp(mBHp-e' );
define( 'NONCE_SALT',       '7QX&GQaUGfR%$XMsdSqp%eB1>] ,#EsZ8{H*(wSRoD?DwTlo<n9V$yG]J~fs=B^K' );

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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', false);
define( 'SCRIPT_DEBUG', false
 );

/* Add any custom values between this line and the "stop editing" line. */



define( 'SURECART_ENCRYPTION_KEY', 'uT~SmI3xsMgq/VSdR(eK$,sbl?z&T_! 3tO^Y7##,WwMw}^eGAh6I Ctri7IZ{Se' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
