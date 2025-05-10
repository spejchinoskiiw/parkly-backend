<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Parkly API Documentation",
 *     description="API documentation for Parkly backend",
 *     @OA\Contact(
 *         email="stefan.pejchinoski@iwconnect.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 * @OA\Tag(
 *     name="Facilities",
 *     description="API Endpoints for managing parking facilities"
 * )
 * @OA\Tag(
 *     name="Parking Spots",
 *     description="API Endpoints for managing parking spots within facilities"
 * )
 * @OA\Tag(
 *     name="Reservations",
 *     description="API Endpoints for managing parking reservations"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
