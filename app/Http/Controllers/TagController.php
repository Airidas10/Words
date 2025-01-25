<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Tag;

use App\Http\Requests\TagRequest;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('words')->orderBy('created_at', 'asc')->get();

        return Inertia::render('TagsIndex', [
            'tags' => $tags
        ]);
    }

    public function show($id)
    {
        $tag = Tag::with('words')->findOrFail($id);

        return Inertia::render('Tag', [
            'tag' => $tag,
        ]);
    }

    public function create()
    {
        return Inertia::render('TagCreator', [
            'tag' => null
        ]);
    }

    public function store(TagRequest $request)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $tag = Tag::create($request->all());

        $response = ['status' => 'success', 'msg' => 'Tag was created!', 'data' => $tag];

        return $response;
    }

    public function edit($id)
    {
        $tag = Tag::findOrFail($id);

        return Inertia::render('TagCreator', [
            'tag' => $tag
        ]);
    }

    public function update(TagRequest $request, $id)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $tag = Tag::findOrFail($id);

        if($tag){
            $tag->update($request->all());
        }

        $response = ['status' => 'success', 'msg' => 'Tag was updated!', 'data' => $tag];

        return $response;
    }

    public function destroy(TagRequest $request, $id)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $tag = Tag::findOrFail($id);

        if($tag){
            $tag->delete();
        }

        $response = ['status' => 'success', 'msg' => 'Tag was deleted!', 'data' => null];

        return $response;
    }
}
