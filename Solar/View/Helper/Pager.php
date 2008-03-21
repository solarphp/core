<?php
/**
 * 
 * Helper to build a list of pager links.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Action.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_View_Helper_Pager extends Solar_View_Helper {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are...
     * 
     * `type`
     * : (string) The type of list to use; default is 'ul'. Only 'ul' and 'ol'
     *   are honored.
     * 
     * `id`
     * :(string) The CSS ID for the list.  Default is 'pager'.
     * 
     * `class`
     * : (string) The CSS class for the list. Default empty.
     * 
     * `prev`
     * : (string) The locale key for the "previous" link text.  Default is
     *   'PAGER_PREV'.
     * 
     * `next`
     * : (string) The locale key for the "next" link text.  Default is
     *   'PAGER_PREV'.
     * 
     * `prev_class`
     * : (string) The CSS class for the previous-page <a> tag. Default is
     *   'prev'.
     * 
     * `curr_class`
     * : (string) The CSS class for the current-page <a> tag. Default is 
     *   'curr'.
     * 
     * `next_class`
     * : (string) The CSS class for the next-page <a> tag. Default is 'next'.
     * 
     */
    protected $_Solar_View_Helper_Pager = array(
        'type'       => 'ul',
        'id'         => '',
        'class'      => 'pager',
        'prev'       => 'PAGER_PREV',
        'next'       => 'PAGER_NEXT',
        'prev_class' => 'prev',
        'curr_class' => 'curr',
        'next_class' => 'next',
    );
    
    /**
     * 
     * Returns a list of pager links.
     * 
     * @param array $data An associative array of data for the pager with keys
     * for 'count' (total number of items), 'pages' (number of pages), 'paging'
     * (the number of items per page), and 'page' (the current page number).
     * 
     * @param array $config An array of output config to override the default
     * config.
     * 
     * @return string
     * 
     */
    public function pager($data, $config = null)
    {
        // info for the pager
        $count  = $data['count'];
        $pages  = $data['pages'];
        $paging = $data['paging'];
        $page   = $data['page'];
        
        // output config
        $config = array_merge($this->_config, (array) $config);
        
        // make sure we have ol or ul
        $config['type'] = strtolower($config['type']);
        if ($config['type'] != 'ol') {
            $config['type'] = 'ul';
        }
        // get the base href to work with, and use str_replace on it later.
        // this will be faster than calling $uri->get() multiple times.
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->query['page'] = '__PAGE__';
        $base = $uri->get();
        
        // html we're building
        $html = array();
        
        // start the list
        $attribs = $this->_view->attribs(array(
            'id'    => $config['id'],
            'class' => $config['class'],
        ));
        
        $html[] = "<" . $this->_view->escape($config['type'])
                . $attribs . ">";
        
        // show the "prev" link?
        $html[] = "    <li>";
        if ($page > 1) {
            $href = str_replace('__PAGE__', $page - 1, $base);
            $html[] = $this->_view->action($href, 'PAGER_PREV',
                array('class' => $config['prev_class']));
        } else {
            $html[] = $this->_view->getText('PAGER_PREV');
        }
        $html[] = "</li>";

        
        // build the list of page links
        $list = $this->_getPageList($page, $pages);
        foreach ($list as $item) {
    
            if ($item == '...') {
                $html[] = "    <li>...</li>";
                continue;
            }
    
            if ($item == $page) {
                $attribs = array('class' => $config['curr_class']);
            } else {
                $attribs = array();
            }
    
            $href = str_replace('__PAGE__', $item, $base);
            $html[] = "    <li>"
               . $this->_view->action($href, (string) $item, $attribs)
               . "</li>";
        }

        // show the "next" link?
        $html[] = "    <li>";
        if ($page < $pages) {
            $href = str_replace('__PAGE__', $page + 1, $base);
            $html[] = $this->_view->action($href, 'PAGER_NEXT',
                array('class' => $config['next_class']));
        } else {
            $html[] = $this->_view->getText('PAGER_NEXT');
        }
        $html[] = "</li>";
        
        // close the list, and done
        $html[] = "</" . $this->_view->escape($config['type']) . ">";
        return implode("\n", $html);
    }
    
    /**
     * 
     * Returns a list of page-number links to use, with ellipsis as needed.
     * 
     * @param int $page The current page number.
     * 
     * @param int $pages The total number of pages.
     * 
     * @return array
     * 
     */
    protected function _getPageList($page, $pages)
    {
        // keep a list of 11 items
        $list = array();
        
        // how to show them?
        if ($pages <= 11) {
            // 11 or fewer items
            $list = range(1, $pages);
        } elseif ($page < 8) {
            // early in the list
            $list = array(
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                '...',
                $pages - 1,
                $pages,
            );
        } elseif ($page > $pages - 8) {
            // late in the list
            $list = array(
                1,
                2,
                '...',
                $pages - 7,
                $pages - 6,
                $pages - 5,
                $pages - 4,
                $pages - 3,
                $pages - 2,
                $pages - 1,
                $pages,
            );
        } else {
            // mid-list
            $list = array(
                1,
                2,
                '...',
                $page - 2,
                $page - 1,
                $page,
                $page + 1,
                $page + 2,
                '...',
                $pages - 1,
                $pages,
            );
        }
        
        // done!
        return $list;
    }
}