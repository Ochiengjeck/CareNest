# ParseError - Internal Server Error

syntax error, unexpected token "return"

PHP 8.4.13
Laravel 12.49.0
localhost:8000

## Stack Trace

0 - storage\framework\views\livewire\classes\87ef8d93.php:12
1 - vendor\livewire\livewire\src\Compiler\Compiler.php:29
2 - vendor\livewire\livewire\src\Factory\Factory.php:60
3 - vendor\livewire\livewire\src\Factory\Factory.php:88
4 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:251
5 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:209
6 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:982
7 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:41
8 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
9 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
10 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
11 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
12 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
13 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
14 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
15 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
16 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
17 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
18 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
19 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
20 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
21 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
22 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
23 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
24 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
25 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
26 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
27 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
28 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
29 - vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19
30 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
31 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
32 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
33 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
34 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
35 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
36 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
37 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
38 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
39 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
40 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
41 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
42 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
43 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
44 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
45 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
46 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
47 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
49 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
50 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
51 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
52 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
53 - public\index.php:20
54 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /dashboard

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Not(A:Brand";v="8", "Chromium";v="144", "Microsoft Edge";v="144"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **dnt**: 1
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/dashboard
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: XSRF-TOKEN=eyJpdiI6ImxnODcyL2JBcEpxbWJjTHhwUVc0SGc9PSIsInZhbHVlIjoiZk5NY1krbTg5Q3M2OVhJZ3FRTlpjeUJmREYwYlBybGxmektiNnpwalVUcDQ4amxPL2Y2M2tBMFNHVklDZWt1ZXRIQnE1d0lYSXMxNDRHU0syK01sYTJOd3BMRTZTcE9SM2ExODM3MHBQVzNxRE95clFsOGpvRHJRdUczUVYyc20iLCJtYWMiOiIzMGQxOGY4NmY3ZTYxMjU2OWM3MjAxNGM2ZWNhM2FhZjgzOTY1MDI2M2E5YTRlOTU2OGQ3MDU1MzM0Mzk0MzI5IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IkhwR2NFU3Y0cVNFN01hV2wxWWNBd0E9PSIsInZhbHVlIjoieDd5R1dCTXlCc291MTduTHVaNnRBU3lFV3d0Ym1YWHg3ZEVWWG9PejVlWnE4emNUZnZrd1MxUjhENDJOdHFzQnllVmRzV2ZDK2cyZ0FaVjRwUkhSZnFsdHRrTGtESGpMMERSNzVyYVI5cEYvY3huK1hDcDNyNjFwRG5zTW9kRjEiLCJtYWMiOiI1MTE0YzAyOTkzYjFhNjRjNTY3NjNlN2U0M2E3Mjc0MWFhNzU3ZWQ4NjU4YmIxNDE2NTk4YTJiYWRkMWZiY2MxIiwidGFnIjoiIn0%3D

## Route Context

controller: Livewire\Features\SupportRouting\LivewirePageController
route name: dashboard
middleware: web, auth, verified

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "sessions" where "id" = 'dk9KRYNAzk9vHSUD54i6mnYSvHAqhApNFfi6Nfi2' limit 1 (1.81 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.37 ms)
