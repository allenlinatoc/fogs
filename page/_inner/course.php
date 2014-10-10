<?php

if ( count($_GET)>1 )
{
    PARAMS::AcceptURLQueries(true, [ 'COURSE_ID' ]);
}

$COURSE_ID = PARAMS::Get('COURSE_ID', PARAMS::PAGE_SELF);
$COURSE_INFO = array();
if ( $COURSE_ID===null )
{
    // if no COURSE_ID is supplied, throw user to other page
    UI::RedirectTo('manage-courses');
}
else
{
    $sql = new DB();
    $sql
            ->Select()
            ->From('courses')
            ->Where('id='.$COURSE_ID);
    $result = $sql->Query();
    if ( count($result)>0 )
    {
        $COURSE_INFO = $result[0];
    }
}


?>