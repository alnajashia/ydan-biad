<?php

namespace App\Features\Users\Domain\Enums;

enum IdentityType: string
{
    case NationalId = 'national_id';
    case Passport = 'passport';
}
