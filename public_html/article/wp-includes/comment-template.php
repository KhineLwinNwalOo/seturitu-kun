<?php
/**
 * Comment template functions
 *
 * These functions are meant to live inside of the WordPress loop.
 *
 * @package WordPress
 * @subpackage Template
 */

/**
 * Retrieve the author of the current comment.
 *
 * If the comment has an empty comment_author field, then 'Anonymous' person is
 * assumed.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to retrieve the author. Default current comment.
 * @return string The comment author
 */
function get_comment_author( $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );

	if ( empty( $comment->comment_author ) ) {
		if ( $comment->user_id && $user = get_userdata( $comment->user_id ) )
			$author = $user->display_name;
		else
			$author = __('Anonymous');
	} else {
		$author = $comment->comment_author;
	}

	/**
	 * Filter the returned comment author name.
	 *
	 * @since 1.5.0
	 *
	 * @param string $author The comment author's username.
	 */
	return apply_filters( 'get_comment_author', $author );
}

/**
 * Displays the author of the current comment.
 *
 * @since 0.71
 *
 * @param int $comment_ID Optional. The ID of the comment for which to print the author. Default current comment.
 */
function comment_author( $comment_ID = 0 ) {
	$author = get_comment_author( $comment_ID );
	/**
	 * Filter the comment author's name for display.
	 *
	 * @since 1.2.0
	 *
	 * @param string $author The comment author's username.
	 */
	$author = apply_filters( 'comment_author', $author );
	echo $author;
}

/**
 * Retrieve the email of the author of the current comment.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to get the author's email. Default current comment.
 * @return string The current comment author's email
 */
function get_comment_author_email( $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );
	/**
	 * Filter the comment author's returned email address.
	 *
	 * @since 1.5.0
	 *
	 * @param string $comment->comment_author_email The comment author's email address.
	 */
	return apply_filters( 'get_comment_author_email', $comment->comment_author_email );
}

/**
 * Display the email of the author of the current global $comment.
 *
 * Care should be taken to protect the email address and assure that email
 * harvesters do not capture your commentors' email address. Most assume that
 * their email address will not appear in raw form on the blog. Doing so will
 * enable anyone, including those that people don't want to get the email
 * address and use it for their own means good and bad.
 *
 * @since 0.71
 *
 * @param int $comment_ID Optional. The ID of the comment for which to print the author's email. Default current comment.
 */
function comment_author_email( $comment_ID = 0 ) {
	$author_email = get_comment_author_email( $comment_ID );
	/**
	 * Filter the comment author's email for display.
	 *
	 * @since 1.2.0
	 *
	 * @param string $author_email The comment author's email address.
	 */
	echo apply_filters( 'author_email', $author_email );
}

/**
 * Display the html email link to the author of the current comment.
 *
 * Care should be taken to protect the email address and assure that email
 * harvesters do not capture your commentors' email address. Most assume that
 * their email address will not appear in raw form on the blog. Doing so will
 * enable anyone, including those that people don't want to get the email
 * address and use it for their own means good and bad.
 *
 * @global object $comment The current Comment row object

 * @since 0.71
 *
 * @param string $linktext Optional. The text to display instead of the comment author's email address. Default empty.
 * @param string $before   Optional. The text or HTML to display before the email link.Default empty.
 * @param string $after    Optional. The text or HTML to display after the email link. Default empty.
 */
function comment_author_email_link( $linktext = '', $before = '', $after = '' ) {
	if ( $link = get_comment_author_email_link( $linktext, $before, $after ) )
		echo $link;
}

/**
 * Return the html email link to the author of the current comment.
 *
 * Care should be taken to protect the email address and assure that email
 * harvesters do not capture your commentors' email address. Most assume that
 * their email address will not appear in raw form on the blog. Doing so will
 * enable anyone, including those that people don't want to get the email
 * address and use it for their own means good and bad.
 *
 * @global object $comment The current Comment row object.
 *
 * @since 2.7
 *
 * @param string $linktext Optional. The text to display instead of the comment author's email address. Default empty.
 * @param string $before   Optional. The text or HTML to display before the email link. Default empty.
 * @param string $after    Optional. The text or HTML to display after the email link. Default empty.
 */
function get_comment_author_email_link( $linktext = '', $before = '', $after = '' ) {
	global $comment;
	/**
	 * Filter the comment author's email for display.
	 *
	 * Care should be taken to protect the email address and assure that email
	 * harvesters do not capture your commentors' email address.
	 *
	 * @since 1.2.0
	 *
	 * @param string $comment->comment_author_email The comment author's email address.
	 */
	$email = apply_filters( 'comment_email', $comment->comment_author_email );
	if ((!empty($email)) && ($email != '@')) {
	$display = ($linktext != '') ? $linktext : $email;
		$return  = $before;
		$return .= "<a href='mailto:$email'>$display</a>";
	 	$return .= $after;
		return $return;
	} else {
		return '';
	}
}

