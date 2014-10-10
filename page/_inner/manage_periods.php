<?php

$postPeriodname = '';
$postPercentage = 0;

if ( count($_GET)>1 )
{
    PARAMS::AcceptURLQueries(true, [ 'MODE','PERIOD_ID' ]);
}

$MODE = PARAMS::Get('MODE', PARAMS::PAGE_SELF);
$PERIOD_ID = PARAMS::Get('PERIOD_ID', PARAMS::PAGE_SELF);
if ( $PERIOD_ID !== null )
{
    $periodName = DB::__getRecord('periods', [ 'id='.$PERIOD_ID ])[0]['name'];
}

// Processing Parameter actions
if ( $MODE==='REQ_CANCEL' )
{
    FLASH::clearFlashes();
    PARAMS::DeleteParametersByPage([ 'MODE', 'PERIOD_ID' ]);
    UI::RefreshPage();
}
else if ( $MODE==='EDIT' )
{
    $sql = new DB();
    $sql
            ->Select()
            ->From('periods')
            ->Where('id='.$PERIOD_ID);
    $result = $sql->Query()[0];
    $postPeriodname = $result['name'];
    $postPercentage = $result['percentage'];
}
else if ( $MODE=='REQ_DELETE' )
{
    if ( PARAMS::__HasParameters(PARAMS::PAGE_GLOBAL, [ 'DIALOG_RESULT' ]) )
    {
        $DIALOG_RESULT = PARAMS::Get('DIALOG_RESULT');
        if ( $DIALOG_RESULT===DIALOG::R_AFFIRMATIVE )
        {
            $sql = new DB();
            $sql
                    ->DeleteFrom('periods')
                    ->Where('id='.$PERIOD_ID);
            $is_success = $sql->Execute()->__IsSuccess();
            if ( $is_success )
            {
                FLASH::AddFlash('Grading period "'.$periodName.'" has been deleted', Index::__GetPage());
            }
            else
            {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
        PARAMS::DeleteParametersByPage([ 'MODE', 'PERIOD_ID' ]);
        PARAMS::DeleteParametersByPage([ 'DIALOG_RESULT', 'DIALOG_OBJECT' ], PARAMS::PAGE_GLOBAL);
        PARAMS::DropParametersFrom(DIALOG::DIALOG_PAGENAME);
        UI::RefreshPage();
    }
    else
    {
        $dialog = new DIALOG('Confirm deletion of Grading period');
        $dialog
                ->SetMessage('Are you sure you want to delete <b>'.$periodName.'</b> grading period?')
                ->SetPageCallback(Index::__GetPage())
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialog
                ->ShowDialog();
    }
}


// Processing $_POST data
if ( DATA::__HasPostData([ 'postPeriodname', 'postPercentage' ]) )
{
    $postPeriodname = DATA::__GetPOST('postPeriodname', true, true);
    $postPercentage = DATA::__GetPOST('postPercentage', true, true);
    
    if ( $MODE=='ADD' )
    {
        $exists = DB::__exists('periods', array(
            'name LIKE '.$postPeriodname
        ));
        if ( $exists )
        {
            FLASH::AddFlash('This grading period already exists', Index::__GetPage(), FLASH::ERROR);
        }
        else
        {
            $sql = new DB();
            $sql
                    ->InsertInto('periods', [ 'user_id', 'name', 'percentage' ])
                    ->Values(array(
                        USER::Get(USER::ID),
                        $postPeriodname,
                        $postPercentage
                    ), [ 1 ]);
            $sql->Execute();
            $is_success = $sql->__IsSuccess();
            if ( $is_success )
            {
                FLASH::AddFlash('Grading period "'.$postPeriodname.'" has been added', Index::__GetPage());
                PARAMS::DestroyOwn();
                UI::RefreshPage();
            }
            else
            {
                FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR);
            }
        }
    }
    else if ( $MODE=='EDIT' )
    {
        $sql = new DB();
        $sql
                ->Update('periods')
                ->Set(array(
                    'name' => '"'.$postPeriodname.'"',
                    'percentage' => $postPercentage
                ))
                ->Where('id='.$PERIOD_ID);
        $is_success = $sql->Execute()->__IsSuccess();
        if ( $is_success )
        {
            FLASH::AddFlash('"'.$postPeriodname.'" has been successfully updated', Index::__GetPage());
            PARAMS::DestroyOwn();
            UI::RefreshPage();
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it'.$sql->query, Index::__GetPage(), FLASH::ERROR);
        }
    }
}


// Preparation of Reports(PERIODS)
$sql = new DB();
$sql
        ->Select([ 'id', 'percentage', 'name' ])
        ->From('periods')
        ->Where('user_id='.USER::Get(USER::ID));
$result = $sql->Query();
$countPeriods = count($result);
$report_Periods = new MySQLReport();
$report_Periods
        ->setReportProperties(array(
            'width' => '100%',
            'class' => 'table'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_PERCENTAGE',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Period name'
            ], [
                'CAPTION' => 'Percentage',
                'DEFAULT' =>
                    UI::Divbox([ 'class' => 'progress' ]
                            , UI::Divbox(array(
                                'class' => 'progress-bar',
                                'role' => 'progressbar',
                                'aria-valuenow' => '{2}',
                                'aria-valuemin' => '0',
                                'aria-valuemax' => '100',
                                'style' => 'width: {2}%;'
                            )
                                , '{2}%', true)
                            , true)
            ], [
                'CAPTION' => 'Actions',
                'DEFAULT' =>
                    UI::Button('Edit', 'button', 'btn btn-info'
                            , UI::GetPageUrl(Index::__GetPage()
                                    , array(
                                        'MODE' => 'EDIT',
                                        'PERIOD_ID' => '{1}'
                                    ))
                            , false)
                  . UI::Button('Delete', 'button', 'btn btn-danger'
                          , UI::GetPageUrl(Index::__GetPage()
                                    , array(
                                        'MODE' => 'REQ_DELETE',
                                        'PERIOD_ID' => '{1}'
                                    )), false)
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], [], [], []
        ))
        ->loadResultdata($result)
        ->defineEmptyMessage('No grading periods yet');


?>