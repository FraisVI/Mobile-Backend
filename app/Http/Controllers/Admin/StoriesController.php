<?php

namespace App\Http\Controllers\Admin;
use App\Models\Stories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoriesController extends AdminController
{
    public function index() {
        $title = 'Stories';

        $stories = Stories::orderBy('order')->get();

        return view('admin.stories', compact('title', 'stories'));
    }

    public function create() {
        $title = 'Новая story';

        return view('admin.story.add', compact('title'));
    }

    public function store(Request $request) {
        $story = new Stories();
        $story->fill($request->all());
        $story->type = 'image';
        $story->duration = $story->duration * 1000;
        $story = $this->loadFile($request, $story, 'image');
        $story = $this->loadFile($request, $story, 'thumb');
        $story->save();

        return redirect()->route('stories.index', [$story->id])->with('success-message', 'Story успешно создана.');
    }

    public function edit($id) {
        $title = 'Редактирование story';
        $story = Stories::findOrFail($id);

        return view('admin.story.edit', compact('story', 'title'));
    }

    public function update(Request $request, $id) {
        /** @var \App\Models\Stories $story */
        $story = Stories::findOrFail($id);
        $story->fill($request->all());
        $story->duration = $story->duration * 1000;
        $story = $this->loadFile($request, $story, 'image');
        $story = $this->loadFile($request, $story, 'thumb');
        $story->published = $request->get('published', 0);
        $story->save();

        return redirect()->route('stories.index')->with('success-message', 'Story успешно отредактирована');
    }

    public function destroy($id) {
        Stories::destroy($id);
        return redirect()->route('stories.index')->with('success-message', 'Story успешно удалена.');
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $order = $request->get('order', []);

        $c = 1;
        foreach ($order as $id) {
            DB::table('stories')->where('id', $id)->update(['order' => $c++]);
        }

        return response()->json([]);
    }

    /**
     * @throws \Exception
     */
    private function loadFile(Request $request, Stories $story, $field): Stories
    {
        if ($request->hasFile($field))
        {
            $filename = $this->storeFile($request, $field);
            switch ($field) {
                case 'image':
                    $story->url = $filename;
                    break;
                case 'thumb':
                    $story->thumb = $filename;
                    break;

                default:
                    throw new \Exception("Unknown file field" . $field);
            }
        }

        return $story;
    }
}
