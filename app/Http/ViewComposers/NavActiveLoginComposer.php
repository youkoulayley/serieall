<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class NavActiveLoginComposer
{

    /**
     * AdminViewComposer constructor.
     */
    public function __construct()
    {
        // Dependencies automatically resolved by service container...
        $this->navActive = 'login';
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('navActive', $this->navActive);
    }
}