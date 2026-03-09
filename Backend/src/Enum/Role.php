<?php

namespace App\Enum;

enum Role: string {
    case ADMIN = 'ADMIN';
    case COACH = 'COACH';
    case MEDICAL = 'MEDICAL';
}
