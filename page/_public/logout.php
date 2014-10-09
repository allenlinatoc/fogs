<?php

if ( USER::__IsLoggedIn() ) {
    USER::Logout();
    FLASH::AddFlash('You have successfully logged off', 'home', FLASH::SUCCESS, true);
}
UI::RedirectTo('home');
?>