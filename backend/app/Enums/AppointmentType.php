<?php

namespace App\Enums;

enum AppointmentType: string
{
    case TELEHEALTH = 'TELEHEALTH';
    case IN_PERSON = 'IN_PERSON';
}
