
<a href="index.html">Home</a><br><br>

<?php

require 'database.php';

$pdo = Database::connect();

$sql = 'SELECT * FROM upload '
    . 'ORDER BY BINARY filename ASC;';

foreach ($pdo->query($sql) as $row) {
    $id = $row['id'];
    $sql = "SELECT * FROM upload where id=$id";
    echo $row['id'] . ' - ' . $row['fileName'] . '<br>'
        . '<img width="200" src="data:image/jpeg;base64,'
        . base64_encode( $row['content'] ).'"/>'
        . '<br><br>';
}

Database::disconnect();