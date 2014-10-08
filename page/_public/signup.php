<?php

// Redirect to homepage if no parameter is defined
if ( !PARAMS::__HasParameters(PARAMS::PAGE_SELF, [ 'username','password' ]) )
{
    UI::RedirectTo('home');
}

// get passed parameters
$postUsername = PARAMS::Get('username', PARAMS::PAGE_SELF);
$postPassword = PARAMS::Get('password', PARAMS::PAGE_SELF);

if ( DATA::__HasPostData(['postPassword2']) )
{
    $postPassword2 = DATA::__GetPOST('postPassword2');
    
    // if passwords matched
    if ( $postPassword==$postPassword2 )
    {
        $sql = new DB();
        $sql
                ->InsertInto('users', [ 'username','password' ])
                ->Values(array(
                    $postUsername, USER::Encryptor($postPassword, 'ENCRYPT')
                ), [ 0,1 ]);
        $sql->Execute();
        if ( $sql->__IsSuccess() )
        {
            FLASH::AddFlash('You have successfully registered! You can now log in to system.', 'home', FLASH::SUCCESS, true);
            UI::RedirectTo('home');
        }
        else
        {
            FLASH::AddFlash('Something went wrong, geeks are on their way to fix it', Index::__GetPage(), FLASH::ERROR, true);
        }
    }
    // passwords didn't matched!
    else
    {
        FLASH::AddFlash('Passwords didn\'t matched, please try again', Index::__GetPage(), FLASH::ERROR, true);
    }
}

?>