<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\LogRepository;

/**
 * Class AdminController.
 */
class AdminController extends Controller
{
    protected $nbPerPage = 20;

    protected $logRepository;

    /**
     * AdminController constructor.
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Renvoi vers la page admin/index.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $logs = $this->logRepository->getTenDistinctLogs();

        return view('admin/index', compact('logs'));
    }
}
