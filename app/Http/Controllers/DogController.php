<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDogRequest;
use App\Models\Dog;

class DogController extends Controller
{
    public function create()
    {
        $dog = auth()->user()->dogs()->first();

        return view('dogs.create', compact('dog'));
    }

    public function store(StoreDogRequest $request)
    {
        $data = $request->validated();

        $path = $request->file('photo')->store('dogs', 'public');
        $data['photo_path'] = $path;
        unset($data['photo']);

        $data['user_id'] = auth()->id();

        $dog = auth()->user()->dogs()->first();
        if ($dog) {
            if ($dog->photo_path && $dog->photo_path !== $path) {
                \Storage::disk('public')->delete($dog->photo_path);
            }
            $dog->update($data);
        } else {
            $dog = Dog::create($data);
        }

        return redirect()->route('journey.departure');
    }
}
