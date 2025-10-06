<?php
// app/Enums/DailyTask/DailyTaskList/GroupType.php

namespace App\Enums\DailyTask\DailyTaskList;

enum GroupType: string
{
    case NONE = 'none';
    case STATUS = 'status';
    case PRIORITY = 'priority';
    case PROJECT = 'project';
    case ASSIGNEE = 'assignee';
    case DATE = 'date';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Tanpa Grouping',
            self::STATUS => 'Status',
            self::PRIORITY => 'Priority',
            self::PROJECT => 'Project',
            self::ASSIGNEE => 'Assignee',
            self::DATE => 'Tanggal',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::NONE => 'heroicon-o-list-bullet',
            self::STATUS => 'heroicon-o-flag',
            self::PRIORITY => 'heroicon-o-exclamation-triangle',
            self::PROJECT => 'heroicon-o-folder',
            self::ASSIGNEE => 'heroicon-o-users',
            self::DATE => 'heroicon-o-calendar-days',
        };
    }

    public function defaultBadgeClasses(): string
    {
        return match($this) {
            self::PROJECT => 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700',
            self::ASSIGNEE => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700',
            default => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}