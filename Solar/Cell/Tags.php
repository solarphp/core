<?php

/**
* 
* Component module to search for tags on related items.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Tags
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Have the Entity class available for extension.
*/
Solar::loadClass('Solar_Sql_Entity');


/**
* 
* Component module to search for tags on related items.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Tags
* 
*/

class Solar_Cell_Tags extends Solar_Sql_Entity {
	
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys are:
	* 
	* locale => (string) path to the locale files
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'locale' => 'Solar/Cell/Tags/Locale/',
	);
	
	
	/**
	* 
	* Initialize the custom schema.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function getSchema()
	{
		// -------------------------------------------------------------
		// 
		// table name
		// 
		
		$schema['tbl'] = 'sc_tags';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// the tag applies to an item in this related table
		$schema['col']['rel'] = array(
			'type'     => 'varchar',
			'size'     => 64,
			'require'  => true,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_REL'),
					'/^[a-z][a-z0-9_]*$/'
				),
			),
		);
		
		// the tag applies to this ID in the related table
		$schema['col']['rel_id'] = array(
			'type'    => 'int',
		);
		
		// a single tag for the related item
		$schema['col']['tag'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_TAG'),
					'/[A-Za-z0-9_]*/'
				),
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'rel'    => 'normal',
			'rel_id' => 'normal',
			'tag'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		$schema['qry']['refresh'] = array(
			'select' => 'tag',
			'where'  => 'rel = :rel AND rel_id = :rel_id',
			'fetch'  => 'Col'
		);
		
		// placeholders for the relatedFetch() method.  will
		// redefine these on-the-fly.
		$schema['qry']['lookup'] = array();
		$schema['rel']['lookup'] = '';
		
		// -------------------------------------------------------------
		// 
		// done!
		// 
		
		return $schema;
	}
	
	
	/**
	* 
	* Refresh the tags stored for a related item.
	* 
	* Performs a diff on the existing tags so that only changes to the tag
	* set are inserted and deleted.
	* 
	* @access public
	* 
	* @param string $rel The name of the related table, e.g. 'sc_bookmarks'.
	* 
	* @param int $rel_id The ID of the item in the related table.
	* 
	* @param string|array $tags The tags to be stored for the related item.
	* 
	* @return mixed Void on success, or a Solar_Error stack of errors.
	* 
	*/
	
	public function refresh($rel, $rel_id, $tags)
	{
		// error collection object
		$err = Solar::object('Solar_Error');
		
		// fix up the tags param
		$tags = $this->fixString($tags);
		
		// the new set of tags as an array
		$new = explode(' ', $tags);
		
		// the old set of tags
		$tmp = array(
			'rel' => $rel,
			'rel_id' => $rel_id
		);
		$old = $this->selectFetch('refresh', $tmp);
		
		// get a diff list so we can insert and delete.
		$diff = $this->diff($old, $new);
		
		// are there tags to delete?
		if ($diff['del']) {
		
			// always filter by related table and related id
			$where = 'rel = ' . $this->quote($rel) . 
				' AND rel_id = ' . $this->quote($rel_id);
			
			// create a list of quoted tag names from the diff deletes
			$tmp = array();
			foreach ($diff['del'] as $tag) {
				$tmp[] = $this->quote($tag);
			}
			
			// delete the un-needed tags
			$where .= ' AND (tag = ' . implode(' OR tag = ', $tmp) . ')';
			$result = $this->delete($where);
			if (Solar::isError($result)) {
				$err->push($result);
			}
		}
		
		// are there tags to insert?
		if ($diff['ins']) {
			foreach ($diff['ins'] as $tag) {
			
				// don't insert blanks
				if (trim($tag) == '') {
					continue;
				}
				
				// ok, not a blank, insert it.
				$data = array(
					'rel'    => $rel,
					'rel_id' => $rel_id,
					'tag'    => $tag
				);
				
				$result = $this->insert($data);
				
				if (Solar::isError($result)) {
					$err->push($result);
				}
			}
		}
		
		// done!
		if ($err->count() > 0) {
			return $err;
		}
	}
	
	
	/**
	* 
	* Determines the diff (delete/insert matrix) on two sets of tags.
	* 
	* <code>
	* $old = array('a', 'b', 'c');
	* $new = array('c', 'd', 'e');
	* 
	* $diff = $this->diff($old, $new);
	* 
	* // $diff['del'] == array('a', 'b');
	* // $diff['ins'] == array('d', 'e');
	* // 'c' doesn't show up becuase it's present in both sets
	* </code>
	* 
	* @access protected
	* 
	* @param array $old The old (previous) set of tags.
	* 
	* @param array $new The new (current) set of tags.
	* 
	* @return array An associative array of two keys: 'del' (where the
	* value is a sequential array of tags removed from the old set)
	* and 'ins' (where the value is a sequential array of tags added to
	* the new set).
	* 
	*/
	
	protected function diff($old, $new)
	{
		// find intersections first
		$intersect = array_intersect($old, $new);
		
		// now flip arrays so we can unset easily
		$old = array_flip($old);
		$new = array_flip($new);
		
		// remove intersections from each array
		foreach ($intersect as $val) {
			unset($old[$val]);
			unset($new[$val]);
		}
		
		// keys remaining in $old are to be deleted,
		// keys remaining in $new are to be added
		return array(
			'del' => array_keys($old),
			'ins' => array_keys($new)
		);
	}
	
	
	/**
	* 
	* Does a lookup on the related table returning specified columns.
	* 
	* This more-optimized search algorithm is derived from...
	* 
	* 	http://www.pui.ch/phred/archives/2005/04/tags-database-schemas.html
	* 	http://www.petercooper.co.uk/archives/000648.html
	* 	http://www.bigbold.com/snippets/posts/show/32
	* 	
	* SELECT
	* 	p.*
	* FROM
	* 	posts_tags pt,
	* 	posts p,
	* 	tags t
	* WHERE
	* 	pt.tag_id = t.id AND (
	* 		t.name = '" + tags.uniq.join ('\' OR t.name=\'') + "'
	* 	) AND p.id=pt.post_id
	* GROUP BY
	* 	p.id
	* HAVING
	* 	COUNT(p.id) = " + tags.uniq.length.to_s
	* 
	* 
	* 
	* (intersection/and...)
	* 
	* SELECT *
	* FROM sc_tags
	* WHERE
	* 	rel = 'sc_bookmarks' 
	* 	AND tag IN ('this','that','other') -- count = 3
	* GROUP BY
	* 	rel_id
	* HAVING
	* 	COUNT(rel_id) = 3 -- count = 3
	* 
	* (union/or: drop the HAVING clause)
	* 
	* 
	* @access public
	* 
	* @param string $rel The name of the related table, e.g. 'sc_bookmarks'.
	* 
	* @param string|array $tags Fetch related rows with all of these tags.
	* This can be a space-separated list or a sequential array.
	* 
	* @param string|array $cols The columns to select from the related table.
	* 
	* @param string $where An additional WHERE filter.
	* 
	* @param string $having An additional HAVING filter.
	* 
	* @param string $order Order results by this ORDER clause.
	* 
	* @param int $page The page number to fetch.
	* 
	* @return array An array of rows with the requested tags.
	* 
	*/
	
	public function relatedFetch($rel, $tags, $cols = '*', $where = null,
		$having = null, $order = null, $page = null)
	{
		// redefine the lookup relation on the fly so that it points to 
		// the requested related table.
		$this->schema['rel']['lookup'] = array(
			'table' => 'sc_tags',
			'on'    => array(
				'rel'    => $this->quote($rel),
				'rel_id' => "$rel.id",
			),
		);
		
		// redefine the lookup query on the fly
		$qry =& $this->schema['qry']['lookup'];
		
		$qry['select'] = $cols;
		$qry['from'] = $rel;
		$qry['join'] = 'lookup';
	
		// add the tag WHERE.  convert $tags to array...
		if (! is_array($tags)) {
			$tags = $this->fixString($tags);
			$tags = explode(' ', $tags);
		}
		
		// ... get the tags as part of an IN() list ...
		$taglist = array();
		foreach ($tags as $tag) {
			if (trim($tag) != '') {
				// quote and add to the list of tags
				$taglist[] = $this->quote($tag);
			}
		}
		
		// and build the WHERE clause.
		$qry['where'] = 'sc_tags.tag IN (' . implode(', ', $taglist) . ')';
		
		// add the grouping; this lets us count the result hits by single
		// related IDs.
		$qry['group'] = 'sc_tags.rel_id';
		
		// add the HAVING clause that counts the tag return;
		// this is what lets us avoid LIKE-AND queries.
		$qry['having'] = " COUNT(sc_tags.rel_id) = " . count($taglist);
		
		// always fetch all results
		$qry['fetch'] = 'All';
		
		// count only related IDs
		$qry['count'] = "sc_tags.rel_id";
		
		// now build a special lookup-count query; can't use a normal
		// countPages() because of the table relations.
		$this->schema['qry']['lookup_count'] = $qry;
		$this->schema['qry']['lookup_count']['select'] = 'COUNT(sc_tags.rel_id)';
		
		// done redefining the query. return the fetch results.
		return $this->selectFetch('lookup', $where, $having, $order, $page);
	}
	
	
	/**
	* 
	* Gets a row-count and page-count of related items.
	* 
	* This method is lazy and dumb, it only counts on the most-recent
	* relatedFetch().
	* 
	* @access public
	* 
	* @param string $where The WHERE clause filter.
	* 
	* @param string $having The HAVING clause filter.
	* 
	* @return array An associative array with keys 'count' (the number of related
	* items) and 'pages' (the number of pages needed for the items).
	* 
	*/
	
	public function relatedCountPages($where = null, $having = null)
	{
		$result = $this->selectResult('lookup_count', $where, $having);
		$count = $result->numRows();
		unset($result);
		
		$pages = 0;
		if ($count > 0) {
			$pages = ceil($count / $this->config['rows_per_page']);
		}
		
		// done!
		return array(
			'count' => $count,
			'pages' => $pages
		);
		
	}
	
	
	/**
	* 
	* Fixes tag strings for the queries so they're in proper format.
	* 
	* Converts + to space, trims extra space, and removes duplicate tags.
	* 
	* @access public
	* 
	* @param string|array A space-separated string of tags, or a sequential
	* array of tags.
	* 
	* @return string A space-separated string of tags.
	* 
	*/
	
	public function fixString($tags)
	{
		// convert to array from string?
		if (! is_array($tags)) {
			
			// convert all "+" to spaces (this is for URL values)
			$tags = str_replace('+', ' ', $tags);
			
			// trim all surrounding spaces and extra spaces
			$tags = trim($tags);
			$tags = preg_replace('/[ ]{2,}/', ' ', $tags);
			
			// convert to array for easy processing
			$tmp = explode(' ', $tags);
		}
		
		// make sure each tag is unique (no double-entries)
		$tmp = array_unique($tmp);
		
		// return as space-separated text
		return implode(' ', $tmp);
	}
}
?>