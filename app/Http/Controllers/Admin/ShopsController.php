<?php

namespace App\Http\Controllers\Admin;
use App\Models\City;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopsController extends AdminController
{
    public function index() {
        $title = 'Магазины';

        $cities = [];
        foreach (City::all() as $city) {
            $cities[$city->id] = $city;
        }

        $shops = Shop::all();

        return view('admin.shops', compact('title', 'shops', 'cities'));
    }

    public function create() {
        $title = 'Новый магазин';
        $cities = City::all();

        return view('admin.shop.add', compact('title', 'cities'));
    }

    public function store(Request $request) {
        $shop = new Shop();
        $shop->fill($request->all());
        $shop->save();

        return redirect()->route('shops.index', [$shop->id])->with('success-message', 'Магазин успешно добавлен.');
    }

    public function edit($id) {
        $title = 'Редактирование магазина';
        $shop = Shop::findOrFail($id);

        $cities = City::all();

        return view('admin.shop.edit', compact('shop', 'cities', 'title'));
    }

    public function update(Request $request, $id) {
        /** @var \App\Models\Shop $shop */
        $shop = Shop::findOrFail($id);
        $shop->fill($request->all());

        $active_images = $request->get('active_images', []);
        $shop->images = $active_images;
        $shop->save();

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $images = $shop->images;
            foreach ($files as $file) {
                $ext = $this->getFileExtension($file->getMimeType());
                $filename = md5_file($file->getPathname()) . $ext;
                $file->storeAs('', $filename);
                $images[] = $filename;
            }

            $shop->images = $images;
            $shop->save();
        }


        return redirect()->route('shops.index')->with('success-message', 'Магазин успешно отредактирован.');
    }

    public function destroy($id) {
        Shop::destroy($id);
        return redirect()->route('shops.index')->with('success-message', 'Магазин успешно удалён.');
    }
}
