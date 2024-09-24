<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use ApiResponse;
    public function __invoke(Request $request)
    {
        try {
            // Fetch categories along with their subcategories and random items (max 5)
            $home = Category::with(['subCategory', 'subCategory.item' => function ($query) {
                $query->inRandomOrder()->with('images')->take(6);
            }])->get();

            // Return a success response with a 200 status code
            return $this->apiResponse($home, 200, 'Data retrieved successfully');

        } catch (\Exception $exception) {
            // Log the exception message for debugging purposes
            \Log::error('Error fetching home data: ' . $exception->getMessage());

            // Return an error response with a 500 status code
            return $this->apiResponse(null, 500, 'An error occurred while retrieving the data');
        }
    }

}
