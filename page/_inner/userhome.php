<?php

// Disallow non-registered users
Index::__IncludeFiles('page/_includes', [ 'pan_useronly.php' ]);

// Counters
$sql = new DB();
$count = $sql->__getRowCount('students', [ 'user_id='.USER::Get(USER::ID) ]);
$count_Students = $count > 0 ? $count : '';

$sql = new DB();
$count = $sql->__getRowCount('courses', [ 'user_id='.USER::Get(USER::ID) ]);
$count_Courses = $count > 0 ? $count : '';

$sql = new DB();
$count = $sql->__getRowCount('periods', [ 'user_id='.USER::Get(USER::ID) ]);
$count_Periods = $count > 0 ? $count : '';

?>