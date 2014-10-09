<?php

// Pre-defined values
$postUsername = '';
$postPassword = '';

// redirect "logged-in users" to User panel
if ( USER::__IsLoggedIn() )
{
    UI::RedirectTo('userhome');
}


if (DATA::__HasPostData())
{
    $type = DATA::__GetPOST('postType', true, true, false);
    $postUsername = DATA::__GetPOST('postUsername', true, true, true);
    $postPassword = DATA::__GetPOST('postPassword');
    
    FLASH::CheckAndAdd(array(
        'Username should not contain spaces' => STR::__HasSpaces($postUsername),
        'Username should contain letters and numbers only' => STR::__HasPunct($postUsername),
        'Password cannot exceed 75 characters' => strlen($postPassword)>75
    ), 'Success', Index::__GetPage(), Index::__GetPage(), true);
    
    if ( FLASH::__GetType()==FLASH::SUCCESS )
    {
        if ( $type=='LOGIN' )
        {
            $loginsucess = USER::Login($postUsername, $postPassword);
            $sql = new DB();
            $exists = $sql->__getRowCount('users', [ 'username="'.$postUsername.'"' ]) > 0;
            if ($loginsucess)
            {
                FLASH::AddFlash('Login success but redirection didn\'t work!', Index::__GetPage(), FLASH::SUCCESS, true);
                UI::RedirectTo('userhome');
            }
            else
            {
                if ( !$exists )
                {
                    FLASH::AddFlash('User does not exist, mind signing up?', Index::__GetPage(), FLASH::ERROR, true);
                }
                else
                {
                    FLASH::AddFlash('Invalid username or password, please try again', Index::__GetPage(), FLASH::ERROR);
                }
            }
        }
        else if ( $type=='REGISTER' )
        {
            PARAMS::CreateMany(array(
                [
                    'name' => 'username',
                    'value' => $postUsername
                ], [
                    'name' => 'password',
                    'value' => $postPassword
                ]
            ), 'signup');
            UI::RedirectTo('signup');
        }
    }
}

?>