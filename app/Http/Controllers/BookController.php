<?php


namespace App\Http\Controllers;


use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('checkUuid:book/', ['except' => ['index', 'create']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => Book::all()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $originalName = $image->getClientOriginalName();
            $timestamp = Carbon::now()->timestamp;
            $newName = "$timestamp-$originalName";
            $destinyFolder = './uploads/';
            $image->move($destinyFolder, $newName);

            $new_Book = new Book([
                'title' => $request->get('title'),
                'image' => ltrim($destinyFolder, '.') . $newName
            ]);
            $new_Book->save();

            return response()->json(['data' => $new_Book], 201);
        } else {
            return response()->json(['error' => 'Image missing'], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        return response()->json(['data' => Book::query()->findOrFail($id)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function delete(string $id): JsonResponse
    {
        $book = Book::query()->findOrFail($id);
        $routeFile = base_path('public') . $book['image'];
        if (file_exists($routeFile)) unlink($routeFile);
        $book->delete();

        return response()->json(['msg' => 'Book deleted successfully!']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $book = Book::query()->findOrFail($id);

        $title = $request->get('title');
        $book['title'] = $title ?? $book['title'];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $originalName = $image->getClientOriginalName();
            $timestamp = Carbon::now()->timestamp;
            $newName = "$timestamp-$originalName";
            $destinyFolder = './uploads/';
            $image->move($destinyFolder, $newName);

            $routeFile = base_path('public') . $book['image'];
            if (file_exists($routeFile)) unlink($routeFile);

            $book['image'] = ltrim($destinyFolder, '.') . $newName;
        }

        $book->update();
        return response()->json(['data' => $book->refresh()]);
    }
}
