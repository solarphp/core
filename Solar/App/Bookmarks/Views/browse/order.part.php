        <h2><?php $this->eprint($this->locale('ORDERED_BY')) ?></h2>
        <p><?php
            $tmp = array(
                'created'      => $this->locale('ORDER_CREATED'),
                'created_desc' => $this->locale('ORDER_CREATED_DESC'),
                'rank'         => $this->locale('ORDER_RANK'),
                'rank_desc'    => $this->locale('ORDER_RANK_DESC'),
                'tags'         => $this->locale('ORDER_TAGS'),
                'tags_desc'    => $this->locale('ORDER_TAGS_DESC'),
                'subj'         => $this->locale('ORDER_SUBJ'),
                'subj_desc'    => $this->locale('ORDER_SUBJ_DESC'),
            );
            
            // refresh the base link
            $link->import();
            
            // add links
            foreach ($tmp as $key => $val) {
                if (Solar::get('order', 'created_desc') == $key) {
                    echo "<strong>";
                    $this->eprint($val);
                    echo "</strong><br />\n";
                } else {
                    $link->setQuery('order', $key);
                    echo $this->ahref($link->export(), $val) . "<br />\n";
                }
            }
        ?></p>
