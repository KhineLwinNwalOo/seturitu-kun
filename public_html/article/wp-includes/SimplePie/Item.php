<?php
/**
 * SimplePie
 *
 * A PHP-Based RSS and Atom Feed Framework.
 * Takes the hard work out of managing a complete RSS/Atom solution.
 *
 * Copyright (c) 2004-2012, Ryan Parman, Geoffrey Sneddon, Ryan McCue, and contributors
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 	* Redistributions of source code must retain the above copyright notice, this list of
 * 	  conditions and the following disclaimer.
 *
 * 	* Redistributions in binary form must reproduce the above copyright notice, this list
 * 	  of conditions and the following disclaimer in the documentation and/or other materials
 * 	  provided with the distribution.
 *
 * 	* Neither the name of the SimplePie Team nor the names of its contributors may be used
 * 	  to endorse or promote products derived from this software without specific prior
 * 	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package SimplePie
 * @version 1.3.1
 * @copyright 2004-2012 Ryan Parman, Geoffrey Sneddon, Ryan McCue
 * @author Ryan Parman
 * @author Geoffrey Sneddon
 * @author Ryan McCue
 * @link http://simplepie.org/ SimplePie
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * Manages all item-related data
 *
 * Used by {@see SimplePie::get_item()} and {@see SimplePie::get_items()}
 *
 * This class can be overloaded with {@see SimplePie::set_item_class()}
 *
 * @package SimplePie
 * @subpackage API
 */
class SimplePie_Item
{
	/**
	 * Parent feed
	 *
	 * @access private
	 * @var SimplePie
	 */
	var $feed;

	/**
	 * Raw data
	 *
	 * @access private
	 * @var array
	 */
	var $data = array();

	/**
	 * Registry object
	 *
	 * @see set_registry
	 * @var SimplePie_Registry
	 */
	protected $registry;

	/**
	 * Create a new item object
	 *
	 * This is usually used by {@see SimplePie::get_items} and
	 * {@see SimplePie::get_item}. Avoid creating this manually.
	 *
	 * @param SimplePie $feed Parent feed
	 * @param array $data Raw data
	 */
	public function __construct($feed, $data)
	{
		$this->feed = $feed;
		$this->data = $data;
	}

	/**
	 * Set the registry handler
	 *
	 * This is usually used by {@see SimplePie_Registry::create}
	 *
	 * @since 1.3
	 * @param SimplePie_Registry $registry
	 */
	public function set_registry(SimplePie_Registry $registry)
	{
		$this->registry = $registry;
	}

	/**
	 * Get a string representation of the item
	 *
	 * @return string
	 */
	public function __toString()
	{
		return md5(serialize($this->data));
	}

	/**
	 * Remove items that link back to this before destroying this object
	 */
	public function __destruct()
	{
		if ((version_compare(PHP_VERSION, '5.3', '<') || !gc_enabled()) && !ini_get('zend.ze1_compatibility_mode'))
		{
			unset($this->feed);
		}
	}

