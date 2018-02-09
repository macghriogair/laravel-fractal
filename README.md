# Laravel Fractal

A simple Fractal wrapper for Laravel.

Fractal provides a presentation and transformation layer for complex data output in HTTP APIs.

For more info on Fractal, see:

https://fractal.thephpleague.com

This package was adapted and inspired by Freek Van der Herten's Fractalistic package for Laravel: https://github.com/spatie/fractalistic/

## Installation

Via Composer

    composer require macghriogair/laravel-fractal

## Usage

```
    // Get Instance
    $fractal = Macgriog\Fractal\Fractal::create()
    // Configure Transformer
    $fractal->setTransformer(new ItemTransformer());

    // Single Item
    $fractal->item($item)->respond();
    
    // Collection
    $fractal->collection($collection)->respond();
    
    // With Pagination
    $fractal->collection($collection)
        ->setPaginator($paginator)
        ->respond();
```

Full Controller example:

``` 
    <?php 

    use Macgriog\Fractal\Fractal;
    use Macgriog\Fractal\FractalRequestTrait;

    class OrderController extends Controller {
        
        use FratalRequestTrait;

        /**
         * List all orders.
         *
         * @param Request $request
         * @return JsonResponse
         */
        public function index(Request $request, Fractal $fractal)
        {   
            // or: $fractal = Fractal::create(); // or: app('fractal')

            // Set implementation of League\Fractal\TransformerAbstract
            $fractal->transformWith(new OrderTransformer());

            if ($request->has('include')) {
                $this->fractal->parseIncludes($request->get('include'));
            }

            $paginator = Order::with(['some_relation'])
                ->paginate(25)
                ->appends($this->queryParams());

            return $fractal->collection($paginator->getCollection())
                ->setPaginator($paginator)
                ->respond();
        }

        /*...*/
```


