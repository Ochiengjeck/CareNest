

# **AI INTEGRATION DOCUMENTATION**

**AUTOMATED REPORT GENERATION IN LARAVEL DOCUMENT & RESOURCE MANAGEMENT SYSTEM**  
**Date: January 23, 2026**  
**Prepared by: JECKONIA OPIYO**  
**Location: Nairobi, Kenya**  
This document provides step-by-step integration guides for adding AI-powered report generation to your Laravel application using two recommended providers:

1. Groq API (Primary – fastest performance, generous free tier)  
2. Google Gemini API (Alternative – excellent for multimodal documents)

Both integrations allow users to generate professional reports on specific topics in custom formats (e.g., Markdown with sections, tables, etc.).

### 

### **Option 1: Groq API Integration (Recommended)**

#### **Step 1: Get Your Groq API Key**

1. Go to [https://console.groq.com](https://console.groq.com/)  
2. Sign up or log in (free, no credit card needed initially)  
3. Navigate to API Keys → Create new key  
4. Copy the key (starts with gsk\_...)

#### **Step 2: Install the Laravel Package**

We use the well-maintained community package for easy integration.  
Bash

##### *composer require lucianotonet/groq-laravel*

#### **Step 3: Publish Configuration (Optional)**

Bash

##### *php artisan vendor:publish \--provider="LucianoTonet\\GroqLaravel\\GroqServiceProvider"*

This creates config/groq.php.

#### **Step 4: Add API Key to .env**

env

##### *GROQ\_API\_KEY=gsk\_your\_actual\_key\_here*

#### **Step 5: Create a Service Class (Recommended)**

Create a service to handle report generation cleanly.  
Bash

##### *php artisan make:service ReportGenerationService*

##### *app/Services/ReportGenerationService.php*

##### *PHP*

##### *\<?php*

##### 

##### *namespace App\\Services;*

##### 

##### *use LucianoTonet\\GroqLaravel\\Groq;*

##### 

##### *class ReportGenerationService*

##### *{*

#####     *public function generateReport(string $topic, string $documentContent, array $formatInstructions \= \[\]): string*

#####     *{*

#####         *$systemPrompt \= "You are an expert report writer. Generate a professional report in Markdown format.*

#####         *Always include these sections:*

#####         *\- Executive Summary*

#####         *\- Key Findings*

#####         *\- Detailed Analysis*

#####         *\- Recommendations*

#####         

#####         *Use tables for comparisons or data lists. Use bullet points where appropriate.*

#####         *" . implode("\\n", $formatInstructions);*

##### 

#####         *$response \= Groq::chat()*

#####             *\-\>model('llama-3.1-70b-versatile') // Fast & capable; alternatives: llama-3.1-8b-instant, mixtral-8x7b-32768*

#####             *\-\>system($systemPrompt)*

#####             *\-\>user("Topic: {$topic}\\n\\nDocument Content:\\n{$documentContent}")*

#####             *\-\>temperature(0.6)*

#####             *\-\>maxTokens(4000)*

#####             *\-\>generate();*

##### 

#####         *return $response-\>content ?? 'Error generating report.';*

#####     *}*

##### 

#####     *// Optional: Structured JSON output*

#####     *public function generateJsonReport(string $topic, string $documentContent)*

#####     *{*

#####         *return Groq::chat()*

#####             *\-\>model('llama-3.1-70b-versatile')*

#####             *\-\>system('Respond ONLY with valid JSON matching the schema.')*

#####             *\-\>user("Topic: {$topic}\\nContent: {$documentContent}")*

#####             *\-\>responseFormat(\['type' \=\> 'json\_object'\])*

#####             *\-\>generate()*

#####             *\-\>content;*

#####     *}*

##### *}*

#### **Step 6: Use in Controller**

##### *app/Http/Controllers/ReportController.php*

##### *PHP*

##### *\<?php*

##### 

##### *namespace App\\Http\\Controllers;*

##### 

##### *use App\\Services\\ReportGenerationService;*

##### *use Illuminate\\Http\\Request;*

##### 

##### *class ReportController extends Controller*

##### *{*

#####     *protected $reportService;*

##### 

#####     *public function \_\_construct(ReportGenerationService $reportService)*

#####     *{*

#####         *$this-\>reportService \= $reportService;*

#####     *}*

##### 

#####     *public function generate(Request $request)*

#####     *{*

#####         *$request-\>validate(\[*

#####             *'topic' \=\> 'required|string',*

#####             *'document\_content' \=\> 'required|string',*

#####         *\]);*

##### 

#####         *$report \= $this-\>reportService-\>generateReport(*

#####             *$request-\>topic,*

#####             *$request-\>document\_content*

#####         *);*

##### 

#####         *// Optional: Convert Markdown to PDF using a package like dompdf or laravel-snappy*

#####         *return response($report)-\>header('Content-Type', 'text/markdown');*

#####         *// Or return view with $report*

#####     *}*

##### *}*

#### **Step 7: Testing & Monitoring**

* Test in Groq Playground first: [https://console.groq.com/playground](https://console.groq.com/playground)  
* Monitor usage: console.groq.com → Usage Dashboard

### 

### **Option 2: Google Gemini API Integration**

#### **Step 1: Get Your Gemini API Key**

1. Go to [https://aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)  
2. Create a new API key (free tier available immediately)  
3. Copy the key

#### **Step 2: Install Laravel Package**

Bash

##### *composer require hosseinhezami/laravel-gemini*

#### **Step 3: Add API Key to .env**

##### *env*

##### *GEMINI\_API\_KEY=your\_gemini\_api\_key\_here*

#### **Step 4: Publish Config (Optional)**

Bash

##### *php artisan vendor:publish \--provider="Hosseinhezami\\LaravelGemini\\GeminiServiceProvider"*

#### **Step 5: Create Report Service**

##### *app/Services/GeminiReportService.php*

##### *PHP*

##### *\<?php*

##### 

##### *namespace App\\Services;*

##### 

##### *use Hosseinhezami\\LaravelGemini\\Gemini;*

##### 

##### *class GeminiReportService*

##### *{*

#####     *public function generateReport(string $topic, string $documentContent): string*

#####     *{*

#####         *$prompt \= "Generate a professional report in Markdown format about the following topic: {$topic}*

##### 

#####         *Document content:*

#####         *{$documentContent}*

##### 

#####         *Structure the report with:*

#####         *\- Executive Summary*

#####         *\- Key Findings*

#####         *\- Analysis*

#####         *\- Recommendations*

##### 

#####         *Use Markdown tables, bullet points, and headers appropriately.";*

##### 

#####         *$result \= Gemini::model('gemini-1.5-flash') // Fast & free tier friendly*

#####             *\-\>systemInstruction('You are a professional business analyst writing clear, structured reports.')*

#####             *\-\>content($prompt)*

#####             *\-\>temperature(0.6)*

#####             *\-\>generateContent();*

##### 

#####         *return $result-\>text();*

#####     *}*

##### 

#####     *// Bonus: Multimodal – Analyze uploaded PDF/image*

#####     *public function analyzeDocumentWithFile(string $topic, string $filePath)*

#####     *{*

#####         *return Gemini::model('gemini-1.5-pro') // Better for vision*

#####             *\-\>content("Analyze this document and generate a report on: {$topic}")*

#####             *\-\>file($filePath) // Supports PDF, images*

#####             *\-\>generateContent()*

#####             *\-\>text();*

#####     *}*

##### *}*

#### **Step 6: Use in Controller**

##### *PHP*

##### *public function generateWithGemini(Request $request, GeminiReportService $service)*

##### *{*

#####     *$request-\>validate(\[*

#####         *'topic' \=\> 'required',*

#####         *'document\_content' \=\> 'required',*

#####     *\]);*

##### 

#####     *$report \= $service-\>generateReport(*

#####         *$request-\>topic,*

#####         *$request-\>document\_content*

#####     *);*

##### 

#####     *return response($report, 200)-\>header('Content-Type', 'text/markdown');*

##### *}*

#### **Step 7: Monitoring**

* View usage at: Google AI Studio → Usage tab

### 

### **Comparison Summary**

| Feature | Groq | Google Gemini |
| :---- | :---- | :---- |
| Speed | Extremely fast (best in class) | Very fast (Flash model) |
| Free Tier Limits | Generous daily tokens/RPM | High (often unlimited tokens) |
| Multimodal (Images/PDFs) | Text-only | Excellent native support |
| Structured JSON Output | Yes | Yes |
| Best For | Speed \+ Text Reports | Documents with images/PDFs |

### 

### **Next Steps**

1. Choose preferred provider (Groq recommended for speed).  
2. Set up API key and run composer require.  
3. Implement the service and test with sample data.  
4. Add frontend form for topic \+ document input.  
5. Optional: Add PDF export using barryvdh/laravel-dompdf.

Let me know if you'd like a ready-to-use GitHub repository template or help with frontend (Livewire/Blade/Inertia) integration.

