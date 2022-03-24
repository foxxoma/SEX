# SEX
```
//GET
	return ProductModel::filter(['ID' => 336])->get();
	return ProductModel::get();
	return ProductModel::orderBy('NAME', 'desc')->get();
	return ProductModel::where('ID', '!=', 336)->orderBy('NAME', 'desc')->take(2)->get();
	return ProductModel::orderBy('NAME', 'desc')->pagenate(1, 2)->get();

// UPDATE
	$products = ProductModel::first(336)
		->setFields([
			'FIELDS' => ['NAME' => 'name5'],
			'PROPERTIES_VALUES' => ['WIDTH' => '46']
		])->save();

//ADD
	$products = new ProductModel([
			'FIELDS' => ['NAME' => 'test_model_3'],
			'PROPERTIES_VALUES' => ['WIDTH' => '48']
		]);
	return $products->save();

	$products = new ProductModel();
	$products->setFields([
			'FIELDS' => ['NAME' => 'test_model_7'],
			'PROPERTIES_VALUES' => ['WIDTH' => '47']
		]);
	$products->save();
	return $products->first()->toArray();
	
//DELETE
	return $products = ProductModel::first(336)->delete();
	return $products = ProductModel::first(476)->delete(477);
	return $products = ProductModel::delete(478

