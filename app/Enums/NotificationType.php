<?php

namespace App\Enums;

enum NotificationType: string
{
    case INFO = 'info';
    case DATE_REQUEST = 'date_request';
    case FRIEND_REQUEST = 'friend_request';

}
