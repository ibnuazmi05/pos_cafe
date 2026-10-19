<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum VerificationStatus: string
{
    use HasValues;

    case Pending = 'pending';
    case Verified = 'verified';
    case Flagged = 'flagged';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Verified => 'Terverifikasi',
            self::Flagged => 'Ditandai',
        };
    }
}
