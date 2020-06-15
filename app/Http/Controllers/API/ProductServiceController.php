<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;

use App\Category;
use App\Product;
use App\Material;
use App\Colour;
use App\Size;
use App\Photo;

class ProductServiceController extends BaseController
{
    // Get all categories
    public function getCategories()
    {
        $categories = Category::all();

        return $this->sendResponse($categories->toArray(), 'Categories retrieved successfully.');
    }


    // Get first category
    public function getFirstCategory()
    {
        $category = Category::first();
    
        return $this->sendResponse($category->toArray(), 'Category retrieved successfully.');
    }


    // Show category's details (including products)
    public function showCategory($id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $data = [
            "id" => $category->id,
            "category" => $category->category,
            "created_at" => $category->created_at,
            "updated_at" => $category->updated_at,
            "products" => $category->products
        ];

        return $this->sendResponse($data, 'Category retrieved successfully.');
    }


    // Show category's thumbnail
    public function showCategoryThumbnail($id)
    {
        $category = Category::find($id);

        $thumbnail = $category->products->first()->photos->first();
        
        return $this->sendResponse($thumbnail->toArray(), "Category's thumbnail retrieved successfully.");
    }


    // Show category's thumbnail
    public function showCategoryProducts(Request $request, $id)
    {
        $category = Category::find($id);

        $products = Product::where('category_id', $category->id)->get();

        if ($request->has('orderby'))
        {
            if ($request['orderby'] == 'name') {
                $products = Product::where('category_id', $category->id)->orderBy('name')->get();
            } elseif ($request['orderby'] == 'name_desc') {
                $products = Product::where('category_id', $category->id)->orderBy('name', 'desc')->get();
            } elseif ($request['orderby'] == 'price') {
                $products = Product::where('category_id', $category->id)->orderBy('price')->get();
            } elseif ($request['orderby'] == 'price_desc') {
                $products = Product::where('category_id', $category->id)->orderBy('price', 'desc')->get();
            } 
        }

        return $this->sendResponse($products, "Category's products retrieved successfully.");
    }


    // Get all products
    public function getProducts()
    {
        $products = Product::all();

        return $this->sendResponse($products->toArray(), 'Products retrieved successfully.');
    }


    // Get product data (including related) by id
    public function showProduct($id)
    {
        $product = Product::find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        $data = [
            'id'=> $product->id,
            'category_id' => $product->category_id,
            'name'=> $product->name,
            'description'=> $product->description,
            'price'=> $product->price,
            'available'=> $product->available,
            'created_at'=> $product->created_at,
            'updated_at'=> $product->updated_at,
            'category'=> $product->category,
            'materials'=> $product->materials,
            'colours'=> $product->colours,
            'sizes'=> $product->sizes,
            'photos'=> $product->photos,
        ];

        return $this->sendResponse($data, 'Product retrieved successfully.');
    }

    
    // Show product's thumbnail
    public function showProductThumbnail($id)
    {
        $thumbnail = Product::find($id)->photos()->first();

        if (is_null($thumbnail)) {
            return $this->sendError("Product's thumbnail not found.");
        }

        return $this->sendResponse($thumbnail->toArray(), "Product's thumbnail retrieved successfully.");
    }


    // Store product
    public function storeProduct(Request $request)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'category' => 'required|max:30',
            'name' => 'required|max:40',
            'description' => 'required',
            'price' => 'required|max:10',
            'materials' => 'array|required',
            'materials.*' => 'required|max:40',
            'colours' => 'array|required',
            'colours.*' => 'required|max:40',
            'sizes' => 'array|required',
            'sizes.*' => 'required|max:20',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        // Check Category
        $category_inp = $input['category'];
        $category_result = Category::where('category', $category_inp)->first();
        // If category does not exist, create a new one.
        if ($category_result == null){
            $category = new Category;
            $category->category = $category_inp;
            $category->save();
            $category_id = $category->id;
        } else {
            $category_id = $category_result->id;
        }

        // Create Product
        $product = new Product;
        $product->category_id = $category_id;
        $product->name = $input['name'];
        $product->description = $input['description'];
        $product->price = $input['price'];
        $product->save();

        // Check Mateials
        $materials_inp = $input['materials'];
        foreach ($materials_inp as $material_inp){
            $material_result = Material::where('material', $material_inp)->first();
            // Check if material exist, if not, create a new one.
            if ($material_result == null){
                $material = new Material;
                $material->material = $material_inp;
                $material->save();
                $product->materials()->attach($material);
            } else {
                $product->materials()->attach($material_result);
            }
        }

        // Check Colours
        $colours_inp = $input['colours'];
        foreach ($colours_inp as $colour_inp){
            $colour_result = Colour::where('colour', $colour_inp)->first();
            // Check if colour exist, if not, create a new one.
            if ($colour_result == null){
                $colour = new Colour;
                $colour->colour = $colour_inp;
                $colour->save();
                $product->colours()->attach($colour);
            } else {
                $product->colours()->attach($colour_result);
            }
        }

