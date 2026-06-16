<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeSectionRequest;
use App\Models\HomeSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    public function index(): View
    {
        $sections = HomeSection::query()
            ->orderBy('sort_order')
            ->get();

        return view('admin.home-sections.index', compact('sections'));
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home-sections.edit', ['section' => $homeSection]);
    }

    public function update(HomeSectionRequest $request, HomeSection $homeSection): RedirectResponse
    {
        $data = $request->safe()->except(['image']);

        if ($request->hasFile('image')) {
            if ($homeSection->image_path && ! str_starts_with($homeSection->image_path, 'images/')) {
                Storage::disk('public')->delete($homeSection->image_path);
            }

            $data['image_path'] = $request->file('image')->store('home-sections', 'public');
        }

        $homeSection->update($data);

        return redirect()
            ->route('admin.home-sections.index')
            ->with('success', $homeSection->name.' updated successfully.');
    }
}
