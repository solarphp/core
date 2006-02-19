        <h2><?php $this->eprint($this->locale('TAG_LIST')) ?></h2>
        <table border="0" cellspacing="2" cellpadding="0"><?php
            
            // get a clean link
            $link->import();
            
            // clear out pathinfo, but reset the page to 1
            $link->clearInfo();
            $link->setQuery('page', 1);
            
            // set up baseline path info for a 'user' or 'tag' request?
            if ($this->owner_handle) {
                $baseInfo = "bookmarks/user/{$this->owner_handle}";
            } else {
                $baseInfo = "bookmarks/tag";
            }
            
            // build a series of table rows as links to tags
            $tmp = array();
            foreach ($this->tags_in_use as $tag => $count) {
                $link->setInfoString("$baseInfo/$tag");
                $tmp[] = "<tr><td align=\"right\">$count</td><td>" . $this->ahref($link->export(), $tag) . "</td></tr>";
            }
            echo implode("\n", $tmp);
        ?></table>