        // Check Sizes
        $sizes_inp = $input['sizes'];
        foreach ($sizes_inp as $size_inp){
            $size_result = Size::where('size', $size_inp)->first();
            // Check if size exist, if not, create a new one.
            if ($size_result == null){
                $size = new Size;
                $size->size = $size_inp;
                $size->save();
                $product->sizes()->attach($size);
            } else {
                $product->sizes()->attach($size_result);
            }
        }

        return $this->sendResponse($product->toArray(), 'Product stored successfully.');
    }


    // Update product
    public function updateProduct(Request $request, Product $product)
    {
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'category' => 'required|max:30',
            'name' => 'required|max:40',
            'description' => 'required',
            'price' => 'required|max:10',
            'materials' => 'array|required',
            'materials.*' => 'required|max:40',
            'colours' => 'array|required',
            'colours.*' => 'required|max:40',
            'sizes' => 'array|required',
            'sizes.*' => 'required|max:20',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        // Check Category
        $category_result = Category::where('category', $input['category'])->first();
        // If category does not exist, create a new one.
        if ($category_result == null) { 
            $category = new Category;
            $category->category = $input['category'];
            $category->save();
            $category_id = $category->id;
        } else {
            $category_id = $category_result->id;
        }

        $product->category_id = $category_id;
        $product->name = $input['name'];
        $product->description = $input['description'];
        $product->price = $input['price'];

        // Detach all materials from product
        $product->materials()->detach();

        // Check Mateials
        foreach ($input['materials'] as $material_inp){
            $material_result = Material::where('material', $material_inp)->first();
            // Check if material exist, if not, create a new one.
            if ($material_result == null) {
                $material = new Material;
                $material->material = $material_inp;
                $material->save();
                $product->materials()->attach($material);
            } else {
                $product->materials()->attach($material_result);
            }
        }

        // Detach all colours from product
        $product->colours()->detach();

        // Check Colours
        foreach ($input['colours'] as $colour_inp){
            $colour_result = Colour::where('colour', $colour_inp)->first();
            // Check if colour exist, if not, create a new one.
            if ($colour_result == null){
                $colour = new Colour;
                $colour->colour = $colour_inp;
                $colour->save();
                $product->colours()->attach($colour);
            } else {
                $product->colours()->attach($colour_result);
            }
        }

        // Detach all sizes from product
        $product->sizes()->detach();

        // Check Sizes
        foreach ($input['sizes'] as $size_inp){
            $size_result = Size::where('size', $size_inp)->first();
            // Check if size exist, if not, create a new one.
            if ($size_result == null){
                $size = new Size;
                $size->size = $size_inp;
                $size->save();
                $product->sizes()->attach($size);
            } else {
                $product->sizes()->attach($size_result);
            }
        }

        $product->save();

        return $this->sendResponse($product->toArray(), 'Product updated successfully.');
    }


    // Destroy product
    public function destroyProduct($id)
    {
        $product = Product::findorfail($id);

        // Delete Photos
        $photos = $product->photos;
        foreach ($photos as $photo)
        {
            $img_path = 'uploads/images/'.$photo->file;
            unlink($img_path);
        }

        $product->delete();

        return $this->sendResponse($product->toArray(), 'Product deleted successfully.');
    }


    // Destroy product photos
    public function destroyProductPhotos($id)
    {
        $product = Product::findorfail($id);

        // Delete Photos
        $photos = $product->photos;
        foreach ($photos as $photo)
        {
            $img_path = 'uploads/images/'.$photo->file;
            unlink($img_path);
        }

        $product->photos()->delete();

        return $this->sendResponse($product->toArray(), "Product's photos deleted successfully.");
    }


    // Get all materials
    public function getMaterials()
    {
        $materials = Material::all();

        return $this->sendResponse($materials->toArray(), 'Materials retrieved successfully.');
    }


    // Get all colours
    public function getColours()
    {
        $colours = Colour::all();

        return $this->sendResponse($colours->toArray(), 'Colours retrieved successfully.');
    }


    // Show colour
    public function showColour($id)
    {
        $colour = Colour::find($id);

        if (is_null($colour)) {
            return $this->sendError("Colour not found.");
        }

        return $this->sendResponse($colour->toArray(), "Colour retrieved successfully.");
    }


    // Get all sizes
    public function getSizes()
    {
        $sizes = Size::all();

        return $this->sendResponse($sizes->toArray(), 'Sizes retrieved successfully.');
    }


    // Show size
    public function showSize($id)
    {
        $size = Size::find($id);

        if (is_null($size)) {
            return $this->sendError("Size not found.");
        }

        return $this->sendResponse($size->toArray(), "Size retrieved successfully.");
    }


    // Store photo
    public function storePhoto(Request $request)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'product_id' => 'required',
            'image' => 'required'
        ]);

        $image = $request->file('image');
        
        $ext = $image->getClientOriginalExtension();
        
        while(true){
            $newName = rand(100000,1001238912).".".$ext;

            if (!file_exists('uploads/images/'.$newName)){
                $image->move('uploads/images', $newName);
                break;
            }
        }

        $photo = new Photo;
        $photo->product_id = $request['product_id'];
        $photo->file = $newName;
        $photo->save();

        return $this->sendResponse($photo->toArray(), 'Image stored successfully.');
    }
}
