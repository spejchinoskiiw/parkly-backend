<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParkingSpot\StoreParkingSpotRequest;
use App\Http\Requests\ParkingSpot\UpdateParkingSpotRequest;
use App\Http\Resources\ParkingSpotResource;
use App\Models\Facility;
use App\Models\ParkingSpot;
use App\Services\ParkingSpotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Parking Spots",
 *     description="API Endpoints for managing parking spots within facilities"
 * )
 */
final class ParkingSpotController extends Controller
{
    public function __construct(
        private readonly ParkingSpotService $parkingSpotService
    ) {}

    /**
     * Display a listing of parking spots.
     * 
     * @OA\Get(
     *     path="/api/parking-spots",
     *     summary="Get a list of all parking spots",
     *     tags={"Parking Spots"},
     *     @OA\Parameter(
     *         name="facility_id",
     *         in="query",
     *         required=false,
     *         description="Filter spots by facility ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="facility_id", type="integer", example=1),
     *                     @OA\Property(property="spot_number", type="integer", example=5),
     *                     @OA\Property(
     *                         property="facility",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                         @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                         @OA\Property(property="manager_id", type="integer", nullable=true)
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny-parking-spot');
        
        $query = ParkingSpot::query()->with('facility');
        
        // Filter by facility if provided
        if ($request->has('facility_id')) {
            $query->where('facility_id', $request->input('facility_id'));
        }
        
        return ParkingSpotResource::collection(
            $query->orderBy('facility_id')->orderBy('spot_number')->paginate()
        );
    }

    /**
     * Store a newly created parking spot.
     * 
     * @OA\Post(
     *     path="/api/parking-spots",
     *     summary="Create a new parking spot",
     *     tags={"Parking Spots"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"facility_id", "spot_number"},
     *             @OA\Property(property="facility_id", type="integer", example=1),
     *             @OA\Property(property="spot_number", type="integer", example=6)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Parking spot created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=6),
     *                 @OA\Property(property="facility_id", type="integer", example=1),
     *                 @OA\Property(property="spot_number", type="integer", example=6),
     *                 @OA\Property(
     *                     property="facility",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                     @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                     @OA\Property(property="manager_id", type="integer", nullable=true)
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="spot_number",
     *                     type="array",
     *                     @OA\Items(type="string", example="The spot number is already taken for this facility.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function store(StoreParkingSpotRequest $request): ParkingSpotResource
    {
        $parkingSpot = $this->parkingSpotService->createParkingSpot($request->validated());
        
        return new ParkingSpotResource($parkingSpot->load('facility'));
    }

    /**
     * Display the specified parking spot.
     * 
     * @OA\Get(
     *     path="/api/parking-spots/{id}",
     *     summary="Get a specific parking spot by ID",
     *     tags={"Parking Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parking Spot ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="facility_id", type="integer", example=1),
     *                 @OA\Property(property="spot_number", type="integer", example=5),
     *                 @OA\Property(
     *                     property="facility",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                     @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                     @OA\Property(property="manager_id", type="integer", nullable=true)
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking spot not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function show(ParkingSpot $parkingSpot): ParkingSpotResource
    {
        Gate::authorize('viewAny-parking-spot');
        
        return new ParkingSpotResource($parkingSpot->load('facility'));
    }

    /**
     * Update the specified parking spot.
     * 
     * @OA\Put(
     *     path="/api/parking-spots/{id}",
     *     summary="Update an existing parking spot",
     *     tags={"Parking Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parking Spot ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="spot_number", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parking spot updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="facility_id", type="integer", example=1),
     *                 @OA\Property(property="spot_number", type="integer", example=10),
     *                 @OA\Property(
     *                     property="facility",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                     @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                     @OA\Property(property="manager_id", type="integer", nullable=true)
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking spot not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function update(UpdateParkingSpotRequest $request, ParkingSpot $parkingSpot): ParkingSpotResource
    {
        $parkingSpot = $this->parkingSpotService->updateParkingSpot($parkingSpot, $request->validated());
        
        return new ParkingSpotResource($parkingSpot->load('facility'));
    }

    /**
     * Remove the specified parking spot.
     * 
     * @OA\Delete(
     *     path="/api/parking-spots/{id}",
     *     summary="Delete a parking spot",
     *     tags={"Parking Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Parking Spot ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parking spot deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Parking spot deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking spot not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "bearerAuth": {} }}
     * )
     */
    public function destroy(ParkingSpot $parkingSpot): JsonResponse
    {
        Gate::authorize('delete-parking-spot', $parkingSpot);
        
        $this->parkingSpotService->deleteParkingSpot($parkingSpot);
        
        return response()->json(['message' => 'Parking spot deleted successfully'], 200);
    }
}
