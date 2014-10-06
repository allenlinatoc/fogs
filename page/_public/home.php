<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

FLASH::addFlashes(array(
    'It\'s a matter of time!',
    'You got invalid email!',
    'Fuck you boy!'
), Index::__GetPage(), FLASH::ERROR, true);
//FLASH::clearFlashes();

?>