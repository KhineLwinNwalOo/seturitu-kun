<?php
/**
 * Theme, template, and stylesheet functions.
 *
 * @package WordPress
 * @subpackage Theme
 */

/**
 * Returns an array of WP_Theme objects based on the arguments.
 *
 * Despite advances over get_themes(), this function is quite expensive, and grows
 * linearly with additional themes. Stick to wp_get_theme() if possible.
 *
 * @since 3.4.0
 *
 * @param array $args The search arguments. Optional.
 * - errors      mixed  True to return themes with errors, false to return themes without errors, null
 *                      to return all themes. Defaults to false.
 * - allowed     mixed  (Multisite) True to return only allowed themes for a site. False to return only
 *                      disallowed themes for a site. 'site' to return only site-allowed themes. 'network'
 *                      to return only network-allowed themes. Null to return all themes. Defaults to null.
 * - blog_id     int    (Multisite) The blog ID used to calculate which themes are allowed. Defaults to 0,
 *                      synonymous for the current blog.
 * @return Array of WP_Theme objects.
 */
function wp_get_themes( $args = array() ) {
	global $wp_theme_directories;

	$defaults = array( 'errors' => false, 'allowed' => null, 'blog_id' => 0 );
	$args = wp_parse_args( $args, $defaults );

	$theme_directories = search_theme_directories();

	if ( count( $wp_theme_directories ) > 1 ) {
		// Make sure the current theme wins out, in case search_theme_directories() picks the wrong
		// one in the case of a conflict. (Normally, last registered theme root wins.)
		$current_theme = get_stylesheet();
		if ( isset( $theme_directories[ $current_theme ] ) ) {
			$root_of_current_theme = get_raw_theme_root( $current_theme );
			if ( ! in_array( $root_of_current_theme, $wp_theme_directories ) )
				$root_of_current_theme = WP_CONTENT_DIR . $root_of_current_theme;
			$theme_directories[ $current_theme ]['theme_root'] = $root_of_current_theme;
		}
	}

	if ( empty( $theme_directories ) )
		return array();

	if ( is_multisite() && null !== $args['allowed'] ) {
		$allowed = $args['allowed'];
		if ( 'network' === $allowed )
			$theme_directories = array_intersect_key( $theme_directories, WP_Theme::get_allowed_on_network() );
		elseif ( 'site' === $allowed )
			$theme_directories = array_intersect_key( $theme_directories, WP_Theme::get_allowed_on_site( $args['blog_id'] ) );
		elseif ( $allowed )
			$theme_directories = array_intersect_key( $theme_directories, WP_Theme::get_allowed( $args['blog_id'] ) );
		else
			$theme_directories = array_diff_key( $theme_directories, WP_Theme::get_allowed( $args['blog_id'] ) );
	}

	$themes = array();
	static $_themes = array();

	foreach ( $theme_directories as $theme => $theme_root ) {
		if ( isset( $_themes[ $theme_root['theme_root'] . '/' . $theme ] ) )
			$themes[ $theme ] = $_themes[ $theme_root['theme_root'] . '/' . $theme ];
		else
			$themes[ $theme ] = $_themes[ $theme_root['theme_root'] . '/' . $theme ] = new WP_Theme( $theme, $theme_root['theme_root'] );
	}

	if ( null !== $args['errors'] ) {
		foreach ( $themes as $theme => $wp_theme ) {
			if ( $wp_theme->errors() != $args['errors'] )
				unset( $themes[ $theme ] );
		}
	}

	return $themes;
}

/**
 * Gets a WP_Theme object for a theme.
 *
 * @since 3.4.0
 *
 * @param string $stylesheet Directory name for the theme. Optional. Defaults to current theme.
 * @param string $theme_root Absolute path of the theme root to look in. Optional. If not specified, get_raw_theme_root()
 * 	is used to calculate the theme root for the $stylesheet provided (or current theme).
 * @return WP_Theme Theme object. Be sure to check the object's exists() method if you need to confirm the theme's existence.
 */
