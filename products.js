// Product Database for Flex Bliss
const products = [
    // GYPSUM PRODUCTS (6 items) - Using local images
    {
        id: 1,
        name: "Angel Wings Decorative Set",
        category: "gypsum",
        price: 1500,
        originalPrice: 2000,
        image: "images/gypsum_angel_wings_set_8_1764174400055.png",
        rating: 4.9,
        reviews: 87,
        badge: "Bestseller",
        description: "Elegant handcrafted gypsum angel wings wall decor, perfect for bohemian and modern interiors",
        inStock: true
    },
    {
        id: 2,
        name: "Modern Gypsum Vase",
        category: "gypsum",
        price: 1200,
        originalPrice: 1600,
        image: "images/gypsum_modern_vase_1764174422134.png",
        rating: 4.8,
        reviews: 124,
        badge: "New",
        description: "Contemporary gypsum vase with smooth finish, ideal for dried flowers or standalone decor",
        inStock: true
    },
    {
        id: 3,
        name: "Large Round Decorative Tray",
        category: "gypsum",
        price: 1800,
        originalPrice: 2400,
        image: "images/gypsum_large_round_tray_1764174423213.png",
        rating: 4.7,
        reviews: 156,
        badge: null,
        description: "Handmade gypsum tray with intricate details, perfect for displaying jewelry or decorative items",
        inStock: true
    },
    {
        id: 4,
        name: "White Oval Serving Tray",
        category: "gypsum",
        price: 1600,
        originalPrice: 2100,
        image: "images/gypsum_white_oval_tray_1764174424847.png",
        rating: 4.6,
        reviews: 98,
        badge: null,
        description: "Classic oval gypsum tray with elegant edges, versatile for home styling",
        inStock: true
    },
    {
        id: 5,
        name: "Angel Wings Wall Art (Small)",
        category: "gypsum",
        price: 1300,
        originalPrice: 1700,
        image: "images/gypsum_angel_wings_set_6_1764174425697.png",
        rating: 4.8,
        reviews: 145,
        badge: "Popular",
        description: "Smaller angel wings set perfect for creating a gallery wall or shelf display",
        inStock: true
    },
    {
        id: 6,
        name: "Elegant Candlestick Holder",
        category: "gypsum",
        price: 900,
        originalPrice: 1200,
        image: "images/gypsum_candlestick_holder_1764174426683.png",
        rating: 4.7,
        reviews: 112,
        badge: null,
        description: "Hand-sculpted gypsum candlestick holder adds warmth and elegance to any space",
        inStock: true
    },

    // SCENTED CANDLES (6 items) - Using Unsplash URLs
    {
        id: 7,
        name: "Lavender Dreams Candle",
        category: "candles",
        price: 800,
        originalPrice: 1000,
        image: "https://images.unsplash.com/photo-1602874801006-fb2e969c9a9c?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 203,
        badge: "Bestseller",
        description: "Calming lavender scented soy candle with 40-hour burn time, handpoured in glass jar",
        inStock: true
    },
    {
        id: 8,
        name: "Vanilla Bean Bliss",
        category: "candles",
        price: 750,
        originalPrice: 950,
        image: "https://images.unsplash.com/photo-1603006905003-be475563bc59?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 189,
        badge: "New",
        description: "Sweet vanilla bean scented candle with natural soy wax, creates cozy ambiance",
        inStock: true
    },
    {
        id: 9,
        name: "Ocean Breeze Candle",
        category: "candles",
        price: 850,
        originalPrice: 1100,
        image: "https://images.unsplash.com/photo-1598511757337-fe2cafc31ba0?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 156,
        badge: null,
        description: "Fresh ocean-inspired scent with hints of sea salt and citrus",
        inStock: true
    },
    {
        id: 10,
        name: "Rose Garden Candle",
        category: "candles",
        price: 900,
        originalPrice: 1200,
        image: "https://images.unsplash.com/photo-1587070180347-5cc6adfbfe96?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 178,
        badge: "Popular",
        description: "Romantic rose petals scent, handcrafted with essential oils",
        inStock: true
    },
    {
        id: 11,
        name: "Cinnamon Spice Candle",
        category: "candles",
        price: 800,
        originalPrice: 1000,
        image: "https://images.unsplash.com/photo-1603006905174-bc173a27c90e?w=500&h=500&fit=crop",
        rating: 4.6,
        reviews: 134,
        badge: null,
        description: "Warm cinnamon and spice blend perfect for fall and winter evenings",
        inStock: true
    },
    {
        id: 12,
        name: "Sandalwood & Amber",
        category: "candles",
        price: 950,
        originalPrice: 1250,
        image: "https://images.unsplash.com/photo-1602874801007-7ecf0c8b9e0f?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 167,
        badge: "Luxury",
        description: "Sophisticated woody aroma with amber undertones, premium soy wax",
        inStock: true
    },

    // JEWELRY & ACCESSORIES (6 items) - Using Unsplash URLs
    {
        id: 13,
        name: "Bohemian Beaded Necklace",
        category: "jewelry",
        price: 1400,
        originalPrice: 1800,
        image: "https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 145,
        badge: "Trending",
        description: "Handcrafted beaded necklace with natural stones and unique bohemian design",
        inStock: true
    },
    {
        id: 14,
        name: "Gold Plated Earrings Set",
        category: "jewelry",
        price: 1200,
        originalPrice: 1600,
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 198,
        badge: "Bestseller",
        description: "Elegant gold-plated earrings set, hypoallergenic and nickel-free",
        inStock: true
    },
    {
        id: 15,
        name: "Crystal Charm Bracelet",
        category: "jewelry",
        price: 1100,
        originalPrice: 1500,
        image: "https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 176,
        badge: "New",
        description: "Delicate bracelet with sparkling crystal charms, adjustable size",
        inStock: true
    },
    {
        id: 16,
        name: "Statement Ring Collection",
        category: "jewelry",
        price: 1600,
        originalPrice: 2100,
        image: "https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=500&h=500&fit=crop",
        rating: 4.6,
        reviews: 123,
        badge: null,
        description: "Set of 3 statement rings in silver and gold tones, stackable design",
        inStock: true
    },
    {
        id: 17,
        name: "Pearl Drop Earrings",
        category: "jewelry",
        price: 1800,
        originalPrice: 2400,
        image: "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 156,
        badge: "Luxury",
        description: "Classic freshwater pearl earrings with sterling silver hooks",
        inStock: true
    },
    {
        id: 18,
        name: "Layered Chain Necklace",
        category: "jewelry",
        price: 1500,
        originalPrice: 2000,
        image: "https://images.unsplash.com/photo-1599643477877-530eb83abc8e?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 189,
        badge: null,
        description: "Trendy layered necklace with mixed metals and pendant details",
        inStock: true
    },

    // CONCRETE / CEMENT PRODUCTS (3 items) - Using Unsplash URLs
    {
        id: 19,
        name: "Concrete Mini Plant Pot Set",
        category: "concrete",
        price: 1200,
        originalPrice: 1600,
        image: "https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 142,
        badge: "Trending",
        description: "Set of 3 handmade concrete mini plant pots perfect for succulents and small plants",
        inStock: true
    },
    {
        id: 20,
        name: "Designer Concrete Lamp Base",
        category: "concrete",
        price: 2200,
        originalPrice: 2800,
        image: "https://lh3.googleusercontent.com/d/sfnU7W1kYP4kETmnN",
        rating: 4.9,
        reviews: 98,
        badge: "Bestseller",
        description: "Modern geometric concrete lamp base with matte finish, suitable for any shade",
        inStock: true
    },
    {
        id: 21,
        name: "Cement Door Nameplate",
        category: "concrete",
        price: 800,
        originalPrice: 1100,
        image: "https://images.unsplash.com/photo-1606902965551-dce093cda6e7?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 156,
        badge: "Custom",
        description: "Personalized cement nameplate for door or wall, modern industrial design",
        inStock: true
    },

    // SOAP & PERSONAL CARE CRAFTS (3 items) - Using Unsplash URLs
    {
        id: 22,
        name: "Organic Lavender Soap Bar",
        category: "soap",
        price: 450,
        originalPrice: 600,
        image: "https://images.unsplash.com/photo-1600857062241-98e5dba60f0008?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 234,
        badge: "Bestseller",
        description: "Handmade organic soap with natural lavender essential oils, gentle on skin",
        inStock: true
    },
    {
        id: 23,
        name: "Colorful Bath Bomb Set",
        category: "soap",
        price: 650,
        originalPrice: 850,
        image: "https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 189,
        badge: "Popular",
        description: "Set of 6 fizzy bath bombs in vibrant colors with essential oils and natural ingredients",
        inStock: true
    },

    {
        id: 33,
        name: "Organic Lavender Soap Bar OL",
        category: "soap",
        price: 500,
        originalPrice: 650,
        image: "images/organic_lavender_soap_ol.png",
        rating: 4.9,
        reviews: 12,
        badge: "New",
        description: "Premium organic lavender soap bar with soothing aroma.",
        inStock: true
    },

    // HOME DECOR ITEMS (11 items) - Using Unsplash URLs
    {
        id: 25,
        name: "Ceramic Flower Vase Set",
        category: "decorative",
        price: 1300,
        originalPrice: 1700,
        image: "https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=500&h=500&fit=crop",
        rating: 4.8,
        reviews: 134,
        badge: "Popular",
        description: "Set of 3 modern ceramic vases in complementary colors and heights",
        inStock: true
    },
    {
        id: 26,
        name: "Decorative Dreamcatcher",
        category: "decorative",
        price: 1100,
        originalPrice: 1500,
        image: "https://images.unsplash.com/photo-1520699697851-3dc68aa3ca19?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 167,
        badge: "Handmade",
        description: "Bohemian dreamcatcher with feathers and beads, wall hanging decor",
        inStock: true
    },
    {
        id: 27,
        name: "Wooden Photo Frame Set",
        category: "decorative",
        price: 1400,
        originalPrice: 1900,
        image: "https://images.unsplash.com/photo-1513519245088-0e3f7a0bf520?w=500&h=500&fit=crop",
        rating: 4.6,
        reviews: 145,
        badge: null,
        description: "Rustic wooden frames in various sizes, perfect for gallery walls",
        inStock: true
    },
    {
        id: 28,
        name: "Macrame Wall Hanging",
        category: "decorative",
        price: 1600,
        originalPrice: 2200,
        image: "https://images.unsplash.com/photo-1610992826576-45e8bebd585d?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 178,
        badge: "Trending",
        description: "Handwoven macrame wall art in natural cotton, boho chic style",
        inStock: true
    },
    {
        id: 29,
        name: "Bohemian Macramé Wall Art",
        category: "decorative",
        price: 1800,
        originalPrice: 2400,
        image: "https://images.unsplash.com/photo-1598624443135-0c0d10bfd07f?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 198,
        badge: "Trending",
        description: "Large handwoven macramé wall hanging in natural cotton with intricate patterns",
        inStock: true
    },
    {
        id: 30,
        name: "Feather Dream Catcher",
        category: "decorative",
        price: 950,
        originalPrice: 1300,
        image: "https://images.unsplash.com/photo-1582655299221-2609d760ce8e?w=500&h=500&fit=crop",
        rating: 4.6,
        reviews: 145,
        badge: null,
        description: "Handmade dream catcher with natural feathers and beads, boho chic design",
        inStock: true
    },
    {
        id: 31,
        name: "Wooden Keychain Collection",
        category: "decorative",
        price: 350,
        originalPrice: 500,
        image: "https://images.unsplash.com/photo-1594044956901-a9b2e5d76d1d?w=500&h=500&fit=crop",
        rating: 4.7,
        reviews: 178,
        badge: "Gift",
        description: "Set of 5 laser-cut wooden keychains with unique designs, perfect for gifts",
        inStock: true
    },
    {
        id: 32,
        name: "Engraved Wood Art Piece",
        category: "decorative",
        price: 2500,
        originalPrice: 3200,
        image: "https://images.unsplash.com/photo-1604762524889-8a22f71ed1b1?w=500&h=500&fit=crop",
        rating: 4.9,
        reviews: 112,
        badge: "Luxury",
        description: "Custom engraved wooden art piece with intricate details, perfect statement decor",
        inStock: true
    }
];

// Export static products as fallback
window.productsData = products;

// Try to load products from database (synced with admin panel)
(async function loadProductsFromDB() {
    try {
        const response = await fetch('api/get_products.php');
        const data = await response.json();

        if (data.success && data.products && data.products.length > 0) {
            // Map database fields to expected format
            window.productsData = data.products.map(p => ({
                id: p.id,
                name: p.name,
                category: p.category || 'decorative',
                price: p.price,
                originalPrice: p.originalPrice || p.price,
                image: p.image,
                rating: p.rating || 4.5,
                reviews: p.reviews || 0,
                badge: p.badge,
                description: p.description || '',
                inStock: p.inStock
            }));
            console.log('Products loaded from database:', window.productsData.length);

            // Re-render products if function exists
            if (typeof renderProducts === 'function') {
                renderProducts();
            }
        }
    } catch (error) {
        console.log('Using static products (database not available)');
    }
})();
