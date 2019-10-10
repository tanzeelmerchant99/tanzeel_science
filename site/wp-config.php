<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '5P9rV8HkVv' );

/** MySQL database username */
define( 'DB_USER', '5P9rV8HkVv' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wVmFsQRFg4' );

/** MySQL hostname */
define( 'DB_HOST', 'remotemysql.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '}]cCUN=j nX>-k/X4]a{N}T.Ukb-%|1QcMVv<5G/3J<eWN_H&/C-R-5=,ad|/LRY' );
define( 'SECURE_AUTH_KEY',  '@E:g Yo}o>#;Lu#&@yloC5P,+?-Dik/2KemnBkJy-a3xBu<L3rT)[:Ans-%_rTLn' );
define( 'LOGGED_IN_KEY',    '!;&EE%0)&YHm_IYLQK$NTN$^*~db%lv`~4M Lx{QcJUMRO=mt&lzE&)HW|&s!TNr' );
define( 'NONCE_KEY',        'c[Y$HKrajK&qkxN:LO#wc7+z9v C+gg4+<QVt h#QfxUO2TMgW<j$O4Q>VN|&AUV' );
define( 'AUTH_SALT',        'Hl1>s3+*{Ij{?wI)O2w`XdSFuy-YLx$zsV4TbKEbO^ERn~G)k3nY5#o:}>jb+PxR' );
define( 'SECURE_AUTH_SALT', '_6)X)R*H1rJd+tKx:C#,98x{?RmejVH*~Um F#;~ #o4%k$~e4&N6Gf8c/|_6d1>' );
define( 'LOGGED_IN_SALT',   '~#$8AmHGU7$ZhHcPVK:C({rjBnamiFT/E$F8MjXvoHXYCS_>OgRZIcFB^zL[Kdj3' );
define( 'NONCE_SALT',       '%47`>VD09lQ&^Rt<bWm@c@Y4wRJm9T>* 7I22j21 Ts]CH.d/go$<FwY}|7`YTp@' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
