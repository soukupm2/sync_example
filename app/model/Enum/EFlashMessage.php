<?php

namespace App\Model\Enum;

use MabeEnum\Enum;

class EFlashMessage extends Enum
{
    const SUCCESS = "success";
    const ERROR = "danger";
    const INFO = "info";
    const WARNING = "warning";

    public static $flashIcon = [
        self::SUCCESS => 'fas fa-check',
        self::ERROR => 'fas fa-times',
        self::INFO => 'fas fa-info',
        self::WARNING => 'fas fa-exclamation',
    ];

    public static function getFlashIcon($type)
    {
        if (isset(self::$flashIcon[$type]))
        {
            return self::$flashIcon[$type];
        }

        return 'remove';
    }

    public static function getProperFlashType($type)
    {
        if (isset(self::$flashIcon[$type]))
        {
            return self::$flashIcon[$type];
        }

        return 'remove';
    }
}