/**
 * Retrieve the HTML link to the URL of the author of the current comment.
 *
 * Both get_comment_author_url() and get_comment_author() rely on get_comment(),
 * which falls back to the global comment variable if the $comment_ID argument is empty.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to get the author's link. Default current comment.
 * @return string The comment author name or HTML link for author's URL.
 */
function get_comment_author_link( $comment_ID = 0 ) {
	$url    = get_comment_author_url( $comment_ID );
	$author = get_comment_author( $comment_ID );

	if ( empty( $url ) || 'http://' == $url )
		$return = $author;
	else
		$return = "<a href='$url' rel='external nofollow' class='url'>$author</a>";

	/**
	 * Filter the comment author's link for display.
	 *
	 * @since 1.5.0
	 *
	 * @param string $return The HTML-formatted comment author link. Empty for an invalid URL.
	 */
	return apply_filters( 'get_comment_author_link', $return );
}

/**
 * Display the html link to the url of the author of the current comment.
 *
 * @since 0.71
 * @see get_comment_author_link() Echoes result
 *
 * @param int $comment_ID Optional. The ID of the comment for which to print the author's link. Default current comment.
 */
function comment_author_link( $comment_ID = 0 ) {
	echo get_comment_author_link( $comment_ID );
}

/**
 * Retrieve the IP address of the author of the current comment.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to get the author's IP address. Default current comment.
 * @return string The comment author's IP address.
 */
function get_comment_author_IP( $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );

	/**
	 * Filter the comment author's returned IP address.
	 *
	 * @since 1.5.0
	 *
	 * @param string $comment->comment_author_IP The comment author's IP address.
	 */
	return apply_filters( 'get_comment_author_IP', $comment->comment_author_IP );
}

/**
 * Display the IP address of the author of the current comment.
 *
 * @since 0.71
 *
 * @param int $comment_ID Optional. The ID of the comment for which to print the author's IP address. Default current comment.
 */
function comment_author_IP( $comment_ID = 0 ) {
	echo get_comment_author_IP( $comment_ID );
}

/**
 * Retrieve the url of the author of the current comment.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to get the author's URL. Default current comment.
 * @return string
 */
function get_comment_author_url( $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );
	$url = ('http://' == $comment->comment_author_url) ? '' : $comment->comment_author_url;
	$url = esc_url( $url, array('http', 'https') );
	/**
	 * Filter the comment author's URL.
	 *
	 * @since 1.5.0
	 *
	 * @param string $url The comment author's URL.
	 */
	return apply_filters( 'get_comment_author_url', $url );
}

/**
 * Display the url of the author of the current comment.
 *
 * @since 0.71
 *
 * @param int $comment_ID Optional. The ID of the comment for which to print the author's URL. Default current comment.
 */
function comment_author_url( $comment_ID = 0 ) {
	$author_url = get_comment_author_url( $comment_ID );
	/**
	 * Filter the comment author's URL for display.
	 *
	 * @since 1.2.0
	 *
	 * @param string $author_url The comment author's URL.
	 */
	echo apply_filters( 'comment_url', $author_url );
}

/**
 * Retrieves the HTML link of the url of the author of the current comment.
 *
 * $linktext parameter is only used if the URL does not exist for the comment
 * author. If the URL does exist then the URL will be used and the $linktext
 * will be ignored.
 *
 * Encapsulate the HTML link between the $before and $after. So it will appear
 * in the order of $before, link, and finally $after.
 *
 * @since 1.5.0
 *
 * @param string $linktext Optional. The text to display instead of the comment author's email address. Default empty.
 * @param string $before   Optional. The text or HTML to display before the email link. Default empty.
 * @param string $after    Optional. The text or HTML to display after the email link. Default empty.
 * @return string The HTML link between the $before and $after parameters.
 */
function get_comment_author_url_link( $linktext = '', $before = '', $after = '' ) {
	$url = get_comment_author_url();
	$display = ($linktext != '') ? $linktext : $url;
	$display = str_replace( 'http://www.', '', $display );
	$display = str_replace( 'http://', '', $display );
	if ( '/' == substr($display, -1) )
		$display = substr($display, 0, -1);
	$return = "$before<a href='$url' rel='external'>$display</a>$after";

	/**
	 * Filter the comment author's returned URL link.
	 *
	 * @since 1.5.0
	 *
	 * @param string $return The HTML-formatted comment author URL link.
	 */
	return apply_filters( 'get_comment_author_url_link', $return );
}

/**
 * Displays the HTML link of the url of the author of the current comment.
 *
 * @since 0.71
 *
 * @param string $linktext Optional. The text to display instead of the comment author's email address. Default empty.
 * @param string $before   Optional. The text or HTML to display before the email link. Default empty.
 * @param string $after    Optional. The text or HTML to display after the email link. Default empty.
 */
function comment_author_url_link( $linktext = '', $before = '', $after = '' ) {
	echo get_comment_author_url_link( $linktext, $before, $after );
}

/**
 * Generates semantic classes for each comment element
 *
 * @since 2.7.0
 *
 * @param string|array $class      Optional. One or more classes to add to the class list. Default empty.
 * @param int          $comment_id Optional. Comment ID. Default current comment.
 * @param int|WP_Post  $post_id    Optional. Post ID or WP_Post object. Default current post.
 * @param bool         $echo       Optional. Whether comment_class should echo or return. Default true.
 */
