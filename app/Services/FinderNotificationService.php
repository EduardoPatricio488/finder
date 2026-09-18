<?php

namespace App\Services;

use App\Models\Site;
use App\Notifications\FinderNotification;

class FinderNotificationService
{
    public function siteChanged(Site $site, string $message): void
    {
        $this->notifySiteUsers($site, 'Website alterado', $message, 'globe-alt');
    }

    public function productChanged(Site $site, string $message): void
    {
        $this->notifySiteUsers($site, 'Produto alterado', $message, 'shopping-bag');
    }

    public function saleChanged(Site $site, string $message): void
    {
        $this->notifySiteUsers($site, 'Nova atividade de vendas', $message, 'currency-euro');
    }

    private function notifySiteUsers(Site $site, string $title, string $message, string $icon): void
    {
        $users = collect([$site->owner, ...$site->members()->get()])
            ->filter()
            ->unique('id');

        foreach ($users as $user) {
            $user->notify(new FinderNotification($title, $message, $icon));
        }
    }
}