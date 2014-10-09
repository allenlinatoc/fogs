<?php
// Declare pre-defined values
$postStudentname = '';
if ( count($_GET)>1 )
{
    PARAMS::AcceptURLQueries(true, ['MODE', 'STUDENT_ID']);
}

$MODE = PARAMS::Get('MODE', PARAMS::PAGE_SELF);
$STUDENT_ID = PARAMS::Get('STUDENT_ID', PARAMS::PAGE_SELF);


$studentName = null;
if ( !is_null($STUDENT_ID) )
{
    $sql = new DB();
    $sql
            ->Select([ 'fullname' ])
            ->From('students')
            ->Where('id='.$STUDENT_ID);
    $result = $sql->Query();
    if ( count($result)>0 )
    {
        $studentName = $result[0]['fullname'];
    }
    unset($result);
    unset($sql);
}



// Process $_POST data
if ( DATA::__HasPostData('postStudentname') )
{
    $postStudentname = ucwords(DATA::__GetPOST('postStudentname', true, true));
    // $_POST during 'ADD' mode
    if ( $MODE==='ADD' )
    {
        // verify existence
        $sql = new DB();
        $exists = $sql->__getRowCount('students', array('fullname="'.$postStudentname.'"')) > 0;
        if ( $exists )
        {
            FLASH::AddFlash('This student already exists', Index::__GetPage(), FLASH::ERROR);
        }
        else
        {
            $sql = new DB();
            $sql
                    ->InsertInto('students', [ 'user_id', 'fullname' ])
                    ->Values(array(
                        USER::Get(USER::ID), $postStudentname
                    ), [ 1 ]);
            $sql->Execute();
            if ( $sql->__IsSuccess() ) {
                FLASH::AddFlash('"'.$postStudentname.'" has been added', Index::__GetPage(), FLASH::SUCCESS);
                PARAMS::DeleteParametersByPage(array(
                    'MODE',
                    'STUDENT_ID'
                ));
                UI::RefreshPage();
            }
            else {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }   
        }
    }
    else if ( $MODE==='EDIT' )
    {
        $sql = new DB();
        $sql
                ->Update('students')
                ->Set(array(
                    'fullname' => '"'.$postStudentname.'"'
                ))
                ->Where('id='.$STUDENT_ID);
        $sql->Execute();
        if ( $sql->__IsSuccess() )
        {
            FLASH::AddFlash('Student has been updated', Index::__GetPage(), FLASH::SUCCESS);
            PARAMS::DeleteParametersByPage(array(
                'MODE', 'STUDENT_ID'
            ));
            UI::RefreshPage();
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it.', Index::__GetPage(), FLASH::ERROR);
        }
    }
}

// Remove FLASH if MODE changes
if ( $MODE=='REQ_CANCEL' )
{
    PARAMS::DeleteParametersByPage([ 'MODE', 'STUDENT_ID' ]);
    FLASH::clearFlashes();
    UI::RefreshPage();
}
else if ( $MODE=='EDIT' )
{
    $postStudentname = $studentName;
}
else if ( $MODE=='REQ_DELETE' )
{
    if ( PARAMS::__HasParameters(PARAMS::PAGE_GLOBAL, [ 'DIALOG_RESULT' ]) )
    {
        $DIALOG_RESULT = PARAMS::Get('DIALOG_RESULT');
        if ($DIALOG_RESULT == DIALOG::R_AFFIRMATIVE)
        {
            $sql = new DB();
            $sql
                    ->DeleteFrom('students')
                    ->Where('id='.$STUDENT_ID);
            $sql->Execute();
            if ( $sql->__IsSuccess() )
            {
                FLASH::AddFlash('Student '.$studentName.' has been successfully deleted', Index::__GetPage(), FLASH::SUCCESS);
            }
            else
            {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
        PARAMS::DeleteParametersByPage(array(
            'MODE', 'STUDENT_ID'
        ));
        PARAMS::DropParametersFrom(DIALOG::DIALOG_PAGENAME);
        PARAMS::DeleteParametersByPage(array(
            'DIALOG_OBJECT', 'DIALOG_RESULT'
        ), PARAMS::PAGE_GLOBAL);
        UI::RefreshPage();
    }
    // else, initialize DIALOG_WINDOW
    else
    {
        $dialog = new DIALOG('Confirm deletion of student');
        $dialog
                ->SetMessage('Are you sure you want to delete')
                ->SetPageCallback(Index::__GetPage())
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialog->ShowDialog();
    }
}


// Prepare reports of students
$sql = new DB();
$sql
        ->Select([ 'id', 'fullname' ])
        ->From('students')
        ->Where('user_id='.USER::Get(USER::ID));
$result = $sql->Query();

$report_Students = new MySQLReport();
$report_Students
        ->setReportProperties(array(
            'class' => 'table',
            'width' => '100%'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Username'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' => 
                    UI::Button('Edit', 'button', 'btn btn-info btn-sm'
                            , UI::GetPageUrl(Index::__GetPage()
                                    , array(
                                        'MODE' => 'EDIT',
                                        'STUDENT_ID' => '{1}'
                                    ))
                            , false)
                  . UI::Button('Delete', 'button', 'btn btn-danger btn-sm'
                            , UI::GetPageUrl(Index::__GetPage()
                                    , array(
                                        'MODE' => 'REQ_DELETE',
                                        'STUDENT_ID' => '{1}'
                                    ))
                            , false)
            ]
        ))
        ->setReportCellstemplate(array(
            [], [
                'valign' => 'middle'
            ], []
        ));
$report_Students
        ->loadResultdata($result)
        ->defineEmptyMessage('No student in your record');


?>