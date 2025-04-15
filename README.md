How It Works (Step-by-Step)
🟢 1. Scan All Products
The plugin loops through all published WooCommerce products.

🟢 2. Create Matching Image Names
For each product, it tries to match an image using:

A sanitized version of the product title (e.g. accessibe-ai)

The product’s actual slug (e.g. accessibe-ai-review)


🟢 3. Search for Existing Image in Media Library
It first checks if either of those .webp images already exists in the WordPress media library.

If a match is found, it sets that image as the product's featured image (thumbnail).

🟡 4. Fuzzy Matching (Fallback #1)
If exact filename matching fails, the plugin performs a fuzzy search:

It searches the media library for any .webp image whose path contains the product’s slug or sanitized title.

This helps catch similar image names that aren’t an exact match.

🟠 5. Check Local File System (Fallback #2)
If no image was found in the media library, the plugin then looks in:

If it finds a .webp file with a matching name, it:

Uploads the image to the media library.

Sets it as the product thumbnail.

🔴 6. If No Match is Found
It prints a warning saying:

⚠️ No image found for "Product Name"

So you know which ones still need manual attention.

🧩 Smart Matching System
✅ Matches based on name or slug.

🔍 Uses fuzzy partial match if necessary.

📂 Uploads missing images automatically.

🛡️ Skips already updated products.

🔧 Where You Use It
You’ll run it from:

WordPress Dashboard → Tools → WebP Thumbnails
