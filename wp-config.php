<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '123456' );

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
define( 'AUTH_KEY',         'bkB!mHYY+k}0b`c$$^9]S]YvP!L)RQ,/iq-GVARibsj:jO_`H<RMlczE>lmmE1^S' );
define( 'SECURE_AUTH_KEY',  ',&5^RV]%$2hK&))hPbTzwe4M=GkWoj-5br4}J+o@~Op#Rbq 19GL%+O.PF.21md1' );
define( 'LOGGED_IN_KEY',    's!CbfX]gr@A$*XWj.ak!Uh7&wT]VZAFp7F7|uYL)Kk,zHi@PItmmIuSs5}KO+2j(' );
define( 'NONCE_KEY',        'tkB3rrCEQRweY&3JVz^e-W~?W./kzr9Wd50UNYq%oC/,M(MV4_z4BHBK+^nnn4Wu' );
define( 'AUTH_SALT',        '2AwA$J0QWU9N4v]3[D7 avF)ZXZc0%Jm;Am}s%:lPV~,Zfhq-3,k?#R5q%E+N<ld' );
define( 'SECURE_AUTH_SALT', 'XEnn}KN4,a m5rK{!n]%HBtNst}Y?pEJC>)UfxR|{7XQKLl_[T-AfDix`NqBcMYD' );
define( 'LOGGED_IN_SALT',   'gs9>=+<5qa@)){oYT{Xu;<f;9Q1q5{XA<2X0NK5)l5vSNp8&@&&y;Dr<(06_YA%`' );
define( 'NONCE_SALT',       ':ux+M(}02i?pj|Iy_<0(U(@,=+F?)bKov 7#$YqRX>;PMh]F7SXgn@=;~,#,?bhC' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
