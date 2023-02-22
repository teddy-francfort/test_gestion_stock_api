<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class AlreadyLoggedInResponse extends JsonResponse
{
    /**
     * @param  mixed|null  $data
     * @param  int  $status
     * @param  array<string, mixed>  $headers
     * @param  int  $options
     * @param  bool  $json
     */
    public function __construct($data = null, $status = 403, $headers = [], $options = 0, $json = false)
    {
        $data = $data ?? ['message' => 'Already logged in', 'code' => 'ALREADY_LOGGED_IN'];
        parent::__construct($data, $status, $headers, $options, $json);
    }
}
