<?php

$sql = new DB();
$sql
        ->Select([ 'd_student_course.id','students.fullname' ])
        ->From('students, d_student_course')
        ->Where('students.id = d_student_course.student_id '
                . 'AND students.user_id='.USER::Get(USER::ID).' '
                . 'AND d_student_course.course_id='.$COURSE_ID)
        ->OrderBy('students.fullname', DB::ORDERBY_ASCENDING);
$result = $sql->Query();

$report_StudentsList = new MySQLReport();
$report_StudentsList
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
                'CAPTION' => 'Name'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' => 
                    UI::Button('Remove', 'button', 'btn btn-danger btn-sm'
                            , UI::GetPageUrl(Index::__GetPage()
                                    , array(
                                        'MODE' => 'REMOVE-STUDENT',
                                        'TARGET_ID' => '{1}'
                                    ))
                            , false)
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], []
        ))
        ->loadResultdata($result)
        ->defineEmptyMessage('No one on the list');

?>