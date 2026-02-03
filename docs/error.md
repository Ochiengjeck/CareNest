# Error - Internal Server Error

Class "Livewire\Volt\Component" not found

PHP 8.4.13
Laravel 12.49.0
127.0.0.1:8000

## Stack Trace

0 - storage\framework\views\livewire\classes\d883ff83.php:6
1 - vendor\livewire\livewire\src\Compiler\CacheManager.php:58
2 - vendor\livewire\livewire\src\Compiler\Compiler.php:29
3 - vendor\livewire\livewire\src\Factory\Factory.php:60
4 - vendor\livewire\livewire\src\Factory\Factory.php:88
5 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:251
6 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:209
7 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:982
8 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:41
9 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
10 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
11 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
12 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
13 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
14 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
15 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
16 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
17 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
18 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
19 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
20 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
21 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
22 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
23 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
24 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
25 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
26 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
27 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
28 - vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19
29 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
30 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
31 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
32 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
33 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
34 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
35 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
36 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
37 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
38 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
39 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
40 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
41 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
42 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
43 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
44 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
45 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
46 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
47 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
49 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
50 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
51 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
52 - public\index.php:20
53 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /

## Headers

* **host**: 127.0.0.1:8000
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
* **referer**: http://127.0.0.1:8000/
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: _ga=GA1.1.147937518.1759909745; _ga_69MPZE94D5=GS2.1.s1767900065$o1$g0$t1767900261$j16$l0$h0; guest_token=eyJpdiI6Ijg2dXdidENRTTNJWEVCaWZrWjA3eGc9PSIsInZhbHVlIjoiSlpJRHBMSi9Beng1WHNuZUxLdDdOMzE5OWE5ZDhxYXYveXNJZjN1dkVENEkvOU9YNys1dWNlRUJNaTkrd0t0YmRuWmpyb0xvRzVnamRIcUZmbVl0NVdwQXJCWlkxT1ppSlZMZ0Zsc0txc3M9IiwibWFjIjoiYmVlYzgwMzUzZTNlODU1ODU4NjJlNTk1YWJkYzg5MDUzMzExOTgzMjY1MTQ0NjQxNjlkMjUzZjE0YWJkOTY2MSIsInRhZyI6IiJ9; _ga_699NE13B0K=GS2.1.s1768374624$o7$g0$t1768374624$j60$l0$h0; XSRF-TOKEN=eyJpdiI6ImFYMmxTNEFVd25Cam15TWowMlBCbHc9PSIsInZhbHVlIjoiTTF0QjBrMUZQcFpyTEo3NFpJeVVxeXo3NzdhTHlMOVV0QlY0YXZXYU5PSVczK2NXRWFrOUFTejYxdTd6NVp4MUFPL2k4R3pqWGtSWFpBbUtWNU9NaUgySGFPclVwTjVXOExHbkFLMFUzZFRTc3RpMXV1TTZQRjNhSi9QVm82OTgiLCJtYWMiOiJmOWM1YzY0NzIyYzg5NjYzNmJkNmI2NGUxODQwOWQzMDJkYjNiNTAwODA5NmEwZDc1ZmUzZmFkNTNhNTAwMjQ5IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IlhpcmEzSVRzUW5HUUx1VFlRRm1tQWc9PSIsInZhbHVlIjoiMkVVd3hKYU9QdkFLOG9nRld1SmxwQS9lZUZGb0V1Z0tRYzQvZ2hpbE9KRDRpd0dUTmgrOC9MVkZBTWpoTGVvRDJpWnRvR0F1SFRNdkgxS09rRjJIV09YQzdPanZRVkZIeklkVm5NMDY1ZjEycGpWQkVSaDZURGZpb09PZEMvMUkiLCJtYWMiOiI5MzFhNTY3NjZkYWY2ODZhNzAwNWU1MTUxYWU2OTA0OTcyNjNhYTIwZTEzZWM1ZTdlYjgwZDZlZGM5ZjYxNmM4IiwidGFnIjoiIn0%3D

## Route Context

controller: Livewire\Features\SupportRouting\LivewirePageController
route name: home
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_name') (13.62 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:logo_path') (3.12 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_tagline') (0.52 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:favicon_path') (0.52 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:active_theme') (0.87 ms)
* sqlite - select * from "sessions" where "id" = 'rkrxZB3u5XPVPzfT7NF0KfGsbfXIwulJ9yYv5sAo' limit 1 (3.1 ms)