function wp_get_theme( $stylesheet = null, $theme_root = null ) {
	global $wp_theme_directories;

	if ( empty( $stylesheet ) )
		$stylesheet = get_stylesheet();

	if ( empty( $theme_root ) ) {
		$theme_root = get_raw_theme_root( $stylesheet );
		if ( false === $theme_root )
			$theme_root = WP_CONTENT_DIR . '/themes';
		elseif ( ! in_array( $theme_root, (array) $wp_theme_directories ) )
			$theme_root = WP_CONTENT_DIR . $theme_root;
	}

	return new WP_Theme( $stylesheet, $theme_root );
}

/**
 * Clears the cache held by get_theme_roots() and WP_Theme.
 *
 * @since 3.5.0
 * @param bool $clear_update_cache Whether to clear the Theme updates cache
 */
function wp_clean_themes_cache( $clear_update_cache = true ) {
	if ( $clear_update_cache )
		delete_site_transient( 'update_themes' );
	search_theme_directories( true );
	foreach ( wp_get_themes( array( 'errors' => null ) ) as $theme )
		$theme->cache_delete();
}

/**
 * Whether a child theme is in use.
 *
 * @since 3.0.0
 *
 * @return bool true if a child theme is in use, false otherwise.
 **/
function is_child_theme() {
	return ( TEMPLATEPATH !== STYLESHEETPATH );
}

/**
 * Retrieve name of the current stylesheet.
 *
 * The theme name that the administrator has currently set the front end theme
 * as.
 *
 * For all extensive purposes, the template name and the stylesheet name are
 * going to be the same for most cases.
 *
 * @since 1.5.0
 *
 * @return string Stylesheet name.
 */
function get_stylesheet() {
	/**
	 * Filter the name of current stylesheet.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet Name of the current stylesheet.
	 */
	return apply_filters( 'stylesheet', get_option( 'stylesheet' ) );
}

/**
 * Retrieve stylesheet directory path for current theme.
 *
 * @since 1.5.0
 *
 * @return string Path to current theme directory.
 */
function get_stylesheet_directory() {
	$stylesheet = get_stylesheet();
	$theme_root = get_theme_root( $stylesheet );
	$stylesheet_dir = "$theme_root/$stylesheet";

	/**
	 * Filter the stylesheet directory path for current theme.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_dir Absolute path to the current them.
	 * @param string $stylesheet     Directory name of the current theme.
	 * @param string $theme_root     Absolute path to themes directory.
	 */
	return apply_filters( 'stylesheet_directory', $stylesheet_dir, $stylesheet, $theme_root );
}

/**
 * Retrieve stylesheet directory URI.
 *
 * @since 1.5.0
 *
 * @return string
 */
function get_stylesheet_directory_uri() {
	$stylesheet = get_stylesheet();
	$theme_root_uri = get_theme_root_uri( $stylesheet );
	$stylesheet_dir_uri = "$theme_root_uri/$stylesheet";

	/**
	 * Filter the stylesheet directory URI.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_dir_uri Stylesheet directory URI.
	 * @param string $stylesheet         Name of the activated theme's directory.
	 * @param string $theme_root_uri     Themes root URI.
	 */
	return apply_filters( 'stylesheet_directory_uri', $stylesheet_dir_uri, $stylesheet, $theme_root_uri );
}

/**
 * Retrieve URI of current theme stylesheet.
 *
 * The stylesheet file name is 'style.css' which is appended to {@link
 * get_stylesheet_directory_uri() stylesheet directory URI} path.
 *
 * @since 1.5.0
 *
 * @return string
 */
