<?php

$sql = new DB();
$sql
        ->Select([ 'id','percentage','name' ])
        ->From('components')
        ->Where('course_id = '.$COURSE_ID);

$result_Components = $sql->Query();

$report_GradingComponents = new MySQLReport();
$report_GradingComponents
        ->setReportProperties(array(
            'width' => '100%',
            'align' => 'center',
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
                'CAPTION' => 'Name'
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
                'CAPTION' => 'Action',
                'DEFAULT' =>
                    '<span class="btn-group">'
                  . UI::Button('Edit', 'button', 'btn btn-primary btn-sm'
                          , UI::GetPageUrl(Index::__GetPage()
                                  , array(
                                      'MODE' => 'EDIT-GCOMPONENT',
                                      'TARGET_ID' => '{1}'
                                  ))
                          , false)
                  . UI::Button('Remove', 'button', 'btn btn-danger btn-sm'
                          , UI::GetPageUrl(Index::__GetPage()
                                  , array(
                                      'MODE' => 'REMOVE-GCOMPONENT',
                                      'TARGET_ID' => '{1}'
                                  ))
                          , false)
                  . '</span>'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], [], [], []
        ))
        ->loadResultdata($result_Components)
        ->defineEmptyMessage('No existing grading components');

?>