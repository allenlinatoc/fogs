<?php

// Pre-defined form values
$postCoursename = '';


if ( count($_GET)>0 )
{
    PARAMS::AcceptURLQueries(true, [ 'MODE', 'COURSE_ID' ]);
}

$MODE = PARAMS::Get('MODE', PARAMS::PAGE_SELF);
$COURSE_ID = PARAMS::Get('COURSE_ID', PARAMS::PAGE_SELF);
// get name of this COURSE_ID
if ( $COURSE_ID!==null ) {
    $sql = new DB();
    $courseName = $sql->__getRecord('courses', [ 'id='.$COURSE_ID ])[0]['name'];
}

// Process $_POST data
if ( DATA::__HasPostData('postCoursename') )
{
    $postCoursename = DATA::__GetPOST('postCoursename', true, true);
    // ADD mode
    if ( $MODE=='ADD' )
    {
        // check for existence
        $sql = new DB();
        $exists = $sql->__getRowCount('courses'
                , array(
                    'name LIKE "'.$postCoursename.'"',
                    'user_id='.USER::Get(USER::ID)
                )) > 0;
        if ($exists)
        {
            FLASH::AddFlash('This course already exists', Index::__GetPage(), FLASH::ERROR);
        }
        else
        {
            $sql = new DB();
            $sql
                    ->InsertInto('courses'
                            , array(
                                'name', 'user_id'
                            ))
                    ->Values(array(
                        $postCoursename, USER::Get(USER::ID)
                    ), [ 0 ]);
            $sql->Execute();
            if ( $sql->__IsSuccess() )
            {
                FLASH::AddFlash('Course "'.$postCoursename.'" has been successfully created', Index::__GetPage());
                PARAMS::DeleteParametersByPage(array(
                    'MODE', 'COURSE_ID'
                ));
                UI::RefreshPage();
            }
            else
            {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
    }
    // EDIT mode
    else if ( $MODE=='EDIT' )
    {
        $sql = new DB();
        $sql
                ->Update('courses')
                ->Set(array(
                    'name' => '"'.$postCoursename.'"'
                ))
                ->Where('id='.$COURSE_ID);
        $sql->Execute();
        if ( $sql->__IsSuccess() )
        {
            FLASH::AddFlash('Course has been updated to "'.$postCoursename.'"', Index::__GetPage());
            PARAMS::DeleteParametersByPage([ 'MODE', 'EDIT' ]);
            UI::RefreshPage();
        }
        else {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
        }
    }
}


// SWITCH Between MODES
if ( $MODE=='REQ_CANCEL' )
{
    // cancel
    PARAMS::DeleteParametersByPage(array(
        'MODE', 'COURSE_ID'
    ));
    FLASH::clearFlashes();
    UI::RefreshPage();
}
else if ( $MODE=='EDIT' && !DATA::__HasPostData('postCoursename') )
{
    // edit
    $postCoursename = $courseName;
}
else if ( $MODE=='REQ_DELETE' )
{
    // delete
    if ( PARAMS::__HasParameters(PARAMS::PAGE_GLOBAL, [ 'DIALOG_RESULT' ]) )
    {
        $DIALOG_RESULT = PARAMS::Get('DIALOG_RESULT');
        if ( $DIALOG_RESULT == DIALOG::R_AFFIRMATIVE )
        {
            $sql = new DB();
            $sql
                    ->DeleteFrom('courses')
                    ->Where('id='.$COURSE_ID);
            $sql->Execute();
            if ( $sql->__IsSuccess() )
            {
                FLASH::AddFlash('<b>'.$courseName.'</b> has been successfully deleted', Index::__GetPage());
            }
            else {
                FLASH::AddFlash('Deletion of course <b>'.$courseName.'</b> failed, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
        PARAMS::DeleteParametersByPage(array(
            'MODE', 'COURSE_ID'
        ));
        PARAMS::DropParametersFrom(DIALOG::DIALOG_PAGENAME);
        PARAMS::DeleteParametersByPage(array(
            'DIALOG_OBJECT', 'DIALOG_RESULT'
        ), PARAMS::PAGE_GLOBAL);
        UI::RefreshPage();
    }
    else
    {
        $dialog = new DIALOG('Confirm deletion of course');
        $dialog
                ->SetMessage('Are you sure you want to delete course <b>'.$courseName.'</b>?')
                ->SetPageCallback(Index::__GetPage())
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialog->ShowDialog();
    }
}


// Preparing reports for "Courses"
$sql = new DB();
$sql
        ->Select([ 'id', 'name' ])
        ->From('courses')
        ->Where('user_id='.USER::Get(USER::ID));
$result = $sql->Query();

$report_Courses = new MySQLReport();
$report_Courses
        ->setReportProperties(array(
            'width' => '100%',
            'class' => 'table'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_COURSE',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Course name',
                'DEFAULT' => UI::makeLink(UI::GetPageUrl('course', [ 'COURSE_ID'=>'{1}' ]), '{2}', true)
            ], [
                'CAPTION' => 'Actions',
                'DEFAULT' =>
                    UI::Button('Edit', 'button', 'btn btn-info', UI::GetPageUrl(Index::__GetPage()
                            , array(
                                'MODE' => 'EDIT',
                                'COURSE_ID' => '{1}'
                            ))
                        , false)
                  . UI::Button('Delete', 'button', 'btn btn-danger', UI::GetPageUrl(Index::__GetPage()
                            , array(
                                'MODE' => 'REQ_DELETE',
                                'COURSE_ID' => '{1}'
                            ))
                        , false)
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], [], []
        ))
        ->loadResultdata($result)
        ->defineEmptyMessage('No existing courses yet');

?>