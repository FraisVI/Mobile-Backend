<?php

namespace App\Http\Controllers\Admin;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ArticlesController extends AdminController
{
    const THUMB_WIDTH = 512;
    const THUMB_HEIGHT = 256;

    var string $THUMB_SIZE = self::THUMB_WIDTH . "x" . self::THUMB_HEIGHT;

    public function index() {
        $title = 'Акции';

        $articles = Article::whereIn('type_id', [1, 2, 3, 4])->orderBy('created_at', 'DESC')->get();

        return view('admin.articles', compact('title', 'articles'));
    }

    public function create() {
        $title = 'Новая акция';

        return view('admin.article.add', compact('title'));
    }

    public function store(Request $request) {
        $article = new Article();
        $article->fill($request->all());
        $article->save();

        return redirect()->route('articles.edit', [$article->id])->with('success-message', 'Акция успешно создана. Теперь можете прикрепить изображение.');
    }

    public function edit($id) {
        $title = 'Редактирование акции';
        $article = Article::findOrFail($id);

        return view('admin.article.edit', compact('article', 'title'));
    }

    public function update(Request $request, $id) {
        /** @var \App\Models\Article $article */
        $article = Article::findOrFail($id);
        $article->fill($request->all());
        $article->published = $request->get('published', 0);

        if ($request->hasFile('image'))
        {
            $article->image = $this->storeFile($request, 'image');
            $article->image_small = $this->generateSmallImage($article);
        }

        if ($request->hasFile('image-small') && $article->image) {
            $file = $request->file('image-small');
            $filename = md5_file($file->getPathname());

            $img = Image::make($file);
            $img->resize(self::THUMB_WIDTH, null, fn($c) => $c->aspectRatio());
            $img->resizeCanvas(self::THUMB_WIDTH, self::THUMB_HEIGHT);

            $newFilename = "{$filename}_{$this->THUMB_SIZE}.jpg";
            Storage::put($newFilename, $img->encode('jpg')->__toString());

            $article->image_small = $newFilename;
        }

        $article->save();

        return redirect()->route('articles.index')->with('success-message', 'Акция успешно отредактирована');
    }

    public function recreateImages(): string
    {
        /** @var \App\Models\Article[] $articles */
        $articles = Article::all();
        foreach ($articles as $article) {
            $article->image_small = $this->generateSmallImage($article);
            $article->save();
        }

        return 'ok';
    }

    private function generateSmallImage(Article $article) : ?string {
        if (Str::startsWith($article->image, 'http'))
            return null;

        $filename = pathinfo($article->image)['filename'];

        $img = Image::make(Storage::get($article->image));
        $img->resize(self::THUMB_WIDTH, null, fn($c) => $c->aspectRatio());
        $img->resizeCanvas(self::THUMB_WIDTH, self::THUMB_HEIGHT);

        $newFilename = "{$filename}_{$this->THUMB_SIZE}.jpg";
        Storage::put($newFilename, $img->encode('jpg')->__toString());

        return $newFilename;
    }
}
