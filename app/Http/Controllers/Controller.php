<?php

namespace App\Http\Controllers;

use App\Traits\handleResponse;
use App\Traits\HandlesModelTransactions;

abstract class Controller
{
    use handleResponse,HandlesModelTransactions;
}