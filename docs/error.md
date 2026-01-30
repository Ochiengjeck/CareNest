# Exception - Internal Server Error

Flux component [icon.arrow-right-on-rectangle] does not exist.

PHP 8.4.13
Laravel 12.49.0
localhost:8000

## Stack Trace

<!--[if BLOCK]><![endif]-->0 - vendor\livewire\flux\stubs\resources\views\flux\icon\index.blade.php:12
1 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:37
2 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38
3 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
4 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16
5 - vendor\laravel\framework\src\Illuminate\View\View.php:208
6 - vendor\laravel\framework\src\Illuminate\View\View.php:191
7 - vendor\laravel\framework\src\Illuminate\View\View.php:160
8 - vendor\laravel\framework\src\Illuminate\View\Concerns\ManagesComponents.php:103
9 - resources\views\components\dashboard\stat-card.blade.php:13
10 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:37
11 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38
12 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
13 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16
14 - vendor\laravel\framework\src\Illuminate\View\View.php:208
15 - vendor\laravel\framework\src\Illuminate\View\View.php:191
16 - vendor\laravel\framework\src\Illuminate\View\View.php:160
17 - vendor\laravel\framework\src\Illuminate\View\Concerns\ManagesComponents.php:103
18 - storage\framework\views\livewire\views\87bf043d.blade.php:36
19 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:37
20 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38
21 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
22 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16
23 - vendor\laravel\framework\src\Illuminate\View\View.php:208
24 - vendor\laravel\framework\src\Illuminate\View\View.php:191
25 - vendor\laravel\framework\src\Illuminate\View\View.php:160
26 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:377
27 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:428
28 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:369
29 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:70
30 - vendor\livewire\livewire\src\LivewireManager.php:102
31 - vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:19
32 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:118
33 - vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:14
34 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:36
35 - vendor\laravel\framework\src\Illuminate\Container\Util.php:43
36 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:96
37 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:35
38 - vendor\laravel\framework\src\Illuminate\Container\Container.php:799
39 - vendor\livewire\livewire\src\Features\SupportRouting\LivewirePageController.php:15
40 - vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:46
41 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:265
42 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:211
43 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:822
44 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
45 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authorize.php:59
46 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
47 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\EnsureEmailIsVerified.php:41
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
49 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
51 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
52 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
53 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
54 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
55 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
57 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
58 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
59 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
60 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
61 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
62 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
63 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
64 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
65 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
66 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
67 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
68 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
69 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
70 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
71 - vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19
72 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
73 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
74 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
75 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
76 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
77 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
78 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
79 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
80 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
81 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
82 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
83 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
84 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
85 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
86 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
87 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
88 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
89 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
90 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
91 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
92 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
93 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
94 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
95 - public\index.php:20
96 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23
<!--[if ENDBLOCK]><![endif]-->
## Request

GET /residents

## Headers

<!--[if BLOCK]><![endif]-->* **host**: localhost:8000
* **connection**: keep-alive
* **sec-ch-ua-platform**: "Windows"
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0
* **sec-ch-ua**: "Not(A:Brand";v="8", "Chromium";v="144", "Microsoft Edge";v="144"
* **dnt**: 1
* **x-livewire-navigate**: 1
* **sec-ch-ua-mobile**: ?0
* **accept**: */*
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: cors
* **sec-fetch-dest**: empty
* **referer**: http://localhost:8000/residents/1/edit
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: XSRF-TOKEN=eyJpdiI6IjQwYU9GS0FnNlk1SnVVN09kUldQeGc9PSIsInZhbHVlIjoibXNPU25IZXdLSVF6aUpqbmpUeUgwZEVFTWYrTEFYa0ZMb1pCWFc5enZrQjRSY1N5L0NLOUJpMklnaE5OeHVmRlpST1NDYmVYQVFWNWhFK3AwTWdyeXF3NDFnelBlZ1BidUhyNGErb2grU3dzYmpMYy9TQ0NJQ0JxMEZtUW9DVUYiLCJtYWMiOiIyZjIzYmYzYzIxMjc1ZGM1Njk1MjIzYzM1NmFiYzk0NDFkZTUwZTQxYzQ0MGZiNTBhNmVlNzVlN2NhN2JjMjE3IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IjZ4SVY1VDdkNCtDS0dQWGttbjlJQlE9PSIsInZhbHVlIjoiTXFiYy9mSTB6UjZXZ2V4QzhZMXpkS2tvak1PZVQxdjhkZnlUZ1BOTytVUEt2dFoyUUlHak5xSVB4LzdvYWdSNmUxcmx6SzhvSUV5WFBwU0treDJoSnJaSVlkZDR6cS9MaE9RVVgrTHJmYjBlc1dVa1A3TjNHTE53Y1ZiZzAxNXciLCJtYWMiOiIzODNlMGZmZWZjN2Y4N2E5MmE2ZTBmY2RmNmQ0ZTliODczOWJlZWI4ZmQ5YjZjZTkzOGY2MjU5MmY5Yjc3NGM2IiwidGFnIjoiIn0%3D
<!--[if ENDBLOCK]><![endif]-->
## Route Context

<!--[if BLOCK]><![endif]-->controller: Livewire\Features\SupportRouting\LivewirePageController
route name: residents.index
middleware: web, auth, verified, can:view-residents
<!--[if ENDBLOCK]><![endif]-->
## Route Parameters

<!--[if BLOCK]><![endif]-->No route parameter data available.
<!--[if ENDBLOCK]><![endif]-->
## Database Queries

<!--[if BLOCK]><![endif]-->* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_name') (4.17 ms)
* sqlite - select * from "system_settings" where "key" = 'system_name' limit 1 (0.35 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1769782082, 'laravel-cache-system_setting:system_name', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (82.1 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:logo_path') (5.67 ms)
* sqlite - select * from "system_settings" where "key" = 'logo_path' limit 1 (0.37 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1769782083, 'laravel-cache-system_setting:logo_path', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (9.11 ms)
* sqlite - select * from "sessions" where "id" = 'DKKIkPPIm2KmwZsd3Nt1iqFFUjtzEQLC9MGQrrf7' limit 1 (0.37 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.59 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-spatie.permission.cache') (0.33 ms)
* sqlite - select "permissions".*, "model_has_permissions"."model_id" as "pivot_model_id", "model_has_permissions"."permission_id" as "pivot_permission_id", "model_has_permissions"."model_type" as "pivot_model_type" from "permissions" inner join "model_has_permissions" on "permissions"."id" = "model_has_permissions"."permission_id" where "model_has_permissions"."model_id" in (1) and "model_has_permissions"."model_type" = 'App\Models\User' (0.4 ms)
* sqlite - select "roles".*, "model_has_roles"."model_id" as "pivot_model_id", "model_has_roles"."role_id" as "pivot_role_id", "model_has_roles"."model_type" as "pivot_model_type" from "roles" inner join "model_has_roles" on "roles"."id" = "model_has_roles"."role_id" where "model_has_roles"."model_id" in (1) and "model_has_roles"."model_type" = 'App\Models\User' (0.3 ms)
* sqlite - select count(*) as aggregate from "residents" where "residents"."deleted_at" is null (0.36 ms)
* sqlite - select count(*) as aggregate from "residents" where "status" = 'active' and "residents"."deleted_at" is null (0.29 ms)
* sqlite - select count(*) as aggregate from "residents" where "status" = 'on_leave' and "residents"."deleted_at" is null (0.24 ms)
* sqlite - select count(*) as aggregate from "residents" where "status" = 'discharged' and "residents"."deleted_at" is null (0.21 ms)
<!--[if ENDBLOCK]><![endif]-->