<?php

namespace App\Enums;

enum AuditAction: string
{
    case VIEW = 'VIEW';
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case EXPORT = 'EXPORT';
    case DOWNLOAD = 'DOWNLOAD';
    case AI_QUERY = 'AI_QUERY';
    case TELEHEALTH_JOIN = 'TELEHEALTH_JOIN';
    case TELEHEALTH_LEAVE = 'TELEHEALTH_LEAVE';
    case TELEHEALTH_PARTICIPANT_ADDED = 'TELEHEALTH_PARTICIPANT_ADDED';
}
