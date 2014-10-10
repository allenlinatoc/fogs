<?php

if ( count($_GET)>1 )
{
    PARAMS::AcceptURLQueries(true, [ 'COURSE_ID', 'MODE', 'TARGET_ID' ]);
}

// Page Parameters preparation
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

// Pre-define FORM PARAMETERJS
# [ form: Grading_Components ]
$postGcname = '';
$postGcpercentage = '0';


// ---------- SWITCH ( Modes )
$MODE = PARAMS::Get('MODE', PARAMS::PAGE_SELF);
$TARGET_ID = PARAMS::Get('TARGET_ID', PARAMS::PAGE_SELF);

if ( $MODE==='ADD-STUDENT' )
{
    // do nothing, form will automatically show there
}
else if ( $MODE==='REMOVE-STUDENT' && $TARGET_ID!==null )
{
    // get student name
    $sql = new DB();
    $sql
            ->Select([ 'students.fullname' ])
            ->From('students,d_student_course')
            ->Where('students.id = d_student_course.student_id '
                    . 'AND d_student_course.id = '.$TARGET_ID);
    $studentName = $sql->Query()[0]['fullname'];
    
    if ( PARAMS::__HasParameters(PARAMS::PAGE_GLOBAL, [ 'DIALOG_RESULT' ]) )
    {
        $DIALOG_RESULT = PARAMS::Get('DIALOG_RESULT');
        if ( $DIALOG_RESULT === DIALOG::R_AFFIRMATIVE )
        {
            $sql = new DB();
            $sql
                    ->DeleteFrom('d_student_course')
                    ->Where('id='.$TARGET_ID);
            $is_success = $sql->Execute()->__IsSuccess();
            
            if ( $is_success )
            {
                FLASH::AddFlash('"'.$studentName.'" has been successfully removed from this course', Index::__GetPage());
            }
            else
            {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
        PARAMS::DeleteParametersByPage([ 'MODE', 'TARGET_ID' ]);
        PARAMS::DeleteParametersByPage([ 'DIALOG_RESULT','DIALOG_OBJECT' ], PARAMS::PAGE_GLOBAL);
        PARAMS::DropParametersFrom(DIALOG::DIALOG_PAGENAME);
        UI::RefreshPage();
    }
    else
    {
        $dialog = new DIALOG('Confirm removal of student');
        $dialog
                ->SetMessage('Are you want to remove <b>'.$studentName.'</b> from the list?'
                        . '<br>'
                        . '<i>This will remove all his/her data in this course.</i>')
                ->SetPageCallback(Index::__GetPage())
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialog->ShowDialog();
    }
}
else if ( $MODE==='ADD-GCOMPONENT' )
{
    // do nothing, form will just show up on view
    
}
else if ( $MODE==='EDIT-GCOMPONENT' && $TARGET_ID!==null )
{
    $componentinfo = DB::__getRecord('components', [ 'id='.$TARGET_ID ])[0];
    $postGcname = $componentinfo['name'];
    $postGcpercentage = $componentinfo['percentage'];
}
else if ( $MODE==='REMOVE-GCOMPONENT' && $TARGET_ID!==null )
{
    $componentinfo = DB::__getRecord('components', [ 'id='.$TARGET_ID ])[0];
    $componentname = $componentinfo['name'];
    $sql = new DB();
    $sql
            ->DeleteFrom('components')
            ->Where('id='.$TARGET_ID);
    $sql->Execute();
    if ( $sql->__IsSuccess() )
    {
        FLASH::AddFlash('<b>'.$componentname.'</b> has been removed from grading components list', Index::__GetPage());
        PARAMS::DeleteParametersByPage([ 'MODE', 'TARGET_ID' ]);
        UI::RefreshPage();
    }
    else
    {
        FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
    }
}
else if ( $MODE==='RESETMODE' )
{
    PARAMS::DeleteParametersByPage(array(
        'MODE', 'TARGET_ID'
    ));
    FLASH::clearFlashes();
    UI::RefreshPage();
}


// ---------------- Process $_POST data
if ( DATA::__HasPostData() )
{
    if ( $MODE==='ADD-STUDENT' )
    {
        UI::NewLine(3);
        $postStudentIds = $_POST['postStudentIds'];
        foreach ($postStudentIds as $id)
        {
            $sql = new DB();
            $sql
                    ->InsertInto('d_student_course', [ 'course_id', 'student_id' ])
                    ->Values(array(
                        $COURSE_ID, $id
                    ));
            $is_success = $sql->Execute()->__IsSuccess();
        }
        if ( $is_success )
        {
            FLASH::AddFlash('Students have been successfully added to this course.', Index::__GetPage());
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
        }
        // break-out MODE
        PARAMS::DeleteParametersByPage(array('MODE'));
        UI::RefreshPage();
    }
    else if ( $MODE==='ADD-GCOMPONENT' )
    {
        $postGcname = DATA::__GetPOST('postGcname', true, true);
        $postGcpercentage = DATA::__GetPOST('postGcpercentage', true, true);
        $sql = new DB();
        $sql
                ->InsertInto('components', [ 'course_id', 'name', 'percentage' ])
                ->Values(array(
                    $COURSE_ID,
                    $postGcname,
                    $postGcpercentage
                ), [ '1' ]);
        $sql->Execute();
        if ( $sql->__IsSuccess() )
        {
            FLASH::AddFlash('Component <b>'.$postGcname.'</b> has been added to grading components', Index::__GetPage());
            PARAMS::DeleteParametersByPage([ 'MODE', 'TARGET_ID' ]);
            UI::RefreshPage();
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
        }
    }
    else if ( $MODE==='EDIT-GCOMPONENT' )
    {
        $postGcname = DATA::__GetPOST('postGcname', true, true);
        $postGcpercentage = DATA::__GetPOST('postGcpercentage', true, true);
        
        $sql = new DB();
        $sql
                ->Update('components')
                ->Set(array(
                    'name' => '"'.$postGcname.'"',
                    'percentage' => $postGcpercentage
                ))
                ->Where('id='.$TARGET_ID);
        $sql->Execute();
        if ( $sql->__IsSuccess() )
        {
            FLASH::AddFlash('Component <b>'.$postGcname.'</b> has been updated', Index::__GetPage());
            PARAMS::DeleteParametersByPage([ 'MODE', 'TARGET_ID' ]);
            UI::RefreshPage();
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
        }
    }
}

# --------------------- All reports will fall here -----------------------------

// [REPORTS] Students list
require 'page/_reports/rpt_StudentsInList.php';

// [REPORTS] Grading components list
require 'page/_reports/rpt_GradingComponents.php';

?>