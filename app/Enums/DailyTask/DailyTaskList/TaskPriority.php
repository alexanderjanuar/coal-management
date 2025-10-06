<?php
// app/Enums/DailyTask/DailyTaskList/TaskPriority.php

namespace App\Enums\DailyTask\DailyTaskList;

enum TaskPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function order(): int
    {
        return match($this) {
            self::URGENT => 4,
            self::HIGH => 3,
            self::NORMAL => 2,
            self::LOW => 1,
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::URGENT => 'heroicon-s-exclamation-triangle',
            self::HIGH => 'heroicon-o-exclamation-triangle',
            self::NORMAL => 'heroicon-o-minus',
            self::LOW => 'heroicon-o-arrow-down',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::URGENT => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
            self::HIGH => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-700',
            self::NORMAL => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700',
            self::LOW => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}