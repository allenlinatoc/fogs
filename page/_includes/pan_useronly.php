<?php

if ( !USER::__IsLoggedIn() )
{
    FLASH::AddFlash('You are not allowed to access this page', 'home', FLASH::ERROR);
    UI::RedirectTo('home');
}

?>