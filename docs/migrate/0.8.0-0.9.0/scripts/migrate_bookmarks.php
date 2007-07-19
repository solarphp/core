<?php
require_once 'Solar.php';
Solar::start();
$select = Solar::object('Solar_Sql_Select');
$select->from('sc_bookmarks', '*');
$select->order('id');
$result = $select->fetch('all');

$bookmarks = Solar::object('Solar_Cell_Bookmarks');

foreach ($result as $row) {
    $data = array(
        'created' => $row['ts_new'],
        'updated' => $row['ts_mod'],
        'owner_handle' => $row['user_id'],
        'editor_handle' => $row['user_id'],
        'uri' => $row['uri'],
        'subj' => $row['title'],
        'summ' => $row['descr'],
        'tags' => $row['tags'],
        'rank' => $row['rank'],
    );
    $result = $bookmarks->insert($data);
    Solar::dump($result);
}

Solar::stop();
?>