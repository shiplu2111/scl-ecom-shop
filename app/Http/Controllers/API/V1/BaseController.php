<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;

/**
 * @group Public
 */
class BaseController extends Controller
{
    use ApiResponseTrait;
}
