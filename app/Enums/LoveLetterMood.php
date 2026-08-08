<?php

namespace App\Enums;

enum LoveLetterMood: string
{
    case Happy = 'happy';
    case Love = 'love';
    case Romantic = 'romantic';
    case Nostalgic = 'nostalgic';
    case Grateful = 'grateful';
    case Thoughtful = 'thoughtful';
    case Missing = 'missing';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Happy => 'fa-face-smile',
            self::Love => 'fa-heart',
            self::Romantic => 'fa-dove',
            self::Nostalgic => 'fa-hourglass-half',
            self::Grateful => 'fa-hands-praying',
            self::Thoughtful => 'fa-lightbulb',
            self::Missing => 'fa-face-sad-tear',
        };
    }
}