function get_stylesheet_uri() {
	$stylesheet_dir_uri = get_stylesheet_directory_uri();
	$stylesheet_uri = $stylesheet_dir_uri . '/style.css';
	/**
	 * Filter the URI of the current theme stylesheet.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_uri     Stylesheet URI for the current theme/child theme.
	 * @param string $stylesheet_dir_uri Stylesheet directory URI for the current theme/child theme.
	 */
	return apply_filters( 'stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
}

/**
 * Retrieve localized stylesheet URI.
 *
 * The stylesheet directory for the localized stylesheet files are located, by
 * default, in the base theme directory. The name of the locale file will be the
 * locale followed by '.css'. If that does not exist, then the text direction
 * stylesheet will be checked for existence, for example 'ltr.css'.
 *
 * The theme may change the location of the stylesheet directory by either using
 * the 'stylesheet_directory_uri' filter or the 'locale_stylesheet_uri' filter.
 * If you want to change the location of the stylesheet files for the entire
 * WordPress workflow, then change the former. If you just have the locale in a
 * separate folder, then change the latter.
 *
 * @since 2.1.0
 *
 * @return string
 */
function get_locale_stylesheet_uri() {
	global $wp_locale;
	$stylesheet_dir_uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$locale = get_locale();
	if ( file_exists("$dir/$locale.css") )
		$stylesheet_uri = "$stylesheet_dir_uri/$locale.css";
	elseif ( !empty($wp_locale->text_direction) && file_exists("$dir/{$wp_locale->text_direction}.css") )
		$stylesheet_uri = "$stylesheet_dir_uri/{$wp_locale->text_direction}.css";
	else
		$stylesheet_uri = '';
	/**
	 * Filter the localized stylesheet URI.
	 *
	 * @since 2.1.0
	 *
	 * @param string $stylesheet_uri     Localized stylesheet URI.
	 * @param string $stylesheet_dir_uri Stylesheet directory URI.
	 */
	return apply_filters( 'locale_stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
}

/**
 * Retrieve name of the current theme.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls 'template' filter on template option.
 *
 * @return string Template name.
 */
function get_template() {
	/**
	 * Filter the name of the current theme.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template Current theme's directory name.
	 */
	return apply_filters( 'template', get_option( 'template' ) );
}

/**
 * Retrieve current theme directory.
 *
 * @since 1.5.0
 *
 * @return string Template directory path.
 */
function get_template_directory() {
	$template = get_template();
	$theme_root = get_theme_root( $template );
	$template_dir = "$theme_root/$template";

	/**
	 * Filter the current theme directory path.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template_dir The URI of the current theme directory.
	 * @param string $template     Directory name of the current theme.
	 * @param string $theme_root   Absolute path to the themes directory.
	 */
	return apply_filters( 'template_directory', $template_dir, $template, $theme_root );
}

/**
 * Retrieve theme directory URI.
 *
 * @since 1.5.0
 *
 * @return string Template directory URI.
 */
function get_template_directory_uri() {
	$template = get_template();
	$theme_root_uri = get_theme_root_uri( $template );
	$template_dir_uri = "$theme_root_uri/$template";

	/**
	 * Filter the current theme directory URI.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template_dir_uri The URI of the current theme directory.
	 * @param string $template         Directory name of the current theme.
	 * @param string $theme_root_uri   The themes root URI.
	 */
	return apply_filters( 'template_directory_uri', $template_dir_uri, $template, $theme_root_uri );
}

/**
 * Retrieve theme roots.
 *
 * @since 2.9.0
 *
 * @return array|string An array of theme roots keyed by template/stylesheet or a single theme root if all themes have the same root.
 */
function get_theme_roots() {
	global $wp_theme_directories;

	if ( count($wp_theme_directories) <= 1 )
		return '/themes';

	$theme_roots = get_site_transient( 'theme_roots' );
	if ( false === $theme_roots ) {
		search_theme_directories( true ); // Regenerate the transient.
		$theme_roots = get_site_transient( 'theme_roots' );
	}
	return $theme_roots;
}

/**
 * Register a directory that contains themes.
 *
 * @since 2.9.0
 *
 * @param string $directory Either the full filesystem path to a theme folder or a folder within WP_CONTENT_DIR
 * @return bool
 */
function register_theme_directory( $directory ) {
	global $wp_theme_directories;

	if ( ! file_exists( $directory ) ) {
		// Try prepending as the theme directory could be relative to the content directory
		$directory = WP_CONTENT_DIR . '/' . $directory;
		// If this directory does not exist, return and do not register
		if ( ! file_exists( $directory ) )
			return false;
	}

	$wp_theme_directories[] = $directory;

	return true;
}

/**
 * Search all registered theme directories for complete and valid themes.
 *
 * @since 2.9.0
 *
 * @param bool $force Optional. Whether to force a new directory scan. Defaults to false.
 * @return array Valid themes found
 */
function search_theme_directories( $force = false ) {
	global $wp_theme_directories;
	if ( empty( $wp_theme_directories ) )
		return false;

	static $found_themes;
	if ( ! $force && isset( $found_themes ) )
		return $found_themes;

	$found_themes = array();

	$wp_theme_directories = (array) $wp_theme_directories;

	// Set up maybe-relative, maybe-absolute array of theme directories.
	// We always want to return absolute, but we need to cache relative
	// to use in get_theme_root().
	foreach ( $wp_theme_directories as $theme_root ) {
		if ( 0 === strpos( $theme_root, WP_CONTENT_DIR ) )
			$relative_theme_roots[ str_replace( WP_CONTENT_DIR, '', $theme_root ) ] = $theme_root;
		else
			$relative_theme_roots[ $theme_root ] = $theme_root;
	}

	/**
	 * Filter whether to get the cache of the registered theme directories.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   $cache_expiration Whether to get the cache of the theme directories. Default false.
	 * @param string $cache_directory  Directory to be searched for the cache.
	 */
	if ( $cache_expiration = apply_filters( 'wp_cache_themes_persistently', false, 'search_theme_directories' ) ) {
		$cached_roots = get_site_transient( 'theme_roots' );
		if ( is_array( $cached_roots ) ) {
			foreach ( $cached_roots as $theme_dir => $theme_root ) {
				// A cached theme root is no longer around, so skip it.
				if ( ! isset( $relative_theme_roots[ $theme_root ] ) )
					continue;
				$found_themes[ $theme_dir ] = array(
					'theme_file' => $theme_dir . '/style.css',
					'theme_root' => $relative_theme_roots[ $theme_root ], // Convert relative to absolute.
				);
			}
			return $found_themes;
		}
		if ( ! is_int( $cache_expiration ) )
			$cache_expiration = 1800; // half hour
	} else {
		$cache_expiration = 1800; // half hour
	}

	/* Loop the registered theme directories and extract all themes */
	foreach ( $wp_theme_directories as $theme_root ) {

		// Start with directories in the root of the current theme directory.
		$dirs = @ scandir( $theme_root );
		if ( ! $dirs ) {
			trigger_error( "$theme_root is not readable", E_USER_NOTICE );
			continue;
		}
		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $theme_root . '/' . $dir ) || $dir[0] == '.' || $dir == 'CVS' )
				continue;
			if ( file_exists( $theme_root . '/' . $dir . '/style.css' ) ) {
				// wp-content/themes/a-single-theme
				// wp-content/themes is $theme_root, a-single-theme is $dir
				$found_themes[ $dir ] = array(
					'theme_file' => $dir . '/style.css',
					'theme_root' => $theme_root,
				);
			} else {
				$found_theme = false;
				// wp-content/themes/a-folder-of-themes/*
				// wp-content/themes is $theme_root, a-folder-of-themes is $dir, then themes are $sub_dirs
				$sub_dirs = @ scandir( $theme_root . '/' . $dir );
				if ( ! $sub_dirs ) {
					trigger_error( "$theme_root/$dir is not readable", E_USER_NOTICE );
					continue;
				}
				foreach ( $sub_dirs as $sub_dir ) {
					if ( ! is_dir( $theme_root . '/' . $dir . '/' . $sub_dir ) || $dir[0] == '.' || $dir == 'CVS' )
						continue;
					if ( ! file_exists( $theme_root . '/' . $dir . '/' . $sub_dir . '/style.css' ) )
						continue;
					$found_themes[ $dir . '/' . $sub_dir ] = array(
						'theme_file' => $dir . '/' . $sub_dir . '/style.css',
						'theme_root' => $theme_root,
					);
					$found_theme = true;
				}
				// Never mind the above, it's just a theme missing a style.css.
				// Return it; WP_Theme will catch the error.
				if ( ! $found_theme )
					$found_themes[ $dir ] = array(
						'theme_file' => $dir . '/style.css',
						'theme_root' => $theme_root,
					);
			}
		}
	}

	asort( $found_themes );

	$theme_roots = array();
	$relative_theme_roots = array_flip( $relative_theme_roots );

	foreach ( $found_themes as $theme_dir => $theme_data ) {
		$theme_roots[ $theme_dir ] = $relative_theme_roots[ $theme_data['theme_root'] ]; // Convert absolute to relative.
	}

	if ( $theme_roots != get_site_transient( 'theme_roots' ) )
		set_site_transient( 'theme_roots', $theme_roots, $cache_expiration );

	return $found_themes;
}

/**
 * Retrieve path to themes directory.
 *
 * Does not have trailing slash.
 *
 * @since 1.5.0
 *
 * @param string $stylesheet_or_template The stylesheet or template 