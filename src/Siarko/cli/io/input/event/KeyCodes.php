<?php


namespace Siarko\cli\io\input\event;


class KeyCodes
{
    const CTRL_A = 1;
    const CTRL_B = 2;
    const CTRL_C = 3;
    const CTRL_D = 4;
    const CTRL_E = 5;
    const CTRL_F = 6;
    const CTRL_G = 7;
    const CTRL_H = 8;
    const CTRL_I = 9;
    const CTRL_J = 10;
    const CTRL_K = 11;
    const CTRL_L = 12;
    const CTRL_M = 13;
    const CTRL_N = 14;
    const CTRL_O = 15;
    const CTRL_P = 16;
    const CTRL_Q = 17;
    const CTRL_R = 18;
    const CTRL_S = 19;
    const CTRL_T = 20;
    const CTRL_U = 21;
    const CTRL_V = 22;
    const CTRL_W = 23;
    const CTRL_X = 24;
    const CTRL_Z = 25;

    const CTRL_BACKSPACE = 8;

    const CTRL_KEYS = [
        self::CTRL_A, self::CTRL_B, self::CTRL_C, self::CTRL_D,
        self::CTRL_E, self::CTRL_F, self::CTRL_G, self::CTRL_H,
        self::CTRL_I, self::CTRL_J, self::CTRL_K, self::CTRL_L,
        self::CTRL_M, self::CTRL_N, self::CTRL_O, self::CTRL_P,
        self::CTRL_Q, self::CTRL_R, self::CTRL_S, self::CTRL_T,
        self::CTRL_U, self::CTRL_V, self::CTRL_W, self::CTRL_X,
        self::CTRL_Z,
        self::CTRL_BACKSPACE, self::CTRL_ARROW_LEFT, self::CTRL_ARROW_RIGHT
    ];

    // do not use these in KeyEvent::isKey method
    const ALT_KEYS = [
        self::ALT_ARROW_UP, self::ALT_ARROW_DOWN, self::ALT_ARROW_LEFT, self::ALT_ARROW_RIGHT,
        self::ALT_INSERT, self::ALT_DELETE
    ];

    const SPECIAL = 27;
    const SPECIAL_KEYS = [
        self::TAB, self::BACKSPACE, self::ESC,
        self::ENTER, self::INSERT, self::DELETE,
        self::PG_DOWN, self::PG_UP, self::HOME,
        self::END,
    ];


    const TAB = 9;
    const BACKSPACE = 127;
    const SPACE = 32;
    const ESC = 27;
    const ENTER = 10;
    const TILDA = 126;
    const INSERT = [27, 91, 50, 126];
    const ALT_INSERT = [27,91,50,59,51,126];
    const DELETE = [27, 91, 51, 126];
    const ALT_DELETE = [27,91,51,59,51,126];
    const CTRL_DELETE = [27, 91, 51, 59, 53, 126];
    const PG_DOWN = [27,91,54,126];
    const PG_UP = [27,91,53,126];
    const HOME = [27,91,72];
    const END = [27,91,70];
    const ARROW_LEFT = [27,91,68];
    const CTRL_ARROW_LEFT = [27,91,49,59,53];
    const ALT_ARROW_LEFT = [27,91,49,59,51,68];
    const ARROW_RIGHT = [27,91,67];
    const CTRL_ARROW_RIGHT = [27,91,49,59,53,67];
    const ALT_ARROW_RIGHT = [27,91,49,59,51,67];
    const ARROW_DOWN = [27,91,66];
    const ALT_ARROW_DOWN = [27,91,49,59,51,66];
    const ARROW_UP = [27,91,65];
    const ALT_ARROW_UP = [27,91,49,59,51,65];
    const ARROWS = [
        'UP' => [self::ARROW_UP, self::ALT_ARROW_UP],
        'DOWN' => [self::ARROW_DOWN, self::ALT_ARROW_DOWN],
        'LEFT' => [self::ARROW_LEFT, self::CTRL_ARROW_LEFT, self::ALT_ARROW_LEFT],
        'RIGHT' => [self::ARROW_RIGHT, self::CTRL_ARROW_RIGHT, self::ALT_ARROW_RIGHT]
    ];

    const F1 = [27,79,80];
    const F2 = [27,79,81];
    const F3 = [27,79,82];
    const F4 = [27,79,83];
    const F5 = [27,91,49,53,126];
    const F6 = [27,91,49,55,126];
    const F7 = [27,91,49,56,126];
    const F8 = [27,91,49,57,126];
    const F9 = [27,91,50,48,126];
    const F10 = [27,91,50,49,126];
    const F11 = [27,91,50,50,126];
    const F12 = [27,91,50,52,126];
}