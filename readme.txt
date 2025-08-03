=== PEMPO GEO ===
Contributors: joshjaffe  
Tags: schema, AI, GEO, AEO, citations
Requires at least: 5.0  
Tested up to: 6.8  
Stable tag: 1.0.2  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Structured data for the AI era. PEMPO GEO helps ChatGPT, Claude, Perplexity, and other LLMs cite and surface your content.

== Description ==

PEMPO GEO prepares your WordPress content for retrieval-augmented generation (RAG) by large language models (LLMs) like ChatGPT, Claude, and Perplexity. It automatically injects AI-optimized, citation-ready schema markup into your posts and pages to increase your chances of being cited in AI responses.

**Key Features:**
- Automatic schema injection for posts and pages
- Customizable publication name and citation style
- Source reliability configuration (primary, secondary, blog)
- Promotional content filtering for cleaner AI processing
- Q&A extraction and FAQ-style schema support
- Title, author, publication date, and fact-check metadata
- Semantic chunking for AI citation anchors
- Clean text processing and smart truncation
- Works with any WordPress theme
- No configuration needed

**Genuinely LLM-Optimized Features:**
- Citation-optimized schema with preferred citation formatting
- Author credentials structured for credibility scoring
- Fact-checking metadata for source reliability
- AI handling instructions embedded in the markup
- Content confidence scoring metadata
- Key fact verification fields (dates, stats, names)
- Semantic chunking for enhanced RAG segmentation
- Claim-level granularity for precision referencing

**Why This Matters:**
As AI tools replace traditional search for many queries, structured data has become essential. PEMPO ensures your content is citation-friendly by extracting the most relevant text, adding semantic anchors, and packaging everything in JSON-LD format that AI systems can parse and attribute.

**Technical Details:**
- Uses JSON-LD schema markup (Schema.org: Article + FAQ + Citation)
- Injects into HTML head section for full-page context
- Includes structured Q&A, claim-level chunks, and citation fields
- Embeds fact-checking and confidence scoring metadata
- Adds author credentials and AI handling guidance
- Automatically processes up to 25,000 characters of cleaned content
- Caches schema for performance
- Compatible with traditional SEO plugins

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your citation preferences in Settings > PEMPO GEO
4. That's it! Schema markup will automatically appear on all posts and pages

== Frequently Asked Questions ==

= Will this slow down my site? =
No. The plugin is extremely lightweight and only adds a small amount of structured data to your page head. There are no external requests or render-blocking scripts.

= Where does the schema appear? =
In the HTML head section of single post and page views, as a `<script type="application/ld+json">` tag.

= Can I see the schema markup? =
Yes! View the page source of any post or page and look for the JSON-LD script tag in the head section. You can also use Google's Rich Results Test tool.

= Does this work with my SEO plugin? =
Yes! This plugin is designed to complement existing SEO plugins like Yoast, RankMath, or All in One SEO. While those plugins optimize for traditional search engines, this plugin specifically targets AI systems.

= How do I know if it's working? =
You can verify the schema using Google's Rich Results Test (https://search.google.com/test/rich-results) or any Schema.org validator tool.

= Will this help with Google search rankings? =
While the primary focus is AI citation, structured data can also help with traditional search engine optimization and rich snippets.

= Can I customize the schema fields? =
The current version automatically handles all schema fields. A premium version with customization options is planned for the future.

= How do I customize my publication name? =
Go to Settings > PEMPO GEO to set how your publication appears in citations, choose your citation style (academic, journalism, or web), and set your source reliability level.

= What gets filtered out? =
The plugin automatically removes promotional widgets, newsletter signups, and other non-content elements that can confuse AI systems during content analysis.


== Screenshots ==

1. Example of JSON-LD schema markup in page source
2. Rich Results Test showing valid schema

== Changelog ==

= 1.0.0 =
* Initial release
* JSON-LD Article + FAQ + citation schema injection
* Semantic chunking for AI retrieval
* Clean text processing and metadata extraction
* Caching and full WordPress compatibility

== Upgrade Notice ==

= 1.0.0 =
First release of PEMPO GEO. Instantly make your WordPress content more discoverable and citable by LLMs and AI search engines.