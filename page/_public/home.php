<?php

// Pre-defined values
$postUsername = '';
$postPassword = '';



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
            if ($loginsucess)
            {
                FLASH::AddFlash('Login success but redirection didn\'t work!', Index::__GetPage(), FLASH::SUCCESS, true);
                UI::RedirectTo('user-home');
            }
            else
            {
                FLASH::AddFlash('Invalid username or password, please try again', Index::__GetPage(), FLASH::ERROR, true);
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