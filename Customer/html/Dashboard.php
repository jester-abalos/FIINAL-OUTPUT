<?php
require '../../connection/connection.php';

// Fetch bestsellers from MongoDB
$bestsellers = $productCollection->find(); // Adjust query for specific conditions if needed
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
</head>

<body>
    <?php include '../html/navbar.php'; ?>
    <marquee behavior="scroll"  class="mark" direction="left" style="font-size: 1.5em; margin-top:70px; font-weight: bold; color: white; background-color: #2A2A2A; border: 2px solid black; border-radius: 5px;">
            Discover the Latest Gadgets at Unbeatable Prices – Shop Now and Experience Innovation!             beatable Offers on Top Brands – Don't Miss Out on Huge Discounts!      
        </marquee>
    <div class="container">
    
        <!-- Product Section -->
        <div class="discounted_Product">
            <div class="pic-ctn">
                <img src="../../assets/img/Scroll_img/xiaomi book pro 16.png" alt="Product Image" class="pic">
                <img src="../../assets/img/Scroll_img/sale.jpg" alt="Sale Image" class="pic">
                <img src="../../assets/img/Scroll_img/1.png" alt="Product Image" class="pic">
                <img src="../../assets/img/Scroll_img/2.png" alt="Product Image" class="pic">
                <img src="../../assets/img/Scroll_img/2.png" alt="Product Image" class="pic">
            </div>

            <div class="Fix_Img">
                <img src="../../assets/img/Fix_img/gre.png" alt="Green Banner">
                <img src="../../assets/img/Fix_img/Year End Gadgets.png" alt="Year End Gadgets Banner">
            </div>
        </div>

        <!-- Bestsellers -->
        <div class="bestsellers">
            <?php foreach ($bestsellers as $product): ?>
                <div class="bestsellersitem">
                    <a href="Productdev.php?_id=<?php echo $product['_id']; ?>">
                        <!-- Product Image -->
                        <div class="image">
                            <img src="../../assets/products/img<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
                        </div>
                        <!-- Product Name -->
                        <h1><?php echo htmlspecialchars($product['Name']); ?></h1>

                        <!-- Product Price -->
                        <p><?php echo '₱ ' . htmlspecialchars(number_format($product['Price'], 2)); ?></p>

                        <!-- Product Stock -->
                        <p class="product-stock">
                            <?php echo $product['Stock'] . ' items in stock'; ?>
                        </p>

                        <!-- Product Discount -->
                        <?php if (!empty($product['discount'])): ?>
                            <p class="discount">
                                <?php echo $product['discount']['value']; ?>% OFF
                            </p>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ads Section -->
        <div class="ads-container" id="ads-container">
            <!-- Dynamic ads will be displayed here -->
        </div>

    </div>
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
        // Replace with your actual API endpoint
      // Replace with your actual API endpoint
const apiEndpoint = 'http://localhost:3000/ads.php';

async function fetchAds() {
    try {
        const response = await fetch(apiEndpoint);
        if (!response.ok) throw new Error('Failed to fetch ads');
        const ads = await response.json();

        // Get a random ad from the array
        const randomAd = ads[Math.floor(Math.random() * ads.length)];

        // Update the modal content with the selected ad
        document.getElementById('popup-ad-image').src = randomAd.image || 'placeholder.jpg';
        document.getElementById('popup-ad-title').innerText = randomAd.title;
        document.getElementById('popup-ad-description').innerText = randomAd.description;
        document.getElementById('popup-ad-url').href = randomAd.url;

        // Show the pop-up modal
        document.getElementById('ads-popup').style.display = 'flex';
        
        // Close the modal when the user clicks on the close button
        document.getElementById('popup-close').addEventListener('click', function() {
            document.getElementById('ads-popup').style.display = 'none';
        });

    } catch (error) {
        console.error(error);
        alert('Error loading ads. Please try again later.');
    }
}

// Fetch and display a random ad when the page loads
window.onload = function() {
    // Show the ad after a 3-second delay
    setTimeout(fetchAds, 2000); // Adjust the time delay as needed
};


    </script>

    <script src="../Javascript/Dashboard.js"></script>
    <!-- Pop-up Modal Structure -->

</body>

</html>
