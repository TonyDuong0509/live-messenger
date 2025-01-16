<?php

namespace App\Services;

class NotifyService
{
    public static function SuccessNotification(string $message)
    {
        notyf()->addSuccess($message);
    }

    public static function ErrorNotification(string $message)
    {
        notyf()->addError($message);
    }

    public static function WarningNotification(string $message)
    {
        notyf()->addWarning($message);
    }
}
