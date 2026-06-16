<?php

namespace App\Http\Controllers\Admin;
use App\Models\StoriesV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoriesV2Controller extends AdminController
{
    public function index() {
        $title = 'Stories';

        $stories = StoriesV2::orderBy('order')->get();

        return view('admin.storiesV2', compact('title', 'stories'));
    }

    public function create() {
        $title = 'Новая story';

        return view('admin.storyV2.add', compact('title'));
    }

    public function store(Request $request) {
        $story = new StoriesV2();
        $story->fill($request->all());
        $story->duration = $story->duration * 1000;
        $story = $this->loadFile($request, $story, 'preview');
        $story->save();

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $elements = [];
            foreach ($files as $file) {
                $ext = $this->getFileExtension($file->getMimeType());
                $filename = md5_file($file->getPathname()) . $ext;
                $file->storeAs('', $filename);
                $elements[] = [$filename, null, null];
            }

            $story->elements = $elements;
            $story->save();
        }

        return redirect()->route('storiesV2.edit', [$story->id])->with('success-message', 'Story успешно создана.');
    }

    public function edit($id) {
        $title = 'Редактирование story';
        $story = StoriesV2::findOrFail($id);

        return view('admin.storyV2.edit', compact('story', 'title'));
    }

    public function update(Request $request, $id) {
        /** @var \App\Models\StoriesV2 $story */
        $story = StoriesV2::findOrFail($id);
        $story->fill($request->all());
        $story->duration = $story->duration * 1000;
        $story = $this->loadFile($request, $story, 'preview');
        $story->published = $request->get('published', 0);

        $active_images = $request->get('active_images', []);
        $active_links_text = $request->get('active_links_text', []);
        $active_links = $request->get('active_links', []);

        $slideInfo = [];
        for ($i = 0; $i < count($active_images); ++$i) {
            $slideInfo[] = [$active_images[$i], $active_links_text[$i], $active_links[$i]];
        }
        $story->elements = $slideInfo;
        $story->save();

        $filesLoaded = false;
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $elements = $story->elements;
            foreach ($files as $file) {
                $ext = $this->getFileExtension($file->getMimeType());
                $filename = md5_file($file->getPathname()) . $ext;
                $file->storeAs('', $filename);
                $elements[] = [$filename, null, null];
            }

            $filesLoaded = true;
            $story->elements = $elements;
            $story->save();
        }

        if ($filesLoaded)
            return redirect()->route('storiesV2.edit', $story->id);

        return redirect()->route('storiesV2.index')->with('success-message', 'Story успешно отредактирована');
    }

    public function destroy($id) {
        StoriesV2::destroy($id);
        return redirect()->route('storiesV2.index')->with('success-message', 'Story успешно удалена.');
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $order = $request->get('order', []);

        $c = 1;
        foreach ($order as $id) {
            DB::table('stories_v2')->where('id', $id)->update(['order' => $c++]);
        }

        return response()->json([]);
    }

    /**
     * @throws \Exception
     */
    private function loadFile(Request $request, StoriesV2 $story, $field): StoriesV2
    {
        if ($request->hasFile($field))
        {
            $filename = $this->storeFile($request, $field);
            switch ($field) {
                case 'image':
                    $story->url = $filename;
                    break;
                case 'preview':
                    $story->preview = $filename;
                    break;

                default:
                    throw new \Exception("Unknown file field" . $field);
            }
        }

        return $story;
    }
}
