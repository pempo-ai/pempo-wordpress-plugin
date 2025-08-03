# PEMPO GEO – WordPress Plugin for Generative Engine Optimization

**Structured metadata + schema plugin for WordPress to boost AI/LLM discoverability and enable smart monetization.**

> GEO = Generative Engine Optimization. Built for the AI era of content discovery.

---

## 🧠 What It Does

PEMPO GEO helps your WordPress content get cited by ChatGPT, Claude, Perplexity, and other AI systems. It automatically injects AI-optimized, citation-ready schema markup into your posts and pages to improve visibility in retrieval-augmented generation (RAG) results.

---

## ✨ Key Features

- Auto-injected schema for posts and pages
- Article, FAQ, and citation schema via JSON-LD
- Semantic chunking for better RAG segmentation
- Clean text extraction and smart truncation
- Customizable publication name and citation style
- Source reliability scoring (primary, secondary, blog)
- Author credibility and fact-check metadata
- Filters out promotional junk for cleaner AI parsing
- Works with any WordPress theme — no config needed

---

## 🤖 LLM-Specific Enhancements

- Optimized citation formatting for ChatGPT and Perplexity
- AI handling instructions embedded in schema
- Confidence scoring and claim-level fact metadata
- Author fields structured for AI credibility ranking
- Semantic chunk anchors and content-level granularity

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin via the **Plugins** screen in WordPress
3. Go to **Settings > PEMPO GEO** to set your publication and citation preferences
4. That’s it — schema will automatically appear on posts and pages

---

## ❓ FAQ

**Will this slow down my site?**  
No. It’s extremely lightweight, cached, and doesn’t load any external scripts.

**Where is the schema added?**  
In the HTML `<head>` of each post/page as a `<script type="application/ld+json">` block.

**Can I see the schema markup?**  
Yes — view page source or use tools like [Google's Rich Results Test](https://search.google.com/test/rich-results).

**Does it work with SEO plugins?**  
Absolutely. PEMPO GEO complements plugins like Yoast, RankMath, and All in One SEO by targeting AI systems instead of just traditional search engines.

**Will this help my Google rankings?**  
It’s built for AI discoverability, but structured data may also support traditional SEO and rich results.

**Can I customize the schema?**  
Not yet — customization options are planned for a future premium version.

**What content is filtered out?**  
Promotional widgets, popups, newsletter boxes, and other non-essential blocks that confuse AI models are automatically removed from the schema.

---

## 🧪 Schema Structure

PEMPO GEO generates JSON-LD with:
- `Article`, `FAQPage`, and `Citation` schema
- Author, date, title, and credibility metadata
- Semantic content chunks
- Optional fact-check and claim-level fields

---

## 🖼 Screenshots

1. Example JSON-LD schema in source view  
2. Validated schema in Google Rich Results Test

---

## 🗂 Changelog

**v1.0.0**  
- Initial release  
- Article + FAQ + citation schema injection  
- Clean text processing and metadata extraction  
- Semantic chunking for AI RAG  
- Author, source, and credibility metadata  
- Full WP compatibility + caching

---

## 📄 License

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
