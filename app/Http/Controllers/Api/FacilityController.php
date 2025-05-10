<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\StoreFacilityRequest;
use App\Http\Requests\Facility\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Services\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Facilities",
 *     description="API Endpoints for managing parking facilities"
 * )
 */
final class FacilityController extends Controller
{
    public function __construct(
        private readonly FacilityService $facilityService
    ) {}

    /**
     * Display a listing of facilities.
     * 
     * @OA\Get(
     *     path="/api/facilities",
     *     summary="Get a list of all facilities",
     *     tags={"Facilities"},
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
     *                     @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                     @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                     @OA\Property(property="manager_id", type="integer", nullable=true, example=5),
     *                     @OA\Property(
     *                         property="manager",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="John Manager"),
     *                         @OA\Property(property="email", type="string", example="manager@example.com")
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
     *     security={{ "sanctum": {} }}
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny-facility');
        
        return FacilityResource::collection(
            Facility::with('manager')->paginate()
        );
    }

    /**
     * Store a newly created facility.
     * 
     * @OA\Post(
     *     path="/api/facilities",
     *     summary="Create a new facility",
     *     tags={"Facilities"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "parking_spot_count"},
     *             @OA\Property(property="name", type="string", example="East Side Parking"),
     *             @OA\Property(property="parking_spot_count", type="integer", example=25),
     *             @OA\Property(property="manager_id", type="integer", nullable=true, example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Facility created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="East Side Parking"),
     *                 @OA\Property(property="parking_spot_count", type="integer", example=25),
     *                 @OA\Property(property="manager_id", type="integer", nullable=true, example=5),
     *                 @OA\Property(
     *                     property="manager",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="John Manager"),
     *                     @OA\Property(property="email", type="string", example="manager@example.com")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
     *     security={{ "sanctum": {} }}
     * )
     */
    public function store(StoreFacilityRequest $request): FacilityResource
    {
        $facility = $this->facilityService->createFacility($request->validated());
        
        return new FacilityResource($facility->load('manager'));
    }

    /**
     * Display the specified facility.
     * 
     * @OA\Get(
     *     path="/api/facilities/{id}",
     *     summary="Get a specific facility by ID",
     *     tags={"Facilities"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Facility ID",
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
     *                 @OA\Property(property="name", type="string", example="Downtown Parking"),
     *                 @OA\Property(property="parking_spot_count", type="integer", example=50),
     *                 @OA\Property(property="manager_id", type="integer", nullable=true, example=5),
     *                 @OA\Property(
     *                     property="manager",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="John Manager"),
     *                     @OA\Property(property="email", type="string", example="manager@example.com")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facility not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "sanctum": {} }}
     * )
     */
    public function show(Facility $facility): FacilityResource
    {
        Gate::authorize('viewAny-facility');
        
        return new FacilityResource($facility->load('manager'));
    }

    /**
     * Update the specified facility.
     * 
     * @OA\Put(
     *     path="/api/facilities/{id}",
     *     summary="Update an existing facility",
     *     tags={"Facilities"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Facility ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Facility Name"),
     *             @OA\Property(property="parking_spot_count", type="integer", example=30),
     *             @OA\Property(property="manager_id", type="integer", nullable=true, example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facility updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Facility Name"),
     *                 @OA\Property(property="parking_spot_count", type="integer", example=30),
     *                 @OA\Property(property="manager_id", type="integer", nullable=true, example=5),
     *                 @OA\Property(
     *                     property="manager",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="John Manager"),
     *                     @OA\Property(property="email", type="string", example="manager@example.com")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facility not found"
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
     *     security={{ "sanctum": {} }}
     * )
     */
    public function update(UpdateFacilityRequest $request, Facility $facility): FacilityResource
    {
        $facility = $this->facilityService->updateFacility($facility, $request->validated());
        
        return new FacilityResource($facility->load('manager'));
    }

    /**
     * Remove the specified facility.
     * 
     * @OA\Delete(
     *     path="/api/facilities/{id}",
     *     summary="Delete a facility",
     *     tags={"Facilities"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Facility ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facility deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Facility deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Facility not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     security={{ "sanctum": {} }}
     * )
     */
    public function destroy(Facility $facility): JsonResponse
    {
        Gate::authorize('delete-facility', $facility);
        
        $this->facilityService->deleteFacility($facility);
        
        return response()->json(['message' => 'Facility deleted successfully'], 200);
    }
}
