<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->paginate(12);
            
        $categories = Category::orderBy('name')->get();
        
        return view('products.index', compact('products', 'categories'));
    }
    
    public function list(Request $request)
    {
        $query = Product::with('category');
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Apply category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        
        // Apply sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $products = $query->paginate(12);
        
        if ($request->ajax()) {
            $productsHtml = '';
            
            foreach ($products as $product) {
                $productsHtml .= view('components.product-card', compact('product'))->render();
            }
            
            return response()->json([
                'products' => $productsHtml,
                'pagination' => $products->links()->toHtml()
            ]);
        }
        
        return $products;
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
        
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'stock' => $request->stock,
            'status' => $request->status,
            'image' => $imagePath,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'product' => $product
        ]);
    }
    
    public function edit(Product $product)
    {
        // Load the category relationship
        $product->load('category');
        return response()->json($product);
    }
    
    public function update(Request $request, Product $product)
    {
        // For inline editing, we only validate the fields that are being updated
        $rules = [];
        $data = [];
        
        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:255';
            $data['name'] = $request->name;
        }
        
        if ($request->has('price')) {
            $rules['price'] = 'required|numeric|min:0';
            $data['price'] = $request->price;
        }
        
        if ($request->has('description')) {
            $rules['description'] = 'required|string';
            $data['description'] = $request->description;
        }
        
        if ($request->has('category_id')) {
            $rules['category_id'] = 'required|exists:categories,id';
            $data['category_id'] = $request->category_id;
        }
        
        if ($request->has('stock')) {
            $rules['stock'] = 'required|integer|min:0';
            $data['stock'] = $request->stock;
        }
        
        if ($request->has('status')) {
            $rules['status'] = 'required|in:Active,Inactive';
            $data['status'] = $request->status;
        }
        
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }
        
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product->update($data);
        
        // Reload the product with its category
        $product->load('category');
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => $product
        ]);
    }
    
    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully!'
        ]);
    }
}