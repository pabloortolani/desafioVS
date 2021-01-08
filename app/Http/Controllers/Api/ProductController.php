<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Products;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Products $request)
    {

        $createProduct = Product::create($request->all());

        if(isset($createProduct->id)){
            return response()->json(
                [
                    "result"=>1,
                    "message" => "Produto cadastrado com sucesso.",
                    "data"=>$createProduct->toArray()
                ],
                200
            );
        }else{
            return response()->json(
                [
                    "result"=>0,
                    "message" => "Erro ao cadastrar o produto."
                ], 500
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Verifica se o produto existe
        $product = Product::find($id);
        if(empty($product)){
            return response()->json(
                [
                    "result"=>0,
                    "errors" => "Produto não encontrado"
                ], 400
            );
        }

        return response()->json(
            [
                "result"=>1,
                "message" => "Produto encontrado com sucesso.",
                "data"=>$product->makeHidden(['created_at', 'updated_at'])
            ],
            200
        );

    }

    /**
     * search products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {

        $products = null;
        $sortname = null;
        $sortorder = null;
        $validFields = ['name', 'brand', 'price', 'stock_quantity'];

        //filtro Principal
        if($request->has('q')){
            //Verifica se o produto existe
            $products = Product::where("name","like",'%' .$request->q. '%');
        }

        //filtro opcional
        if($request->has('q') && $request->has('filter')){

            $fieldArray = explode(":", $request->filter);
            $fieldItem = (isset($fieldArray[0]) && !empty($fieldArray[0]) && in_array($fieldArray[0], $validFields) ? $fieldArray[0] : null);
            $fieldValue = (isset($fieldArray[1]) && !empty($fieldArray[1]) ? $fieldArray[1] : null);

            //Formata a moeda para pesquisar corretamente no BD
            if(!empty($fieldItem) && $fieldItem == 'price'){
                $fieldValue = str_replace(',', '.', str_replace('.', '', $fieldValue));
            }

            if(!empty($fieldItem) && !empty($fieldValue)) {
                $products->where($fieldItem, 'like', '%' . $fieldValue . '%');
            }
        }

        //sorting
        if($request->has('q') && $request->has('sortname')){
            if(!empty($request->sortname) && in_array($request->sortname, $validFields)) {
                $products->orderby($request->sortname, (isset($request->sortorder) && $request->sortorder == 'DESC' ? 'DESC' : 'ASC'));
            }
        }

        if(!empty($products)){
            $products = $products->paginate(2);
        }

        if(!empty($products) && $products->total() > 0){
            return response()->json(
                [
                    "result"=>1,
                    "message" => "Produtos encontrados.",
                    "data"=>$products
                ],
                200
            );
        }else{
            return response()->json(
                [
                    "result"=>0,
                    "errors" => "Nenhum Produto encontrado",
                    "data"=>$products
                ], 400
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //Verifica se o produto existe
        $product = Product::find($id);
        if(empty($product)){
            return response()->json(
                [
                    "result"=>0,
                    "errors" => "Produto não encontrado"
                ], 400
            );
        }

        //Valida somente os campos que foram informados para atualizar
        $rules = array();
        if($request->has('name')){
            $rules["name"] = "required";
            $product->name = $request->name;
        }
        if($request->has('brand')){
            $rules["brand"] = "required";
            $product->brand = $request->brand;
        }
        if($request->has('price')){
            $rules["price"] = "required";
            $product->price = $request->price;
        }
        if($request->has('stock_quantity')){
            $rules["stock_quantity"] = "required|integer";
            $product->stock_quantity = $request->stock_quantity;
        }
        $messages = [
            "name.required"=>"Por favor, informe o nome do Produto",
            "brand.required"=>"Por favor, informe a marca do Produto",
            "price.required"=>"Por favor, informe o preço do Produto",
            "stock_quantity.required"=>"Por favor, informe a quantidade em estoque do Produto",
            "stock_quantity.integer"=>"Somente números inteiros pode ser informado na quantidade em estoque",
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if($validate->fails()){
            return response()->json(
                [
                    "result"=>0,
                    "errors" => $validate->messages()
                ], 400
            );
        }

        if($product->save()){
            return response()->json(
                [
                    "result"=>1,
                    "message" => "Produto atualizado com sucesso.",
                    "data"=>$product->toArray()
                ],
                200
            );
        }else{
            return response()->json(
                [
                    "result"=>0,
                    "message" => "Erro ao atualizar o produto."
                ], 500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Verifica se o produto existe
        $product = Product::find($id);
        if(empty($product)){
            return response()->json(
                [
                    "result"=>0,
                    "errors" => "Produto não encontrado"
                ], 400
            );
        }

        if($product->delete()){
            return response()->json(
                [
                    "result"=>1,
                    "message" => "Produto excluído com sucesso."
                ],
                200
            );
        }else{
            return response()->json(
                [
                    "result"=>0,
                    "errors" => "Erro ao escluir o produto."
                ], 400
            );
        }

    }
}
