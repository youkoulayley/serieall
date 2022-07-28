<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\List_log;
use App\Models\Log;
use Carbon\Carbon;

/**
 * Class ShowRepository.
 */
class LogRepository
{
    protected $log;

    protected $list_log;

    /**
     * LogRepository constructor.
     */
    public function __construct(Log $log, List_log $list_log)
    {
        $this->log = $log;
        $this->list_log = $list_log;
    }

    /**
     * On récupère les 10 derniers logs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getTenDistinctLogs()
    {
        return $this->list_log::with('user')
            ->select('id', 'job', 'object', 'object_id', 'user_id', 'created_at')
            ->limit(10)
            ->orderBy('id', 'desc')
            ->distinct()
            ->get();
    }

    /**
     * On récupère tous les logs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllDistinctLogs()
    {
        return $this->list_log::with('user')
            ->select('id', 'job', 'object', 'object_id', 'user_id', 'created_at')
            ->orderBy('id', 'desc')
            ->distinct()
            ->get();
    }

    /**
     * On récupère un log grâce à son ID.
     *
     * @param $id
     *
     * @return List_log|\Illuminate\Database\Eloquent\Builder
     */
    public function getLogByID($id)
    {
        return $this->list_log::with('logs', 'user')->find($id);
    }

    /**
     * Récupère les logs de plus d'une semaine.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getLogsSup1Week()
    {
        $oneWeekAgo = Carbon::today()->subWeek()->toDateString();

        return $this->list_log::where('created_at', '<', $oneWeekAgo)->get();
    }
}
