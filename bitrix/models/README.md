# ELEMENT
```php

//GET

	return ProductModel::filter(['ID' => 336, 'ACTIVE' => 'Y'])->get();
	return ProductModel::get();
	return ProductModel::orderBy('NAME', 'desc')->get();
	return ProductModel::where('ID', '!=', 336)->orderBy('NAME', 'desc')->take(2)->get();
	return ProductModel::orderBy('NAME', 'desc')->pagenate(1, 2)->get();
	return ProductModel::orderBy('NAME', 'desc')->select(['*', 'UF_*'])->pagenate(1, 2)->get();
	return ProductModel::where('ACTIVE', '=', 'Y')->select(['ID'])->first()->toArray();

//RILATIONS

	return ProductModel::first(338)->parent()->get();
	return ParentProductModel::first(339)->children()->get();

// UPDATE
	
	$products = ProductModel::first(336)
		->setFields([
			'FIELDS' => ['NAME' => 'name5'],
			'PROPERTY_VALUES' => ['WIDTH' => '46']
		])->save();

//ADD
	
	$products = new ProductModel([
			'FIELDS' => ['NAME' => 'test_model_3'],
			'PROPERTY_VALUES' => ['WIDTH' => '48']
		]);
	return $products->save();

	$products = new ProductModel();
	$products->setFields([
			'FIELDS' => ['NAME' => 'test_model_7'],
			'PROPERTY_VALUES' => ['WIDTH' => '47']
		]);
	$products->save();
	return $products->first()->toArray();

//DELETE

	return $products = ProductModel::first(336)->delete();
	return $products = ProductModel::delete(478)


```


# SECTION
```php

//GET
	return MenuModel::filter(['=ID' => 20])->get();
	return MenuModel::orderBy('NAME', 'asc')->get();
	return MenuModel::where('ID', '!=', 18)->orderBy('NAME', 'desc')->take(2)->get();
	return MenuModel::orderBy('NAME', 'desc')->pagenate(2, 1)->get();

//RILATIONS
	return MenuModel::first(20)->parent()->get();
	return MenuModel::first(20)->children()->get();

//ADD
	$menu = new MenuModel([
			'NAME' => 'test_model_1'
		]);
	return $menu->save();
	
	
	$menu = new MenuModel();
	$menu->setFields([
			'NAME' => 'test_model_2'
		]);
	$menu->save();
	return $menu->toArray();

//UPADATE
	$menu = MenuModel::first(26)
		->setFields([
			'NAME' => 'update'
		])->save();
	return MenuModel::get();

//DELETE
	return MenuModel::first(25)->delete();
	return MenuModel::delete(25);