function comment_class( $class = '', $comment_id = null, $post_id = null, $echo = true ) {
	// Separates classes with a single space, collates classes for comment DIV
	$class = 'class="' . join( ' ', get_comment_class( $class, $comment_id, $post_id ) ) . '"';
	if ( $echo)
		echo $class;
	else
		return $class;
}

/**
 * Returns the classes for the comment div as an array
 *
 * @since 2.7.0
 *
 * @param string|array $class      Optional. One or more classes to add to the class list. Default empty.
 * @param int          $comment_id Optional. Comment ID. Default current comment.
 * @param int|WP_Post  $post_id    Optional. Post ID or WP_Post object. Default current post.
 * @return array An array of classes.
 */
function get_comment_class( $class = '', $comment_id = null, $post_id = null ) {
	global $comment_alt, $comment_depth, $comment_thread_alt;

	$comment = get_comment($comment_id);

	$classes = array();

	// Get the comment type (comment, trackback),
	$classes[] = ( empty( $comment->comment_type ) ) ? 'comment' : $comment->comment_type;

	// If the comment author has an id (registered), then print the log in name
	if ( $comment->user_id > 0 && $user = get_userdata($comment->user_id) ) {
		// For all registered users, 'byuser'
		$classes[] = 'byuser';
		$classes[] = 'comment-author-' . sanitize_html_class($user->user_nicename, $comment->user_id);
		// For comment authors who are the author of the post
		if ( $post = get_post($post_id) ) {
			if ( $comment->user_id === $post->post_author )
				$classes[] = 'bypostauthor';
		}
	}

	if ( empty($comment_alt) )
		$comment_alt = 0;
	if ( empty($comment_depth) )
		$comment_depth = 1;
	if ( empty($comment_thread_alt) )
		$comment_thread_alt = 0;

	if ( $comment_alt % 2 ) {
		$classes[] = 'odd';
		$classes[] = 'alt';
	} else {
		$classes[] = 'even';
	}

	$comment_alt++;

	// Alt for top-level comments
	if ( 1 == $comment_depth ) {
		if ( $comment_thread_alt % 2 ) {
			$classes[] = 'thread-odd';
			$classes[] = 'thread-alt';
		} else {
			$classes[] = 'thread-even';
		}
		$comment_thread_alt++;
	}

	$classes[] = "depth-$comment_depth";

	if ( !empty($class) ) {
		if ( !is_array( $class ) )
			$class = preg_split('#\s+#', $class);
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);

	/**
	 * Filter the returned CSS classes for the current comment.
	 *
	 * @since 2.7.0
	 *
	 * @param array       $classes    An array of comment classes.
	 * @param string      $class      A comma-separated list of additional classes added to the list.
	 * @param int         $comment_id The comment id.
	 * @param int|WP_Post $post_id    The post ID or WP_Post object.
	 */
	return apply_filters( 'comment_class', $classes, $class, $comment_id, $post_id );
}

/**
 * Retrieve the comment date of the current comment.
 *
 * @since 1.5.0
 *
 * @param string $d          Optional. The format of the date. Default user's setting.
 * @param int    $comment_ID Optional. The ID of the comment for which to get the date. Default current comment.
 * @return string The comment's date.
 */
function get_comment_date( $d = '', $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );
	if ( '' == $d )
		$date = mysql2date(get_option('date_format'), $comment->comment_date);
	else
		$date = mysql2date($d, $comment->comment_date);
	/**
	 * Filter the returned comment date.
	 *
	 * @since 1.5.0
	 *
	 * @param string|int $date Formatted date string or Unix timestamp.
	 * @param string     $d    The format of the date.
	 */
	return apply_filters( 'get_comment_date', $date, $d );
}

/**
 * Display the comment date of the current comment.
 *
 * @since 0.71
 *
 * @param string $d          Optional. The format of the date. Default user's settings.
 * @param int    $comment_ID Optional. The ID of the comment for which to print the date. Default current comment.
 */
function comment_date( $d = '', $comment_ID = 0 ) {
	echo get_comment_date( $d, $comment_ID );
}

/**
 * Retrieve the excerpt of the current comment.
 *
 * Will cut each word and only output the first 20 words with '&hellip;' at the end.
 * If the word count is less than 20, then no truncating is done and no '&hellip;'
 * will appear.
 *
 * @since 1.5.0
 *
 * @param int $comment_ID Optional. The ID of the comment for which to get the excerpt. Default current comment.
 * @return string The maybe truncated comment with 20 words or less.
 */
function get_comment_excerpt( $comment_ID = 0 ) {
	$comment = get_comment( $comment_ID );
	$comment_text = strip_tags($comment->comment_content);
	$blah = explode(' ', $comment_text);
	if (count($blah) > 20) {
		$k = 20;
		$use_dotdotdot = 1;
	} else {
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for ($i=0; $i<$k; $i++) {
		$excerpt .= $blah[$i] . ' ';
	}
	$e