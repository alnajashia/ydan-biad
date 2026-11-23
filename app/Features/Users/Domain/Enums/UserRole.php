<?php

namespace App\Features\Users\Domain\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Employee = 'employee';
    case Customer = 'customer';
}
