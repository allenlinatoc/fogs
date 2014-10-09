<?php

// Disallow non-registered users
Index::__IncludeFiles('page/_includes', [ 'pan_useronly.php' ]);

// Counters
$sql = new DB();
$count = $sql->__getRowCount('students');
$count_Students = $count > 0 ? $count : '';

$sql = new DB();
$count = $sql->__getRowCount('students');
$count_Courses = $count > 0 ? $count : '';

?>