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
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'mack');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'l,Ijj5{9!rJ9x$U<BNW6vm<<7`xaR2!10po)O<4K;TM62ejIRk{`1<Bv|bR`4!nL');
define('SECURE_AUTH_KEY',  '(^T/0,tx~N|jJf&3([4_RB/5b!-Tl9^mJsywe6!`]oDZg7V.gml;zR4%R^U~k]-;');
define('LOGGED_IN_KEY',    'Ow~6+JcrFm`G<0vvp]?!D;AK)Sbmx:e[&VWdcansy:nYy/4/Ma;k`RNcB_h`IyM@');
define('NONCE_KEY',        'lnx6?`~LMqcqIA8c0-yX1*{0,yG.-p>I+^2OvhsH8 ^RLKw<h%wCw.rl[:xj+z%2');
define('AUTH_SALT',        'k@-Gbb$.zaR[]!#9).+JA1+.HC+- Q_:IG=>Y,+v!^^i~PdY:|b9gLW&>t}TC F#');
define('SECURE_AUTH_SALT', '>T1*B:v+7V#cG@bzFg+g<d_TByn4@zAku1C1-6x+q=mo?}A,f+QyeQZ~Mr]4Ez?*');
define('LOGGED_IN_SALT',   'gZ^QG-~9sX1:BAtQa@yqt!X35!VN!{QWDX:AtYS~m(7MbL~.2HPLc11|l:0K!EbF');
define('NONCE_SALT',       'W_M#%hsFgH)t8NP42wOzjO4kp&{&J(T]y8Xe!zoH7x?8#{~dWtO7[S}EX45Z9#x=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
