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
 * * Mail Settings
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

/*
 * Set the following constants in wp-config.php
 * These should be added somewhere BEFORE the
 * constant ABSPATH is defined.
 */

define( 'SMTP_USER',    'noreply@fanfiction.online' );    // Username to use for SMTP authentication
define( 'SMTP_PASS',    'G{Pc]2=5#O=$' );       // Password to use for SMTP authentication
define( 'SMTP_HOST',    'fanfiction.online' );    // The hostname of the mail server
define( 'SMTP_FROM',    SMTP_USER ); // SMTP From email address
define( 'SMTP_NAME',    'Fanfiction Online' );    // SMTP From name
define( 'SMTP_PORT',    587 );                  // SMTP port number - likely to be 25, 465 or 587
define( 'SMTP_SECURE',  'tls' );                 // Encryption system to use - ssl or tls
define( 'SMTP_AUTH',    true );                 // Use SMTP authentication (true|false)
define( 'SMTP_DEBUG',   0 );                    // for debugging purposes only set to 1 or 2

//define( 'WPMS_ON', true );
//define( 'WPMS_SMTP_PASS', SMTP_PASS );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'readadmt_ffonline' );

/** MySQL database username */
define( 'DB_USER', 'readadmt_ffonline' );

/** MySQL database password */
define( 'DB_PASSWORD', '1+6NaRU)yiuo' );

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
define( 'AUTH_KEY',         '4}xYM>H.ET@!ERhQAE)J<Jy`#9FwMcI/HX2)vfJuR@k?|L+Xt{1f*p(Q&7x-BH0:' );
define( 'SECURE_AUTH_KEY',  'LDv[t5,Duvi&x]HEYL{JOaurc$$mCvC[b?IIq:B^;e1BIuQusit$<$`T<^s5^[[g' );
define( 'LOGGED_IN_KEY',    'E%Ak,f~U}88uQL.*w)__dRocPF!(sz8=m;U?Xm2A[Yf_>hyb;{Hic+:GZdR$Mgbu' );
define( 'NONCE_KEY',        '[Y1%|5q]0p2e3Wi$V|BmFv$Nn-5#)mBs2e$*lWI8ipg1Y!eh:arx!Og>F*48u96T' );
define( 'AUTH_SALT',        '$)/Z@ 4B(d9N?yku]3L/:qI/IT?01w3rqD36iJjH1s/l0%Iu]Y8dQD%P~NsHd~V~' );
define( 'SECURE_AUTH_SALT', '4+NY!O)v14@YYB8X+o8H$k|85qCG<X8-lJM8@fZZfDFy_X4 L. %YEGk|-+W_%E,' );
define( 'LOGGED_IN_SALT',   ',a(>Sr0FfU>u1V78gKY&h,hqyjz#w&>@w-8Gxka^M@~6sIL/sMZo&r!ZL8b><cHd' );
define( 'NONCE_SALT',       'Yicn4^ijieCdwA?d>5QA=a0&%&l8R,F5!5x*w0:6o`mGtaz6sv%DO`Xz@$QIyHKI' );

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';