<?php

namespace App\Enums\Vendor;

enum CategoryKind: string {
    case PRODUCT = 'product';
    case SERVICE = 'service';

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
