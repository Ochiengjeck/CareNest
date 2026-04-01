# ParseError - Internal Server Error

Unclosed '[' does not match ')'

PHP 8.4.13
Laravel 12.53.0
localhost:8000

## Stack Trace

<!--[if BLOCK]><![endif]-->0 - storage\framework\views\livewire\views\8676e0f6.blade.php:73
1 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38
2 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
3 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16
4 - vendor\laravel\framework\src\Illuminate\View\View.php:208
5 - vendor\laravel\framework\src\Illuminate\View\View.php:191
6 - vendor\laravel\framework\src\Illuminate\View\View.php:160
7 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:409
8 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:460
9 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:401
10 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:80
11 - vendor\livewire\livewire\src\LivewireManager.php:102
12 - resources\views\dashboard.blade.php:1
13 - vendor\laravel\framework\src\Illuminate\Filesystem\Filesystem.php:123
14 - vendor\laravel\framework\src\Illuminate\Filesystem\Filesystem.php:124
15 - vendor\laravel\framework\src\Illuminate\View\Engines\PhpEngine.php:57
16 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:22
17 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
18 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:10
19 - vendor\laravel\framework\src\Illuminate\View\View.php:208
20 - vendor\laravel\framework\src\Illuminate\View\View.php:191
21 - vendor\laravel\framework\src\Illuminate\View\View.php:160
22 - vendor\laravel\framework\src\Illuminate\Http\Response.php:78
23 - vendor\laravel\framework\src\Illuminate\Http\Response.php:34
24 - vendor\laravel\framework\src\Illuminate\Routing\ResponseFactory.php:61
25 - vendor\laravel\framework\src\Illuminate\Routing\ResponseFactory.php:91
26 - vendor\laravel\framework\src\Illuminate\Routing\ViewController.php:40
27 - vendor\laravel\framework\src\Illuminate\Routing\ViewController.php:57
28 - vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:43
29 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:265
30 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:211
31 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:822
32 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
33 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\EnsureEmailIsVerified.php:41
34 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
35 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
36 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
37 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
38 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
39 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
40 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
41 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
42 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
43 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
44 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
45 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
46 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
47 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
48 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
49 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
51 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
52 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
53 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
54 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
55 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
57 - vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19
58 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
59 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
60 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
61 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
62 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
63 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
64 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
65 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
66 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
67 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
68 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
69 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
70 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
71 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
72 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
73 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
74 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
75 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
76 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
77 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
78 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
79 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
80 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
81 - public\index.php:20
82 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23
<!--[if ENDBLOCK]><![endif]-->
## Request

GET /dashboard

## Headers

<!--[if BLOCK]><![endif]-->* **host**: localhost:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Chromium";v="146", "Not-A.Brand";v="24", "Microsoft Edge";v="146"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **dnt**: 1
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/dashboard
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IlMrY1VJeWlnaEFmeXVWTEdwak9xL0E9PSIsInZhbHVlIjoib00yTUZMUzh6ckpZZ0lEOXhNVnFwUUNhRnQ0dCtvQmVXLytnZCtyN2JrZXRlTDNaaGovUUxpTktFMlMrNm4wUlhzQ0RwNWxHRUZGc21OejM4ZkZ6bklPYnFWajYwUkRrYUQ3RWVDL3pyZzQ4cHExVEx2cXFDS3dYYk1JQk1wT05UU1V4eFNiZ1RQbWR4NngxVllyUi92VjRuZjc1cVZZbksrY3dtLzV5RFlSUGNoYjFra0xsa0M2ck80aS9scWZ2K3dld0JRVVh2MUlITExlTVpESG5ZQ3BsY2h3R3ZXbTNnUEFnMlI0blhibz0iLCJtYWMiOiIwN2FjNGQyMDlkYjJiZjRhMTlmMzA0MjVjMWJlMjMxNmUxMWI3OTk3Y2FhZmQyMjYzODZkZDRjN2M0YmVhZDVkIiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6Inl2T1A0N1orMFB0YVdud25RWGUwSnc9PSIsInZhbHVlIjoiZDZ0dFFDSGJLNE11TzRhUHlxTjVRaks2WTNCU3FFOS9sMzhjRzhYNVhjaDJpTW14cFl2anNLNHViZ3p0dmg5SFNudUJMVHRMSHBscmpWVU1WZG1tRTRaZG5lU3dyUmJKYUpjQlltR0dUSmdCTFVDZCt0K3ExeVA0SHViTTdFb0YiLCJtYWMiOiI0Zjc2ZmRiN2Y0NzczOGNkYTc1MjJhOWEwMTA1NGM5MWEzNzZlYWRkYTM3YTFlOTIzODE3NWYzMzVmYjE0YzczIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IkR3TjFEZFplQS8xQk5BdXRvVHY0dHc9PSIsInZhbHVlIjoiaEYrK0hqaDN4bkdsRFk5VnNQVjVROVdVd1dpaXRsNHVkSitibGZKemo5OEx4bElkU0dhRkQrcXc5NWZUaExOVEhOZUFXbTZPNlNLQmRxejU0THZ2VUtwckVWSVZ5YWxkaWErOWZ2OTVkRWRoVnVwYlkreityTGZlblBaKzk1QjkiLCJtYWMiOiJiZTJmYzA4MjhmMzZhN2RmZjIzMWQ4NjNjM2FkMWE5NDc2NjRjNjUxYzk3YmQ4YzNiMDI3M2VlN2MxMmFmNDg4IiwidGFnIjoiIn0%3D
<!--[if ENDBLOCK]><![endif]-->
## Route Context

<!--[if BLOCK]><![endif]-->controller: \Illuminate\Routing\ViewController
route name: dashboard
middleware: web, auth, verified
<!--[if ENDBLOCK]><![endif]-->
## Route Parameters

<!--[if BLOCK]><![endif]-->{
    "view": "dashboard",
    "data": [],
    "status": 200,
    "headers": []
}
<!--[if ENDBLOCK]><![endif]-->
## Database Queries

<!--[if BLOCK]><![endif]-->* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_name') (12.38 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:logo_path') (1.08 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_tagline') (1.08 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:favicon_path') (1.02 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:active_theme') (1.96 ms)
* sqlite - select * from "sessions" where "id" = 'oeuSoy4hhtruWThdQQXptbC1E3XubN8Zr6iVBrf1' limit 1 (1.02 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (1 ms)
<!--[if ENDBLOCK]><![endif]-->