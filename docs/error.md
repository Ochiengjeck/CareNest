# Exception - Internal Server Error

Flux component [icon.external-link] does not exist.

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
9 - vendor\livewire\flux\stubs\resources\views\flux\button\index.blade.php:1
10 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:37
11 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:38
12 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
13 - vendor\livewire\livewire\src\Mechanisms\ExtendBlade\ExtendedCompilerEngine.php:16
14 - vendor\laravel\framework\src\Illuminate\View\View.php:208
15 - vendor\laravel\framework\src\Illuminate\View\View.php:191
16 - vendor\laravel\framework\src\Illuminate\View\View.php:160
17 - vendor\laravel\framework\src\Illuminate\View\Concerns\ManagesComponents.php:103
18 - storage\framework\views\livewire\views\62fab03e.blade.php:100
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

GET /admin/logs/4

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
* **referer**: http://localhost:8000/admin/logs
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: XSRF-TOKEN=eyJpdiI6InhhNmY5U1UzN1p1R2NmbmN5V21vblE9PSIsInZhbHVlIjoiRUFLb2VzNW9yQnVVM3ZQRHBqQkViK2hQeDd4ZHNIYzFnK1RWNHdZMUVyN3greUp1dXZHNXZnSnhMc0U0NVNGdm9BVHc5dW5WZ2xnbFhYS3BQbzhlRzJxKzJZb0RGeXVPZUdjZk4zS0EvYk5QL0FvQTllMUFPUlB6NDNiMHFxQjYiLCJtYWMiOiI2YTczNzZhY2YyY2U2ZDBhNzAyYjlhM2I5NzZiZWYyZWZmODE4OTRhY2YyNjZiY2RkY2JmYWJmMzNjOWU4NGIzIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6Im5GUC96ZFk3L0puOVk3cnJ0WStDSUE9PSIsInZhbHVlIjoiMXpDN1F0NndRWGlGV0xQeUJRM09tSmRaN0Z0RGVpS3JLdmNKaXFNRHRPVXNzQmlBejVKUmFVZ0w0cW1LdzVrR0tIWTNBZmt6aEhNTU56MXVaTmZOYVNwM2FyeU82ZlNBRUZXL1M4QXBLRE9uOExxcWVLeVV2TFEvcXdsTkI5VmEiLCJtYWMiOiJjYzhiODg2NTI4NjJiNDExZDIzZDE1NDA1YTYzMmNlZmQ1YzM3N2QyNDAxM2Y0MTM0OTgzNjJiYzM0OWE5YWZlIiwidGFnIjoiIn0%3D
<!--[if ENDBLOCK]><![endif]-->
## Route Context

<!--[if BLOCK]><![endif]-->controller: Livewire\Features\SupportRouting\LivewirePageController
route name: admin.logs.show
middleware: web, auth, verified, can:view-audit-logs
<!--[if ENDBLOCK]><![endif]-->
## Route Parameters

<!--[if BLOCK]><![endif]-->{
    "auditLog": {
        "id": 4,
        "user_id": 1,
        "action": "created",
        "auditable_type": "App\\Models\\Resident",
        "auditable_id": 1,
        "old_values": null,
        "new_values": {
            "first_name": "James",
            "last_name": "Gichuru",
            "date_of_birth": "1994-02-23 00:00:00",
            "gender": "male",
            "phone": "0765665875",
            "email": "james@givhuru.com",
            "admission_date": "2026-01-30 00:00:00",
            "room_number": "001",
            "bed_number": "001",
            "status": "active",
            "blood_type": "B+",
            "allergies": "Nuts, Pollen, Bees and Fur",
            "medical_conditions": "Type 2 Diabetes",
            "mobility_status": "wheelchair",
            "dietary_requirements": "More Proteins",
            "fall_risk_level": "medium",
            "dnr_status": true,
            "emergency_contact_name": "Linda Gichuru",
            "emergency_contact_phone": "0738939982",
            "emergency_contact_relationship": "Wife",
            "nok_name": "Linda Gichuru",
            "nok_phone": "0784833894",
            "nok_email": "linda@gichuru.com",
            "nok_relationship": "Wife",
            "nok_address": "",
            "notes": "James was brought in by the wife Linda on the daae stated above ",
            "created_by": 1,
            "photo_path": "residents/photos/Ebud8421kjMhXyb9aA3TfRypXjMNmQqeoKnYojtk.jpg",
            "updated_at": "2026-01-30 05:47:20",
            "created_at": "2026-01-30 05:47:20",
            "id": 1
        },
        "ip_address": "127.0.0.1",
        "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0",
        "description": "Resident 'James Gichuru' was created",
        "created_at": "2026-01-30T05:47:20.000000Z",
        "updated_at": "2026-01-30T05:47:20.000000Z"
    }
}
<!--[if ENDBLOCK]><![endif]-->
## Database Queries

<!--[if BLOCK]><![endif]-->* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:system_name') (3.07 ms)
* sqlite - select * from "system_settings" where "key" = 'system_name' limit 1 (0.28 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1769757086, 'laravel-cache-system_setting:system_name', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (8.28 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-system_setting:logo_path') (0.37 ms)
* sqlite - select * from "system_settings" where "key" = 'logo_path' limit 1 (0.34 ms)
* sqlite - insert into "cache" ("expiration", "key", "value") values (1769757086, 'laravel-cache-system_setting:logo_path', 'N;') on conflict ("key") do update set "expiration" = "excluded"."expiration", "key" = "excluded"."key", "value" = "excluded"."value" (9.06 ms)
* sqlite - select * from "sessions" where "id" = 'XpxvqQ2hPg2POxsuqoRmyHb5GLG2eyXbNMUnQ1W0' limit 1 (0.56 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.43 ms)
* sqlite - select * from "audit_logs" where "id" = '4' limit 1 (0.38 ms)
* sqlite - select * from "cache" where "key" in ('laravel-cache-spatie.permission.cache') (0.26 ms)
* sqlite - select "permissions".*, "model_has_permissions"."model_id" as "pivot_model_id", "model_has_permissions"."permission_id" as "pivot_permission_id", "model_has_permissions"."model_type" as "pivot_model_type" from "permissions" inner join "model_has_permissions" on "permissions"."id" = "model_has_permissions"."permission_id" where "model_has_permissions"."model_id" in (1) and "model_has_permissions"."model_type" = 'App\Models\User' (0.4 ms)
* sqlite - select "roles".*, "model_has_roles"."model_id" as "pivot_model_id", "model_has_roles"."role_id" as "pivot_role_id", "model_has_roles"."model_type" as "pivot_model_type" from "roles" inner join "model_has_roles" on "roles"."id" = "model_has_roles"."role_id" where "model_has_roles"."model_id" in (1) and "model_has_roles"."model_type" = 'App\Models\User' (0.82 ms)
* sqlite - select * from "users" where "users"."id" in (1) (0.33 ms)
* sqlite - select * from "residents" where "residents"."id" = 1 and "residents"."deleted_at" is null limit 1 (0.67 ms)
<!--[if ENDBLOCK]><![endif]-->