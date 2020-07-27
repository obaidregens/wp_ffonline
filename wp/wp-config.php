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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ffonline' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'K$*0pj~!(a}}~K|khuav`#Sj2e}?GO)mM, xVM1SxQgKY8lnYd?Pt&E=g:pv-B*A' );
define( 'SECURE_AUTH_KEY',  'HlKZRJ)OuRcNsRhjm?c*>9~V.jG&C3JdUSpU3JudeatKg=g,d*Ef/:10WO_c6X;K' );
define( 'LOGGED_IN_KEY',    'lUJ5g5jy{ZZ@FmCC9jEIbZ1sn[jMZ]a>B L9wH)_(A}J4f9j7^o?w|~L0)cJ!;SC' );
define( 'NONCE_KEY',        'xw}^w;YXQ#=nJE`Q@,my`diMqjUf7d[%UYr7-xPI# ZY_yG=@Hp>c!sKzV5)C7,E' );
define( 'AUTH_SALT',        'b7@#bD[Sj:]s]VTqghP3Q=}&8xsp)*x@;ZE`$iTHcSmZ,r%9T;AhZ8>VOGkNc6h@' );
define( 'SECURE_AUTH_SALT', 'V|%,?hg6Ris(y1hz(ik>@fGfCc ?~6oiy^bb@r6ip>[CusYvU:u[_yP;p%ulY5mT' );
define( 'LOGGED_IN_SALT',   'U*Gm04W!J5sdt6wPV{+j]OPiMGgs:.[0hz7|Hh,A^x{^~Iki)w`dEU[f*=xAnq:Q' );
define( 'NONCE_SALT',       'EI36(+ci:<|yfMxBV<Q</;qsdgQ9^Ja?z;K)Fy;=z<%n!r6b:%c;tIiKLb~[+U<F' );

/**
 * Define Archiver path relative to site root
 */
define('ARCHIVER_PATH', 'archiver');
/**
 * Define Wordpress cookie prefixes & names
 */
define( 'COOKIEHASH',           md5( 'https://fanfiction.online' ) 	); 

define( 'USER_COOKIE',          'user_'      . COOKIEHASH );
define( 'PASS_COOKIE',          'password_'      . COOKIEHASH );
define( 'AUTH_COOKIE',          'm_'           . COOKIEHASH );
define( 'SECURE_AUTH_COOKIE',   'sec_'       . COOKIEHASH );
define( 'LOGGED_IN_COOKIE',     'login_' . COOKIEHASH );
define( 'TEST_COOKIE',          'test_cookie'             );

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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true  );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
