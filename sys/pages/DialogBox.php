<?php

PARAMS::AcceptURLQueries(true, [ 'LOCALRESULT' ]);
# ---- Filtered
#

if ( !PARAMS::__HasParameters(PARAMS::PAGE_SELF, [ 'DIALOG_OBJECT' ]) ) {
    FLASH::AddFlash('You tried to enter an unauthorized page.', 
            [Index::$DEFAULT_PAGE],
        'ERROR', TRUE);
    UI::RedirectTo(Index::$DEFAULT_PAGE);
}
$dialogObject = DIALOG::ToDialog(PARAMS::Get('DIALOG_OBJECT', PARAMS::PAGE_SELF));
if ( PARAMS::__HasParameters(PARAMS::PAGE_SELF, [ 'LOCALRESULT' ]) ) {
    // DATA::closePassage(Index::__GetPage());
//        die('May localresult: ' . DATA::__GetIntentSecurely('LOCALRESULT'));
    $dialogObject->SetResult(intval(PARAMS::Get('LOCALRESULT', PARAMS::PAGE_SELF)), true);
}

?>