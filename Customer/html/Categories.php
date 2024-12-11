<?php
require '../../connection/connection.php';

// Get the selected category from the URL
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Initialize the query for MongoDB
$query = [];

// Apply filter if category is provided
if (!empty($category)) {
    $query['Category'] = $category; // Filter products based on selected category
}

// Fetch products from MongoDB based on the query
$bestsellers = $productCollection->find($query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/Dashboard.css">
    <link rel="stylesheet" href="../css/scroll.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/ads.css">
    <link rel="stylesheet" href="../css/cat.css">
</head>
<body>
<?php include '../html/navbar.php'; ?>
<marquee behavior="scroll"  class="mark" direction="left" style="font-size: 1.5em; margin-top:70px; font-weight: bold; color: white; background-color: #2A2A2A; border: 2px solid black; border-radius: 5px;">
            Discover the Latest Gadgets at Unbeatable Prices – Shop Now and Experience Innovation!             beatable Offers on Top Brands – Don't Miss Out on Huge Discounts!      
        </marquee>
<div class="container">
    <div class="Category">
        <h1 class="categoryTitle">CATEGORIES</h1>
        <div class="categorygrid">
            <div class="catitem" onclick="window.location.href='?category=smartphones'">
                <img src="../../assets/img/Categories/smart-phone.png" alt="Smartphones">
                <p>Smartphones</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=laptops'">
                <img src="../../assets/img/Categories/Laptop.png" alt="Laptops">
                <p>Laptops</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=tablets'">
                <img src="../../assets/img/Categories/tablet.png" alt="Tablets">
                <p>Tablets</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=wearables'">
                <img src="../../assets/img/Categories/wareable.png" alt="Wearables">
                <p>Wearables</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=audio'">
                <img src="../../assets/img/Categories/audio.png" alt="Audio">
                <p>Audio</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=gaming'">
                <img src="../../assets/img/Categories/gaming.png" alt="Gaming">
                <p>Gaming</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=camera'">
                <img src="../../assets/img/Categories/camera.png" alt="Cameras">
                <p>Cameras</p>
            </div>
            <div class="catitem" onclick="window.location.href='?category=homegadgets'">
                <img src="../../assets/img/Categories/home gadgets.png" alt="Home Gadgets">
                <p>Home Gadgets</p>
            </div>
        </div>
    </div>

    <!-- Bestsellers (Filtered by Category) -->
    <div class="bestsellers">
        <?php if ($bestsellers->isDead()): ?>
            <p>No products found in this category.</p>
        <?php else: ?>
            <?php foreach ($bestsellers as $product): ?>
                <div class="bestsellersitem">
                    <a href="Productdev.php?_id=<?php echo $product['_id']; ?>">
                        <div class="image">
                            <img src="../../assets/products/img<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
                        </div>
                        <h1><?php echo htmlspecialchars($product['Name']); ?></h1>
                        <p><?php echo '₱ ' . htmlspecialchars(number_format($product['Price'], 2)); ?></p>
                        <p class="product-stock"><?php echo $product['Stock'] . ' items in stock'; ?></p>
                        <?php if (!empty($product['discount'])): ?>
                            <p class="discount"><?php echo $product['discount']['value']; ?>% OFF</p>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

        <!-- Ads Section -->
        <div class="ads-container" id="ads-container"></div>
    </div>

    <!-- Ads Popup -->
    <div id="ads-popup" class="ads-popup">
        <div class="popup-content">
            <span id="popup-close" class="close-btn">&times;</span>
            <img id="popup-ad-image" src="" alt="Ad Image">
            <h3 id="popup-ad-title">Ad Title</h3>
            <p id="popup-ad-description">Ad Description</p>
            <a id="popup-ad-url" href="#" target="_blank">View More</a>
        </div>
    </div>

    <!-- Footer Section -->
    <footer id="footer" class="footer">
        <div class="about">
            <img src="../../assets/img/LOGO1.png" alt="Company Logo">
            <p>"Your Ultimate Destination for Cutting-Edge Technology and Innovation, Where Every Gadget Enthusiast Can Discover, Compare, and Purchase the Latest and Greatest Tech Products, All in One Convenient Place."</p>
            <div class="footerbtn">
                <button>Home</button>
                <button>About</button>
                <button>Contact</button>
                <button>Shop</button>
            </div>
        </div>
        <div class="contactus">
            <p>Contact Us:</p>
            <button id="footerfacebook"></button>
            <button id="footerinstagram"></button>
            <button id="footertwitter"></button>
        </div>
    </footer>

    <script>
        const apiEndpoint = 'http://localhost:3000/ads.php';

        async function fetchAds() {
            try {
                const response = await fetch(apiEndpoint);
                if (!response.ok) throw new Error('Failed to fetch ads');
                const ads = await response.json();

                const randomAd = ads[Math.floor(Math.random() * ads.length)];
                document.getElementById('popup-ad-image').src = randomAd.image || 'placeholder.jpg';
                document.getElementById('popup-ad-title').innerText = randomAd.title;
                document.getElementById('popup-ad-description').innerText = randomAd.description;
                document.getElementById('popup-ad-url').href = randomAd.url;

                document.getElementById('ads-popup').style.display = 'flex';

                document.getElementById('popup-close').addEventListener('click', function() {
                    document.getElementById('ads-popup').style.display = 'none';
                });

            } catch (error) {
                console.error(error);
                alert('Error loading ads. Please try again later.');
            }
        }

        window.onload = function() {
            setTimeout(fetchAds, 2000); // Adjust the time delay as needed
        };
    </script>
    <script src="../Javascript/Dashboard.js"></script>
</body>
</html>