	/**
	 * Get data for an item-level element
	 *
	 * This method allows you to get access to ANY element/attribute that is a
	 * sub-element of the item/entry tag.
	 *
	 * See {@see SimplePie::get_feed_tags()} for a description of the return value
	 *
	 * @since 1.0
	 * @see http://simplepie.org/wiki/faq/supported_xml_namespaces
	 * @param string $namespace The URL of the XML namespace of the elements you're trying to access
	 * @param string $tag Tag name
	 * @return array
	 */
	public function get_item_tags($namespace, $tag)
	{
		if (isset($this->data['child'][$namespace][$tag]))
		{
			return $this->data['child'][$namespace][$tag];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the base URL value from the parent feed
	 *
	 * Uses `<xml:base>`
	 *
	 * @param array $element
	 * @return string
	 */
	public function get_base($element = array())
	{
		return $this->feed->get_base($element);
	}

	/**
	 * Sanitize feed data
	 *
	 * @access private
	 * @see SimplePie::sanitize()
	 * @param string $data Data to sanitize
	 * @param int $type One of the SIMPLEPIE_CONSTRUCT_* constants
	 * @param string $base Base URL to resolve URLs against
	 * @return string Sanitized data
	 */
	public function sanitize($data, $type, $base = '')
	{
		return $this->feed->sanitize($data, $type, $base);
	}

	/**
	 * Get the parent feed
	 *
	 * Note: this may not work as you think for multifeeds!
	 *
	 * @link http://simplepie.org/faq/typical_multifeed_gotchas#missing_data_from_feed
	 * @since 1.0
	 * @return SimplePie
	 */
	public function get_feed()
	{
		return $this->feed;
	}

	/**
	 * Get the unique identifier for the item
	 *
	 * This is usually used when writing code to check for new items in a feed.
	 *
	 * Uses `<atom:id>`, `<guid>`, `<dc:identifier>` or the `about` attribute
	 * for RDF. If none of these are supplied (or `$hash` is true), creates an
	 * MD5 hash based on the permalink and title. If either of those are not
	 * supplied, creates a hash based on the full feed data.
	 *
	 * @since Beta 2
	 * @param boolean $hash Should we force using a hash instead of the supplied ID?
	 * @return string
	 */
	public function get_id($hash = false)
	{
		if (!$hash)
		{
			if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'id'))
			{
				return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'id'))
			{
				return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'guid'))
			{
				return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'identifier'))
			{
				return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'identifier'))
			{
				return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif (isset($this->data['attribs'][SIMPLEPIE_NAMESPACE_RDF]['about']))
			{
				return $this->sanitize($this->data['attribs'][SIMPLEPIE_NAMESPACE_RDF]['about'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif (($return = $this->get_permalink()) !== null)
			{
				return $return;
			}
			elseif (($return = $this->get_title()) !== null)
			{
				return $return;
			}
		}
		if ($this->get_permalink() !== null || $this->get_title() !== null)
		{
			return md5($this->get_permalink() . $this->get_title());
		}
		else
		{
			return md5(serialize($this->data));
		}
	}

	/**
	 * Get the title of the item
	 *
	 * Uses `<atom:title>`, `<title>` or `<dc:title>`
	 *
	 * @since Beta 2 (previously called `get_item_title` since 0.8)
	 * @return string|null
	 */
	public function get_title()
	{
		if (!isset($this->data['title']))
		{
			if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'title'))
			{
				$this->data['title'] = $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			else
			{
				$this->data['title'] = null;
			}
		}
		return $this->data['title'];
	}

	/**
	 * Get the content for the item
	 *
	 * Prefers summaries over full content , but will return full content if a
	 * summary does not exist.
	 *
	 * To prefer full content instead, use {@see get_content}
	 *
	 * Uses `<atom:summary>`, `<description>`, `<dc:description>` or
	 * `<itunes:subtitle>`
	 *
	 * @since 0.8
	 * @param boolean $description_only Should we avoid falling back to the content?
	 * @return string|null
	 */
	public function get_description($description_only = false)
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'summary'))
		{
			return $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'summary'))
		{
			return $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'summary'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'subtitle'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML);
		}

		elseif (!$description_only)
		{
			return $this->get_content(true);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the content for the item
	 *
	 * Prefers full content over summaries, but will return a summary if full
	 * content does not exist.
	 *
	 * To prefer summaries instead, use {@see get_description}
	 *
	 * Uses `<atom:content>` or `<content:encoded>` (RSS 1.0 Content Module)
	 *
	 * @since 1.0
	 * @param boolean $content_only Should we avoid falling back to the description?
	 * @return string|null
	 */
	public function get_content($content_only = false)
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'content'))
		{
			return $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_10_content_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'content'))
		{
			return $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10_MODULES_CONTENT, 'encoded'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif (!$content_only)
		{
			return $this->get_description(true);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get a category for the item
	 *
	 * @since Beta 3 (previously called `get_categories()` since Beta 2)
	 * @param int $key The category that you want to return.  Remember that arrays begin with 0, not 1
	 * @return SimplePie_Category|null
	 */
	public function get_category($key = 0)
	{
		$categories = $this->get_categories();
		if (isset($categories[$key]))
		{
			return $categories[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get all categories for the item
	 *
	 * Uses `<atom:category>`, `<category>` or `<dc:subject>`
	 *
	 * @since Beta 3
	 * @return array|null List of {@see SimplePie_Category} objects
	 */
	public function get_categories()
	{
		$categories = array();

		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'category') as $category)
		{
			$term = null;
			$scheme = null;
			$label = null;
			if (isset($category['attribs']['']['term']))
			{
				$term = $this->sanitize($category['attribs']['']['term'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($category['attribs']['']['scheme']))
			{
				$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($category['attribs']['']['label']))
			{
				$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			$categories[] = $this->registry->create('Category', array($term, $scheme, $label));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'category') as $category)
		{
			// This is really the label, but keep this as the term also for BC.
			// Label will also work on retrieving because that falls back to term.
			$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			if (isset($category['attribs']['']['domain']))
			{
				$scheme = $this->sanitize($category['attribs']['']['domain'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			else
			{
				$scheme = null;
			}
			$categories[] = $this->registry->create('Category', array($term, $scheme, null));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'subject') as $category)
		{
			$categories[] = $this->registry->create('Category', array($this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'subject') as $category)
		{
			$categories[] = $this->registry->create('Category', array($this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null));
		}

		if (!empty($categories))
		{
			return array_unique($categories);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get an author for the item
	 *
	 * @since Beta 2
	 * @param int $key The author that you want to return.  Remember that arrays begin with 0, not 1
	 * @return SimplePie_Author|null
	 */
	public function get_author($key = 0)
	{
		$authors = $this->get_authors();
		if (isset($authors[$key]))
		{
			return $authors[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get a contributor for the item
	 *
	 * @since 1.1
	 * @param int $key The contrbutor that you want to return.  Remember that arrays begin with 0, not 1
	 * @return SimplePie_Author|null
	 */
	public function get_contributor($key = 0)
	{
		$contributors = $this->get_contributors();
		if (isset($contributors[$key]))
		{
			return $contributors[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get all contributors for the item
	 *
	 * Uses `<atom:contributor>`
	 *
	 * @since 1.1
	 * @return array|null List of {@see SimplePie_Author} objects
	 */
	public function get_contributors()
	{
		$contributors = array();
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'contributor') as $contributor)
		{
			$name = null;
			$uri = null;
			$email = null;
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data']))
			{
				$name = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data']))
			{
				$uri = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]));
			}
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data']))
			{
				$email = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $uri !== null)
			{
				$contributors[] = $this->registry->create('Author', array($name, $uri, $email));
			}
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'contributor') as $contributor)
		{
			$name = null;
			$url = null;
			$email = null;
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data']))
			{
				$name = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data']))
			{
				$url = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]));
			}
			if (isset($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data']))
			{
				$email = $this->sanitize($contributor['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $url !== null)
			{
				$contributors[] = $this->registry->create('Author', array($name, $url, $email));
			}
		}

		if (!empty($contributors))
		{
			return array_unique($contributors);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get all authors for the item
	 *
	 * Uses `<atom:author>`, `<author>`, `<dc:creator>` or `<itunes:author>`
	 *
	 * @since Beta 2
	 * @return array|null List of {@see SimplePie_Author} objects
	 */
	public function get_authors()
	{
		$authors = array();
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'author') as $author)
		{
			$name = null;
			$uri = null;
			$email = null;
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data']))
			{
				$name = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data']))
			{
				$uri = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]));
			}
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data']))
			{
				$email = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $uri !== null)
			{
				$authors[] = $this->registry->create('Author', array($name, $uri, $email));
			}
		}
		if ($author = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'author'))
		{
			$name = null;
			$url = null;
			$email = null;
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data']))
			{
				$name = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data']))
			{
				$url = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]));
			}
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data']))
			{
				$email = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $url !== null)
			{
				$authors[] = $this->registry->create('Author', array($name, $url, $email));
			}
		}
		if ($author = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'author'))
		{
			$authors[] = $this->registry->create('Author', array(null, null, $this->sanitize($author[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT)));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'creator') as $author)
		{
			$authors[] = $this->registry->create('Author', array($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'creator') as $author)
		{
			$authors[] = $this->registry->create('Author', array($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'author') as $author)
		{
			$authors[] = $this->registry->create('Author', array($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null));
		}

		if (!empty($authors))
		{
			return array_unique($authors);
		}
		elseif (($source = $this->get_source()) && ($authors = $source->get_authors()))
		{
			return $authors;
		}
		elseif ($authors = $this->feed->get_authors())
		{
			return $authors;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the copyright info for the item
	 *
	 * Uses `<atom:rights>` or `<dc:rights>`
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_copyright()
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'rights'))
		{
			return $this->sanitize($return[0]['data'], $this->registry->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'rights'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'rights'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the posting date/time for the item
	 *
	 * Uses `<atom:published>`, `<atom:updated>`, `<atom:issued>`,
	 * `<atom:modified>`, `<pubDate>` or `<dc:date>`
	 *
	 * Note: obeys PHP's timezone setting. To get a UTC date/time, use
	 * {@see get_gmdate}
	 *
	 * @since Beta 2 (previously called `get_item_date` since 0.8)
	 *
	 * @param string $date_format Supports any PHP date format from {@see http://php.net/date} (empty for the raw data)
	 * @return int|string|null
	 */
	public function get_date($date_format = 'j F Y, g:i a')
	{
		if (!isset($this->data['date']))
		{
			if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'published'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'issued'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'created'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'modified'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'pubDate'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'date'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'date'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}

			if (!empty($this->data['date']['raw']))
			{
				$parser = $this->registry->call('Parse_Date', 'get');
				$this->data['date']['parsed'] = $parser->parse($this->data['date']['raw']);
			}
			else
			{
				$this->data['date'] = null;
			}
		}
		if ($this->data['date'])
		{
			$date_format = (string) $date_format;
			switch ($date_format)
			{
				case '':
					return $this->sanitize($this->data['date']['raw'], SIMPLEPIE_CONSTRUCT_TEXT);

				case 'U':
					return $this->data['date']['parsed'];

				default:
					return date($date_format, $this->data['date']['parsed']);
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the update date/time for the item
	 *
	 * Uses `<atom:updated>`
	 *
	 * Note: obeys PHP's timezone setting. To get a UTC date/time, use
	 * {@see get_gmdate}
	 *
	 * @param string $date_format Supports any PHP date format from {@see http://php.net/date} (empty for the raw data)
	 * @return int|string|null
	 */
	public function get_updated_date($date_format = 'j F Y, g:i a')
	{
		if (!isset($this->data['updated']))
		{
			if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated'))
			{
				$this->data['updated']['raw'] = $return[0]['data'];
			}

			if (!empty($this->data['updated']['raw']))
			{
				$parser = $this->registry->call('Parse_Date', 'get');
				$this->data['updated']['parsed'] = $parser->parse($this->data['date']['raw']);
			}
			else
			{
				$this->data['updated'] = null;
			}
		}
		if ($this->data['updated'])
		{
			$date_format = (string) $date_format;
			switch ($date_format)
			{
				case '':
					return $this->sanitize($this->data['updated']['raw'], SIMPLEPIE_CONSTRUCT_TEXT);

				case 'U':
					return $this->data['updated']['parsed'];

				default:
					return date($date_format, $this->data['updated']['parsed']);
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the localized posting date/time for the item
	 *
	 * Returns the date formatted in the localized language. To display in
	 * languages other than the server's default, you need to change the locale
	 * with {@link http://php.net/setlocale setlocale()}. The available
	 * localizations depend on which ones are installed on your web server.
	 *
	 * @since 1.0
	 *
	 * @param string $date_format Supports any PHP date format from {@see http://php.net/strftime} (empty for the raw data)
	 * @return int|string|null
	 */
	public function get_local_date($date_format = '%c')
	{
		if (!$date_format)
		{
			return $this->sanitize($this->get_date(''), SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (($date = $this->get_date('U')) !== null && $date !== false)
		{
			return strftime($date_format, $date);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the posting date/time for the item (UTC time)
	 *
	 * @see get_date
	 * @param string $date_format Supports any PHP date format from {@see http://php.net/date}
	 * @return int|string|null
	 */
	public function get_gmdate($date_format = 'j F Y, g:i a')
	{
		$date = $this->get_date('U');
		if ($date === null)
		{
			return null;
		}

		return gmdate($date_format, $date);
	}

	/**
	 * Get the update date/time for the item (UTC time)
	 *
	 * @see get_updated_date
	 * @param string $date_format Supports any PHP date format from {@see http://php.net/date}
	 * @return int|string|null
	 */
	public function get_updated_gmdate($date_format = 'j F Y, g:i a')
	{
		$date = $this->get_updated_date('U');
		if ($date === null)
		{
			return null;
		}

		return gmdate($date_format, $date);
	}

	/**
	 * Get the permalink for the item
	 *
	 * Returns the first link available with a relationship of "alternate".
	 * Identical to {@see get_link()} with key 0
	 *
	 * @see get_link
	 * @since 0.8
	 * @return string|null Permalink URL
	 */
	public function get_permalink()
	{
		$link = $this->get_link();
		$enclosure = $this->get_enclosure(0);
		if ($link !== null)
		{
			return $link;
		}
		elseif ($enclosure !== null)
		{
			return $enclosure->get_link();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get a single link for the item
	 *
	 * @since Beta 3
	 * @param int $key The link that you want to return.  Remember that arrays begin with 0, not 1
	 * @param string $rel The relationship of the link to return
	 * @return string|null Link URL
	 */
	public function get_link($key = 0, $rel = 'alternate')
	{
		$links = $this->get_links($rel);
		if ($links[$key] !== null)
		{
			return $links[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get all links for the item
	 *
	 * Uses `<atom:link>`, `<link>` or `<guid>`
	 *
	 * @since Beta 2
	 * @param string $rel The relationship of links to return
	 * @return array|null Links found for the item (strings)
	 */
	public function get_links($rel = 'alternate')
	{
		if (!isset($this->data['links']))
		{
			$this->data['links'] = array();
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']))
				{
					$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
					$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));

				}
			}
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']))
				{
					$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
					$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));
				}
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'guid'))
			{
				if (!isset($links[0]['attribs']['']['isPermaLink']) || strtolower(trim($links[0]['attribs']['']['isPermaLink'])) === 'true')
				{
					$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
				}
			}

			$keys = array_keys($this->data['links']);
			foreach ($keys as $key)
			{
				if ($this->registry->call('Misc', 'is_isegment_nz_nc', array($key)))
				{
					if (isset($this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]))
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->data['links'][$key], $this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]);
						$this->data['links'][$key] =& $this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key];
					}
					else
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] =& $this->data['links'][$key];
					}
				}
				elseif (substr($key, 0, 41) === SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY)
				{
					$this->data['links'][substr($key, 41)] =& $this->data['links'][$key];
				}
				$this->data['links'][$key] = array_unique($this->data['links'][$key]);
			}
		}
		if (isset($this->data['links'][$rel]))
		{
			return $this->data['links'][$rel];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get an enclosure from the item
	 *
	 * Supports the <enclosure> RSS tag, as well as Media RSS and iTunes RSS.
	 *
	 * @since Beta 2
	 * @todo Add ability to prefer one type of content over another (in a media group).
	 * @param int $key The enclosure that you want to return.  Remember that arrays begin with 0, not 1
	 * @return SimplePie_Enclosure|null
	 */
	public function get_enclosure($key = 0, $prefer = null)
	{
		$enclosures = $this->get_enclosures();
		if (isset($enclosures[$key]))
		{
			return $enclosures[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get all available enclosures (podcasts, etc.)
	 *
	 * Su