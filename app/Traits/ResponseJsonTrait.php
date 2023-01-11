<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ResponseJsonTrait
{
    /**
     * Message response success
     * types : store_data, default
     *
     * @return mixed
     */
    protected function responseSuccess($type, $options = [])
    {
        switch ($type) {
            case 'store_data':
                $code = Response::HTTP_CREATED;
                break;
            default:
                $code = Response::HTTP_OK;
                break;
        }

        $response = [
            'status' => 'success',
        ];
        if (isset($options['message'])) {
            $response['message'] = $options['message'];
        }
        if (isset($options['data'])) {
            $response['data'] = $options['data'];
        }

        return response()->json($response, $code);
    }

    /**
     * Api error response
     *
     * @param  string|array $message
     * @param int $code
     *
     * @return Illuminate\Http\Response
     */
    protected function responseError($type, $options = [])
    {
        $response = [
            'status' => 'fail',
        ];

        switch ($type) {
            case 'validation':
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;
                break;
            case 'not_found':
                $code = Response::HTTP_NOT_FOUND;
                break;
            case 'forbidden':
                $code = Response::HTTP_FORBIDDEN;
                break;
            case 'bad_request':
                $code = Response::HTTP_BAD_REQUEST;
                break;
            case 'unauthenticated':
                $code = Response::HTTP_UNAUTHORIZED;
                break;
            case 'conflict':
                $code = Response::HTTP_CONFLICT;
                break;
            default:
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $response['status'] = 'error';
                break;
        }

        if (isset($options['message'])) {
            $response['message'] = $options['message'];
        }
        if (isset($options['data'])) {
            $response['data'] = $options['data'];
        }

        return response()->json($response, $code);
    }
}
