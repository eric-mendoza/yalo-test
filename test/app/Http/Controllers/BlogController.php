<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BlogController extends Controller
{
    public function getBlogs($start, $size) {
        try{
            // Query blogs from outside source
            $response = Http::get('https://jsonplaceholder.typicode.com/posts');
            $blogs = collect(json_decode($response))->slice($start, $size);

            // Verify if size is correct
            $blogsSize = count($blogs);
            if ($size + $start > $blogsSize) {
                return response()->json(['error' => 'size is too big'], 404);
            }


            // Query authors
            $response = Http::get('https://jsonplaceholder.typicode.com/users');
            $users = collect(json_decode($response));

            // Add authors
            $blogs->map(function($blog, $key) use($users) {
                $blog->author = $users->firstWhere('id', $blog->userId);

                // Fetch comments
                $response = Http::get('https://jsonplaceholder.typicode.com/posts/'.$blog->id.'/comments');
                $comments = collect(json_decode($response));
                $blog->comments = $comments; // TODO: Test if it comes empty

                return $blog;
            });

            return $blogs;
        } catch (\Exception $e)
        {
            // handle the exception
            return response()->json(['error' => 'server error'], 500);
        }
    }
}
