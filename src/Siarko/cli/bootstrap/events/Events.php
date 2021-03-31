<?php


namespace Siarko\cli\bootstrap\events;


interface Events
{
    const INIT = 'bootstrap_init';
    const IDLE = 'bootstrap_idle';
    const EXIT = 'bootstrap_exit';
}