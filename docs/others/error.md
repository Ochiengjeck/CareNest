# ParseError - Internal Server Error

syntax error, unexpected token "?"

PHP 8.4.18
Laravel 12.53.0
carehome.up.railway.app

## Stack Trace

<!--[if BLOCK]><![endif]-->0 - storage/framework/views/livewire/views/71948f5b.blade.php:420
1 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:38
2 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
3 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:16
4 - vendor/laravel/framework/src/Illuminate/View/View.php:208
5 - vendor/laravel/framework/src/Illuminate/View/View.php:191
6 - vendor/laravel/framework/src/Illuminate/View/View.php:160
7 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:409
8 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:460
9 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:401
10 - vendor/livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:80
11 - vendor/livewire/livewire/src/LivewireManager.php:102
12 - resources/views/dashboard.blade.php:1
13 - vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php:123
14 - vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php:124
15 - vendor/laravel/framework/src/Illuminate/View/Engines/PhpEngine.php:57
16 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:22
17 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
18 - vendor/livewire/livewire/src/Mechanisms/ExtendBlade/ExtendedCompilerEngine.php:10
19 - vendor/laravel/framework/src/Illuminate/View/View.php:208
20 - vendor/laravel/framework/src/Illuminate/View/View.php:191
21 - vendor/laravel/framework/src/Illuminate/View/View.php:160
22 - vendor/laravel/framework/src/Illuminate/Http/Response.php:78
23 - vendor/laravel/framework/src/Illuminate/Http/Response.php:34
24 - vendor/laravel/framework/src/Illuminate/Routing/ResponseFactory.php:61
25 - vendor/laravel/framework/src/Illuminate/Routing/ResponseFactory.php:91
26 - vendor/laravel/framework/src/Illuminate/Routing/ViewController.php:40
27 - vendor/laravel/framework/src/Illuminate/Routing/ViewController.php:57
28 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:43
29 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
30 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
31 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
33 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php:41
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
43 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
44 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
46 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
50 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
51 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
52 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
53 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
54 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
55 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
56 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
57 - vendor/livewire/livewire/src/Features/SupportDisablingBackButtonCache/DisableBackButtonCacheMiddleware.php:19
58 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
59 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
60 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
61 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
62 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
63 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
64 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
65 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
66 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
67 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
68 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
69 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
70 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
71 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
72 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
73 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
74 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
75 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
76 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
77 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
78 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
79 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
80 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
81 - public/index.php:20
82 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23
<!--[if ENDBLOCK]><![endif]-->
## Request

GET /dashboard

## Headers

<!--[if BLOCK]><![endif]-->* **host**: carehome.up.railway.app
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **accept-encoding**: gzip
* **accept-language**: en-US,en;q=0.9
* **backend-is-origin**: 1
* **cdn-loop**: Fastly
* **cookie**: XSRF-TOKEN=eyJpdiI6InQ5QjBMWFMzcDRFY0UyTVpSMXdOc0E9PSIsInZhbHVlIjoiSE1FaEpiYUh3SlV4RnJMeGhTTXIrRWZUUzEwTWxJRUVURWN4aXhFV0loUHVnMkdxTDIydmJhRWltamFVLzlQazUraHhoQ0thc0FCNWlIVlU2eDRzNmc1Z29pWjlRTzRxQ1dFRE84VmtKUHRtZ2lFUEFnbW94R2RGand6c0EzOE0iLCJtYWMiOiJmMTRiYmIyZmQ1MmNhNWFlMjMxNTZkYjc1NGQ2NzA2MzAyMDRkNDdmM2M0ZTZlYjIzMTI4YmJmNThkMzRiMmM1IiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IklKZW9EOG5ybWVwaWFScS9CYmFlT2c9PSIsInZhbHVlIjoidDVjYVpucitGa1NGRm5KdVNBUWtGcldheHVSUTJQMlU1clk2d1UwcEFzTHNuaklYTEx0eWVVd0Judjc0ZWdTMUExQjFnM1pOU1ZqZnJRcW5oenhTclV2d1BSSzdOVmd0Z21jY1dwbUpibEpiMlphZUdVMXBWeU5ZbnFGTUQ5eHEiLCJtYWMiOiI2ODUxNjc4NzQ5MTVkZTQ4Y2JlN2JhZWRlNWQ5Zjk2ZTU3MTRmOTdkMmJmYmVmMmIxYzYyNjNlOTcwZDE5MjU5IiwidGFnIjoiIn0%3D
* **dnt**: 1
* **fastly-cachetype**: PASS
* **fastly-client-ip**: 41.90.96.15
* **fastly-orig-accept-encoding**: gzip, deflate, br, zstd
* **fastly-ssl**: 1
* **lei-timing**: misspass=142
* **priority**: u=0, i
* **referer**: https://carehome.up.railway.app/
* **sec-ch-ua**: "Not:A-Brand";v="99", "Microsoft Edge";v="145", "Chromium";v="145"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **sec-fetch-dest**: document
* **sec-fetch-mode**: navigate
* **sec-fetch-site**: same-origin
* **sec-fetch-user**: ?1
* **upgrade-insecure-requests**: 1
* **x-edge-region**: EU-West
* **x-forwarded-for**: 41.90.96.15, 104.156.93.100
* **x-forwarded-host**: carehome.up.railway.app
* **x-forwarded-proto**: https
* **x-forwarded-server**: cache-mrs1050100-MRS
* **x-railway-edge**: railway/europe-west4-drams3a
* **x-railway-request-id**: u4upDu9zRIeKmQYmw9P4nw
* **x-real-ip**: 104.156.93.100
* **x-request-start**: 1772519898042
* **x-timer**: S1772519896.075433,VS0
* **x-varnish**: 3710521194
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

<!--[if BLOCK]><![endif]-->* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_name') (1.57 ms)
* sqlite - select * from "system_settings" where "key" = 'system_name' limit 1 (0.1 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1772523498, 'laravel-cache-system_setting:system_name', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (7.1 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:logo_path') (0.1 ms)
* sqlite - select * from "system_settings" where "key" = 'logo_path' limit 1 (0.07 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1772523498, 'laravel-cache-system_setting:logo_path', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (4.17 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_tagline') (0.07 ms)
* sqlite - select * from "system_settings" where "key" = 'system_tagline' limit 1 (0.04 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1772523498, 'laravel-cache-system_setting:system_tagline', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (6.68 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:favicon_path') (0.07 ms)
* sqlite - select * from "system_settings" where "key" = 'favicon_path' limit 1 (0.04 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1772523498, 'laravel-cache-system_setting:favicon_path', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (5.16 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:active_theme') (0.08 ms)
* sqlite - select * from "system_settings" where "key" = 'active_theme' limit 1 (0.05 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1772523498, 'laravel-cache-system_setting:active_theme', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (4.02 ms)
* sqlite - select * from "sessions" where "id" = 'xhQDFCHfdpI6jDrj2fvymA4rxNsW4g7ebmgvM7ee' limit 1 (0.12 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.11 ms)
<!--[if ENDBLOCK]><![endif]-->