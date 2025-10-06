<?php
// app/Enums/DailyTask/DailyTaskList/DateGroup.php

namespace App\Enums\DailyTask\DailyTaskList;

enum DateGroup: string
{
    case OVERDUE = 'Terlambat';
    case UPCOMING = 'Mendatang';
    case NO_DEADLINE = 'Tanpa Deadline';
    case COMPLETED = 'Selesai';

    public function badgeClasses(): string
    {
        return match($this) {
            self::OVERDUE => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
            self::UPCOMING => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700',
            self::NO_DEADLINE => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
            self::COMPLETED => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::OVERDUE => 'heroicon-o-exclamation-circle',
            self::UPCOMING => 'heroicon-o-calendar',
            self::NO_DEADLINE => 'heroicon-o-minus-circle',
            self::COMPLETED => 'heroicon-o-check-circle',
        };
    }

    public function progressBarClasses(): string
    {
        return match($this) {
            self::OVERDUE => 'bg-red-500 dark:bg-red-400',
            self::UPCOMING => 'bg-blue-500 dark:bg-blue-400',
            self::NO_DEADLINE => 'bg-gray-500 dark:bg-gray-400',
            self::COMPLETED => 'bg-green-500 dark:bg-green-400',
        };
    }
}