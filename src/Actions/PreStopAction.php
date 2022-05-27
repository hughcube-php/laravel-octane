<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/12/17
 * Time: 21:36.
 */

namespace HughCube\Laravel\Octane\Actions;

use Illuminate\Http\JsonResponse;

class PreStopAction
{
    protected function action(): JsonResponse
    {
        return new JsonResponse(['code' => 200, 'message' => 'ok']);
    }

    public function __invoke(): JsonResponse
    {
        return $this->action();
    }
}
