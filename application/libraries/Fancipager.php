<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @modified  	Ulises Vázquez Espinoza
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		EllisLab Dev Team
 * 
 * Modified by Ulise Vazquez Espinoza for FanCI Tablegrid
 */
class CI_Fancipager {

	protected $base_url				= ''; // The page we are linking to
	protected $prefix				= ''; // A custom prefix added to the path.
	protected $suffix				= ''; // A custom suffix added to the path.
	protected $total_rows			= 0; // Total number of items (database results)
	protected $per_page				= 15; // Max number of items you want shown per page
	protected $num_links			= 2; // Number of "digit" links to show before/after the currently viewed page
	protected $cur_page				= 0; // The current page being viewed
	protected $use_page_numbers		= FALSE; // Use page number for segment instead of offset
	protected $first_link			= '&lsaquo; First';
	protected $next_link			= '&gt;';
	protected $prev_link			= '&lt;';
	protected $last_link			= 'Last &rsaquo;';
	protected $uri_segment			= 3;
	protected $full_tag_open		= '';
	protected $full_tag_close		= '';
	protected $first_tag_open		= '';
	protected $first_tag_close		= '&nbsp;';
	protected $last_tag_open		= '&nbsp;';
	protected $last_tag_close		= '';
	protected $first_url			= ''; // Alternative URL for the First Page.
	protected $cur_tag_open			= '&nbsp;<strong>';
	protected $cur_tag_close		= '</strong>';
	protected $next_tag_open		= '&nbsp;';
	protected $next_tag_close		= '&nbsp;';
	protected $prev_tag_open		= '&nbsp;';
	protected $prev_tag_close		= '';
	protected $num_tag_open			= '&nbsp;';
	protected $num_tag_close		= '';
	protected $page_query_string	= FALSE;
	protected $query_string_segment = 'per_page';
	protected $display_pages		= TRUE;
	protected $anchor_class			= '';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct($params = array())
	{
		$this->initialize($params);
		log_message('debug', "Pagination Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	public function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}

		if ($this->anchor_class != '')
		{
			$this->anchor_class = 'class="'.$this->anchor_class.'" ';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links($uri_params)
	{
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}

		// Set the base page index for starting page number
		if ($this->use_page_numbers)
		{
			$base_page = 1;
		}
		else
		{
			$base_page = 0;
		}

		// Determine the current page number.
		$CI =& get_instance();

		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			if ($CI->input->get($this->query_string_segment) != $base_page)
			{
				$this->cur_page = $CI->input->get($this->query_string_segment);

				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		else
		{
			if ($CI->uri->segment($this->uri_segment) != $base_page)
			{
				$this->cur_page = $CI->uri->segment($this->uri_segment);

				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		
		// Set current page to 1 if using page numbers instead of offset
		if ($this->use_page_numbers AND $this->cur_page == 0)
		{
			$this->cur_page = $base_page;
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			show_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = $base_page;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->use_page_numbers)
		{
			if ($this->cur_page > $num_pages)
			{
				$this->cur_page = $num_pages;
			}
		}
		else
		{
			if ($this->cur_page > $this->total_rows)
			{
				$this->cur_page = ($num_pages - 1) * $this->per_page;
			}
		}

		$uri_page_number = $this->cur_page;
		
		if ( ! $this->use_page_numbers)
		{
			$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);
		}

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			$this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
		}
		else
		{
			$this->base_url = rtrim($this->base_url, '/') .'/';
		}

		$per_page = $this->per_page;
		// Add the new parameters to the url
		$this->per_page = $this->per_page."-".$uri_params["field_order"]."-".$uri_params["order"]."-".$uri_params["like_string"]."-".$uri_params["vars_url"];
		
		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))
		{
			$first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
			$output .= $this->first_tag_open.'<a class="btn" title="Primero" '.$this->anchor_class.'href="'.$first_url.'0-'.$this->per_page.'">'.$this->first_link.'</a>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  ($this->prev_link !== FALSE AND $this->cur_page != 1)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number - 1;
			}
			else
			{
				$i = $uri_page_number - $this->per_page;
			}

			if ($i == 0 && $this->first_url != '')
			{
				$output .= $this->prev_tag_open.'<a class="btn" title="Anterior" '.$this->anchor_class.'href="'.$this->first_url.'-0-'.$this->per_page.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
			else
			{
				$i = ($i == 0) ? '0' : $this->prefix.$i.$this->suffix;
				$output .= $this->prev_tag_open.'<a class="btn" title="Anterior" '.$this->anchor_class.'href="'.$this->base_url.$i.'-'.$this->per_page.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}

		}

		// Render the pages
		if ($this->display_pages !== FALSE)
		{
			// Write the digit links
			for ($loop = $start -1; $loop <= $end; $loop++)
			{
				if ($this->use_page_numbers)
				{
					$i = $loop;
				}
				else
				{
					$i = ($loop * $this->per_page) - $this->per_page;
				}

				if ($i >= $base_page)
				{
					if ($this->cur_page == $loop)
					{
						$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
					}
					else
					{
						$n = ($i == $base_page) ? '' : $i;

						if ($n == '' && $this->first_url != '')
						{
							$output .= $this->num_tag_open.'<a class="btn" '.$this->anchor_class.'href="'.$this->first_url.'"><span class="label">'.$loop.'<span></a>'.$this->num_tag_close;
						}
						else
						{
							$n = ($n == '') ? '0' : $this->prefix.$n.$this->suffix;

							$output .= $this->num_tag_open.'<a class="btn" '.$this->anchor_class.'href="'.$this->base_url.$n.'-'.$this->per_page.'"><span class="label">'.$loop.'<span></a>'.$this->num_tag_close;
						}
					}
				}
			}
		}

		// Render the "next" link
		if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $this->cur_page + 1;
			}
			else
			{
				$i = ($this->cur_page * $this->per_page);
			}

			$output .= $this->next_tag_open.'<a class="btn" title="Siguiente" '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'-'.$this->per_page.'">'.$this->next_link.'</a>'.$this->next_tag_close;
		}

		// Render the "Last" link
		if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $num_pages;
			}
			else
			{
				$i = (($num_pages * $this->per_page) - $this->per_page);
			}
			$output .= $this->last_tag_open.'<a class="btn" title="&Uacute;ltimo" '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'-'.$this->per_page.'">'.$this->last_link.'</a>'.$this->last_tag_close;
		}
		
		$params = $uri_params["field_order"]."-".$uri_params["order"]."-".$uri_params["like_string"]."-".$uri_params["vars_url"];
		$output .= '<a onclick="$(\'.btn.hide\').toggle()" href="javascript:void(0)" class="btn" title="N&uacute;mero de registros mostrados por p&aacute;gina.">
					Registros por p&aacute;gina:&nbsp;&nbsp;'.$per_page.'</a>';
		$output .= '	<a href="'.$this->base_url.$this->prefix.'/0-10-'.$params.'" class="btn hide">10</a>
						<a href="'.$this->base_url.$this->prefix.'/0-20-'.$params.'" class="btn hide">20</a>
						<a href="'.$this->base_url.$this->prefix.'/0-50-'.$params.'" class="btn hide">50</a>
						<a href="'.$this->base_url.$this->prefix.'/0-100-'.$params.'" class="btn hide">100</a>
						<a href="'.$this->base_url.$this->prefix.'/0-500-'.$params.'" class="btn hide">500</a>
						<a href="'.$this->base_url.$this->prefix.'/0-all-'.$params.'" class="btn hide">Todas</a>
						';
		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}
// END Fancipager Class

/* End of file Fancipager.php */
/* Location: ./application/libraries/Fancipager.php */
